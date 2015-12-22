<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
use \Joomla\String\String;

class plgSystemMinicck extends JPlugin
{
    private static
        $customfields = null,
        $contentTypes = null,
        $categoryCustomfields = null,
        $categoryContentTypes = null;

    private
        $input,
        $isAdmin,
        $config,
        $context;


	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

        $this->input = new JInput();
        $this->config = $config;
        $this->config['params'] = json_decode($this->config['params']);
        $this->isAdmin = JFactory::getApplication()->isAdmin();
		$this->loadLanguage();
	}

    /**
     * Добвление скрипта в форму редактирования материала
     */
    function onAfterDispatch()
    {
        $option = $this->input->getCmd('option', '');
        $view = $this->input->getCmd('view', '');
        $layout = $this->input->getCmd('layout', '');

        if($this->isAdmin || ($option == 'com_content' && $view == 'form' && $layout == 'edit'))
        {
            $document = JFactory::getDocument();
            $document->addScript(JUri::root().'plugins/system/minicck/assets/js/minicck_jq.js');
        }
    }

    /** Проверка заполненности обязательных полей перед сохранением
     * @param $context
     * @param $table
     * @param bool $isNew
     * @return bool
     * @throws Exception
     */
    public function onExtensionBeforeSave($context, $table, $isNew=false)
    {
        if($context !== 'com_plugins.plugin' || $table->element != 'minicck')
        {
            return true;
        }

        $params = json_decode($table->params);
        $customfields = isset($params->customfields) ? $params->customfields : null;
        $categoryCustomfields = isset($params->category_customfields) ? $params->category_customfields : null;
        $content_types = isset($params->content_types) ? $params->content_types : null;
        $category_types = isset($params->category_types) ? $params->category_types : null;

        if(!is_array($customfields) || count($customfields) == 0)
        {
            throw new Exception('Custom Fields is Empty');
        }

        if(!is_array($content_types) || count($content_types) == 0)
        {
            throw new Exception('Content Types is Empty');
        }

        $app = JFactory::getApplication();

        //проверяем заполненность обязательных полей
        foreach($customfields as $v)
        {
            if(empty($v->name))
            {
                throw new Exception('Custom Field Name is Empty');
            }

            if(empty($v->type))
            {
                throw new Exception('Custom Field "'.$v->name.'" Type is Empty');
            }

            if(empty($v->title))
            {
                $app->enqueueMessage(JText::_('Custom Field "'.$v->name.'" Title is Empty'), 'error');
                return true;
            }
        }

        //проверяем заполненность обязательных полей типов контента
        foreach($content_types as $v)
        {
            if(empty($v->name))
            {
                throw new Exception('Content Type Name is Empty');
            }

            if(empty($v->title))
            {
                $app->enqueueMessage(JText::_('Content Type "'.$v->name.'" Title is Empty'), 'error');
                return true;
            }
        }

        if(!empty($params->allow_category_fields))
        {
            if(is_array($categoryCustomfields) && count($categoryCustomfields))
            {
                foreach($categoryCustomfields as $v)
                {
                    if(empty($v->name))
                    {
                        throw new Exception('Category Custom Field Name is Empty');
                    }

                    if(empty($v->type))
                    {
                        throw new Exception('Category Custom Field "'.$v->name.'" Type is Empty');
                    }

                    if(empty($v->title))
                    {
                        $app->enqueueMessage(JText::_('Category Custom Field "'.$v->name.'" Title is Empty'), 'error');
                        return true;
                    }
                }
            }

            if(is_array($category_types) && count($category_types))
            {
                foreach($category_types as $v)
                {
                    if(empty($v->name))
                    {
                        throw new Exception('Category Type Name is Empty');
                    }

                    if(empty($v->title))
                    {
                        $app->enqueueMessage(JText::_('Category Type "'.$v->name.'" Title is Empty'), 'error');
                        return true;
                    }
                }
            }
        }
        return true;
    }

    /** Создание новых и удаление удаленных полей из таблицы
     * @param $context
     * @param $table
     * @param bool $isNew
     * @return bool
     * @throws Exception
     */
    public function onExtensionAfterSave($context, $table, $isNew=false)
    {
        if($context !== 'com_plugins.plugin' || $table->element != 'minicck')
        {
            return true;
        }
        $params = json_decode($table->params);
        $this->processContentFields($params);
        if(!empty($params->allow_category_fields))
        {
            $this->processContentFields($params, 'category');
        }

        return true;
    }

    /** Форма в редактировании контента
     * @param $context
     * @param $data
     * @return bool
     */
    function onContentPrepareData($context, $data)
    {
        if(!is_object($data) && !is_array($data)){
            return true;
        }

        $layout = $this->input->getCmd('layout', '');
        $isCategory = $isContent = false;

        $prefix = '';
        if ($context == 'com_content.article' && $layout == 'edit')
        {
            $isContent = true;
            $prefix = 'content';
        }
        else if ($context == 'com_categories.category' && $layout == 'edit')
        {
            $isCategory = true;
            $prefix = 'category';
        }

        if((!$isCategory && !$isContent) || ($isCategory && empty($this->config["params"]->allow_category_fields)))
        {
            return true;
        }

        $config = $this->config["params"];
        $articleId = 0;

        if($isContent)
        {
            if(!self::$contentTypes)
                $this->setContentTypes();
            $contentTypes = self::$contentTypes;
        }
        else if($isCategory)
        {
            if(!self::$categoryContentTypes)
                $this->setContentTypes('category');
            $contentTypes = self::$categoryContentTypes;
        }

        if(is_object($data) && !empty($data->id))
        {
            $articleId = (int)$data->id;
        }
        else if(is_array($data) && !empty($data["id"]))
        {
            $articleId = (int)$data["id"];
        }

        $html = '<div class="tab-pane" id="minicck">';

        if(!empty($config->enable_multi_categories) && $isContent)
        {
            $selectedCats = $this->getSelectedMultiCats($articleId);
            $html .= '
            <div class="control-group" id="minicck_content_type_contayner">
                <label for="minicck_content_types" class="control-label" title="" >'.JText::_('PLG_MINICCK_CATEGORIES').'</label>
                <div class="controls">
                        <select name="minicck[multi_categories][]" id="minicck_multi_categories" multiple="multiple" size="10">
                            '.JHtml::_('select.options',
                    JHtml::_('category.categories', 'com_content', array('filter.published' => 1)),
                    'value', 'text', $selectedCats).'
			        </select>
                </div>
            </div>
			';
        }

        $dataMinicck = $this->getData($articleId, $prefix);

        $options = $this->gerContentTypeOptions($prefix);

        if(!$options)
        {
            $html .= '
                <div class="control-group" id="minicck_content_type_contayner">
                    '.JText::_('PLG_MINICCK_NO_TYPES_CREATED').'
                </div>
                <hr style="clear:both"/>
            </div>
            ';
            echo $html;
            return true;
        }

        $contentType = (!empty($dataMinicck['content_type'])) ? $dataMinicck['content_type'] : '';

        if($contentType == '' || empty($contentTypes[$contentType]))
        {
            $contentTypeFields = new stdClass();
        }
        else
        {
            $contentTypeFields = $contentTypes[$contentType]->fields;
        }

        $js = 'var minicckTypeFields = [];';
        foreach ($contentTypes as $type)
        {
            if(isset($type->fields) && count($type->fields))
            {
                $js .= "\n minicckTypeFields['$type->name']=[";
                foreach($type->fields as $k => $v)
                {
                    $js .= "'$k', ";
                }
                $js .= "];";
            }
        }
        $document = JFactory::getDocument();
        $document->addScriptDeclaration($js);

        $label = JText::_('PLG_MINICCK_TYPE_CONTENT');
        $select = JHTML::_('select.genericlist', $options, 'minicck[content_type]', ' class="type inputbox" onchange="reloadMinicckFields(this)"', 'value', 'text', $contentType);
        $html .= <<<HTML
            <div class="control-group" id="minicck_content_type_contayner">
                <label for="minicck_content_types" class="control-label" title="" >$label</label>
                <div class="controls">$select</div>
            </div>
        <hr style="clear:both"/>
HTML;

        $customfields = array();

        if($isContent)
        {
            if(!self::$customfields)
                $this->setCustomFields();
            $customfields = self::$customfields;
        }
        else if($isCategory)
        {
            if(!self::$categoryCustomfields)
                $this->setCustomFields('category');
            $customfields = self::$categoryCustomfields;
        }

        if(count($customfields)>0)
        {
            foreach($customfields as $customfield)
            {
                $className = $this->loadElement($customfield);

                if(!$className)
                {
                    continue;
                }

                $attributes = array();
                $attributes['name'] = $customfield['name'];
                $attributes['label'] = $customfield['title'];
                $attributes['type'] = $customfield['type'];
                $attributes['disabled'] = $attributes['hidden'] = false;

                if(empty($contentType) || !isset($contentTypeFields->$customfield['name']))
                {
                    $attributes['disabled'] = $attributes['hidden'] = true;
                }

                $value = (isset($dataMinicck[$customfield['name']])) ? $dataMinicck[$customfield['name']] : null;

                $element = new $className('minicck['.$attributes['name'].']', $attributes, $value);

                $html .= $element->getInput($prefix);
            }
        }
        $html .= '</div>';

        echo $html;
        return true;
    }

    /** Действия с табами в редактировании контента
     * @return
     */
    public function onBeforeRender()
    {
        $isAdmin = JFactory::getApplication()->isAdmin();
        $view = $this->input->getCmd('view', '');
        $option = $this->input->getCmd('option', '');
        $layout = $this->input->getCmd('layout', '');
        $extension = $this->input->getCmd('extension', '');

        $isContent = $option == 'com_content' && ( ($isAdmin && $view == 'article') || (!$isAdmin && $view == 'form') ) && $layout === 'edit';
        $isCategory = $isAdmin && $option == 'com_categories' && $extension == 'com_content' && $view == 'category';

        if((!$isCategory && !$isContent) || ($isCategory && empty($this->config["params"]->allow_category_fields)))
        {
            return;
        }

        $document = JFactory::getDocument();
        if($isAdmin)
        {
            $document->addScriptDeclaration('
                (function($){
                    $(document).ready(function(){
		            	var tab = $(\'<li class=""><a href="#minicck" data-toggle="tab">' . JText::_( 'PLG_MINICCK_LABEL' ) . '</a></li>\');
		            	$(\'#myTabTabs\').append(tab);

		            	if($(\'#myTabContent\').length)
		            	{
                            $(\'#minicck\').appendTo($(\'#myTabContent\'));
                        }
                        else if($(\'div.span10>div.tab-content\').length)
		            	{
                            $(\'#minicck\').appendTo($(\'div.span10>div.tab-content\'));
                        }
		            });
		        })(jQuery);
		    ');
        }
        else
        {
            $document->addScriptDeclaration('
                (function($){
                    $(document).ready(function(){
		            	var tab = $(\'<li class=""><a href="#minicck" data-toggle="tab">' . JText::_( 'PLG_MINICCK_LABEL' ) . '</a></li>\');
		            	$(\'ul.nav-tabs\').append(tab);
		            	$(\'#minicck\').appendTo($(\'div.tab-content\', \'#adminForm\'));
		            });
		        })(jQuery);
		    ');
        }

    }

    /** Добавляем идентификатор категории в текст т.к. для контентных плагинов он не передается на фронте.
     * @param $context
     * @param $article
     * @param $isNew
     */
    public function onContentBeforeSave($context, &$article, $isNew)
    {
        $config = $this->config["params"];
        if(!($context == 'com_categories.category' && $config->allow_category_fields)){
            return;
        }

        $data = (isset($_POST['minicck']) && is_array($_POST['minicck']) && count($_POST['minicck'])>0 ) ? $_POST['minicck'] : null;

        $desc = preg_replace('(<div id="category-identifier" style="display: none;">\d+</div>)', '', $article->description);
        $desc = is_null($desc) ? $article->description : $desc;

        if (!$article->id || !$data || empty($data['content_type']))
        {
            $article->description = $desc;
            return;
        }

        $article->description = '<div id="category-identifier" style="display: none;">'.$article->id.'</div>'.$desc;
    }

    /** Сохранение данных
     * @param $context
     * @param $article
     * @param $isNew
     * @return bool
     * @throws Exception
     */
    public function onContentAfterSave($context, $article, $isNew)
    {
        if($context !== 'com_content.article' && $context !== 'com_content.form' && $context !== 'com_categories.category')
            return true;

        $articleId	= $article->id;

        $isCategory = $context == 'com_categories.category';

        $data = (
            isset($_POST['minicck'])
            && is_array($_POST['minicck'])
            && count($_POST['minicck'])>0
        ) ? $_POST['minicck'] : null;

        if ($articleId && $data)
        {
            if($isCategory)
            {
                if(!self::$categoryCustomfields)
                {
                    $this->setCustomFields('category');
                }
            }
            else
            {
                if(!self::$customfields)
                {
                    $this->setCustomFields();
                }
            }

            try
            {
                $object = new stdClass();

                if($isCategory){
                    $object->category_id = $articleId;
                }
                else{
                    $object->content_id = $articleId;
                }


                $config = $this->config["params"];

                if(!empty($config->enable_multi_categories) && !$isCategory)
                {
                    $multiCats = (!empty($data['multi_categories'])) ? $data['multi_categories'] : array();
                    $this->saveMultiCats($multiCats, $articleId);
                }

                $entityType = $isCategory ? 'category' : 'content';

                foreach($data as $k => $v)
                {
                    if($k == 'content_type' || $k == 'multi_categories')
                    {
                        $object->$k = $v;
                        continue;
                    }

                    $cleanedData = array();

                    $field = self::getCustomField($k, $entityType);

                    $className = $this->loadElement($field);

                    if($className != false && method_exists($className,'cleanValue'))
                    {
                        $cleanedData = $className::cleanValue($field, $v);
                    }
                    else
                    {
                        if(is_array($v) && count($v)>0)
                        {
                            foreach($v as $key => $val)
                            {
                                $cleanedData[$key] = htmlspecialchars(strip_tags($val));
                            }
                        }
                        else
                        {
                            $cleanedData = htmlspecialchars(strip_tags($v));
                        }
                    }

                    if(method_exists($className, 'prepareToSaveValue'))
                    {
                        $value = $className::prepareToSaveValue($cleanedData);
                    }
                    else
                    {
                        $value = is_array($cleanedData) ? implode(',', $cleanedData) : $cleanedData;
                    }
                    $object->$k = $value;
                }

                if($isCategory)
                {
                    $table = '#__minicck_category_fields';
                    $key = 'category_id';
                }
                else
                {
                    $table = '#__minicck';
                    $key = 'content_id';
                }

                $db = JFactory::getDbo();

                $query = $db->getQuery(true);
                $query->delete($table);
                $query->where($db->quoteName($key) . ' = ' . $db->Quote($articleId));
                $db->setQuery($query);

                if (!$db->execute())
                {
                    throw new Exception($db->getErrorMsg());
                }

                if(!empty($object->content_type)){
                    if (!$db->insertObject($table, $object))
                    {
                        throw new Exception($db->getErrorMsg());
                    }
                }

            }
            catch (Exception $e)
            {
                $this->_subject->setError($e->getMessage());
                return false;
            }
        }
        return true;
    }

    /** Действия при удалении контента
     * @param	string		The context of the content passed to the plugin (added in 1.6)
     * @param	object		A JTableContent object
     * @since   2.5
     */
    public function onContentAfterDelete($context, $article)
    {

        if ($context != 'com_content.article' && $context != 'com_categories.category')
            return true;

        $isCategory = $context == 'com_categories.category';

        if($isCategory){
            $table = '#__minicck_category_fields';
            $key = 'category_id';
        }
        else{
            $table = '#__minicck';
            $key = 'content_id';
        }

        $articleId	= $article->id;
        if ($articleId)
        {
            try
            {
                $db = JFactory::getDbo();

                $query = $db->getQuery(true);
                $query->delete();
                $query->from($table);
                $query->where($db->quoteName($key) . ' = ' . $db->Quote($articleId));
                $db->setQuery($query);

                if (!$db->query())
                {
                    throw new Exception($db->getErrorMsg());
                }

                if(!$isCategory)
                {
                    $this->deleteMultiCats($articleId);
                }

            }
            catch (Exception $e)
            {
                $this->_subject->setError($e->getMessage());
                return false;
            }
        }

        return true;
    }

    /** Вывод на фронте
     * @param $context
     * @param $article
     * @param $params
     * @param int $page
     */
    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {
        $config = $this->config["params"];

        if(empty($config))
        {
            return;
        }

        if(($context != 'com_content.article' && $context != 'com_content.category' && $context != 'com_tags.tag' && $context != 'com_content.featured')
            || ($context == 'com_content.category' && !$config->allow_in_category)
            || ($context == 'com_categories.category' && empty($config->allow_category_fields))
            || ($context == 'com_tags.tag' && !$config->allow_in_tags)
            || ($context == 'com_content.featured' && !$config->allow_in_featured)
            || ($context == 'com_content.article' && !$config->allow_in_content)
        ){
            return;
        }

        $isTags = $context == 'com_tags.tag';
        $isCategoryEntity = false;

        if($isTags && $article->type_alias != 'com_content.article')
        {
            return;
        }

        if($isTags)
        {
            $articleId = $article->content_item_id;
        }
        else if(isset($article->id))
        {
            $articleId = $article->id;
        }
        else
        {

            if(!empty($article->text) && String::strpos($article->text, '<div id="category-identifier" style="display: none;">') !== false){
                $isCategoryEntity = true;
                preg_match('(<div id="category-identifier" style="display: none;">\d+</div>)', $article->text, $matches);

                if(empty($matches[0])){
                    return;
                }

                $articleId = (int)strip_tags($matches[0]);

                if($articleId == 0){
                    return;
                }

                $desc = preg_replace('(<div id="category-identifier" style="display: none;">\d+</div>)', '', $article->text);
                $desc = is_null($desc) ? $article->text : $desc;
                $article->text = $desc;
            }
            else
            {
                return;
            }
        }

        $body = $isTags ? 'core_body' : 'text';

        $prefix = $isCategoryEntity ? 'category' : 'content';
        $result = $this->getData($articleId, $prefix);

        if(empty($result))
        {
            return;
        }

        $result = (object)$result;

        if($isCategoryEntity){
            if(!self::$categoryCustomfields)
            {
                $this->setCustomFields($prefix);
            }
            if(!self::$categoryContentTypes)
            {
                $this->setContentTypes($prefix);
            }
            $contentTypes = self::$categoryContentTypes;
            $customfields = self::$categoryCustomfields;
        }
        else{
            if(!self::$customfields)
            {
                $this->setCustomFields();
            }
            if(!self::$contentTypes)
            {
                $this->setContentTypes($prefix);
            }
            $contentTypes = self::$contentTypes;
            $customfields = self::$customfields;
        }

        $this->context = $context;
        $isCategory = ($this->context == 'com_content.category');
        $context = $isCategory ? 'category' : 'content';

        $content_type = $result->content_type;

        if(empty($contentTypes[$content_type]))
        {
            return;
        }

        $typeFields = $contentTypes[$content_type]->fields;

        unset($result->content_type);

        if($isCategoryEntity){
            $show = 'show';
        }
        else{
            $show = $context;
        }

        foreach($result as $k => $v)
        {
            if(!isset($typeFields->$k->$show))
            {
                unset($result->$k);
            }
        }

        foreach($customfields as $k => $v)
        {
            if($prefix == 'content'){
                $customfields[$k]['template'] = (isset($typeFields->$k) && isset($typeFields->$k->content_tmpl))
                    ? $typeFields->$k->content_tmpl : 'default.php';
            }
            else
            {
                $customfields[$k]['template'] = (isset($typeFields->$k) && isset($typeFields->$k->category_tmpl))
                    ? $typeFields->$k->category_tmpl : 'default.php';
            }
        }

        if($isCategoryEntity)
            self::$categoryCustomfields = $customfields;
        else
            self::$customfields = $customfields;

        if($this->params->get('load_object', 0) == 1)
        {
            include_once JPATH_ROOT . '/plugins/system/minicck/classes/html.class.php';
            include_once JPATH_ROOT . '/plugins/system/minicck/classes/categorydata.class.php';
            $result->content_type = $content_type;
            if($isCategoryEntity)
            {
                $CategoryData = MiniCCKCategoryData::getInstance();
                $CategoryData->setData($articleId, $customfields, $result);
            }
            else
            {
                $article->minicck = MiniCCKHTML::getInstance($customfields);
                $article->minicck->set($articleId, $result);
            }


        }
        else
        {
            $fields = $customfields;

            $layout = $this->getLayout($content_type, $prefix);
            $position = $isCategoryEntity ? $this->params->get('position_cat', 'top') : $this->params->get('position_content', 'top');

            if($this->params->get('load_css', '1') == 1){
                $doc = JFactory::getDocument();
                $doc->addStyleSheet(JURI::base(true).'/plugins/system/minicck/assets/css/minicck.css');
            }

            //переопределение шаблона
            $template = JFactory::getApplication()->getTemplate();

            $tmpl = JPATH_ROOT. '/templates/'.$template.'/html/plg_system_minicck/'.$layout;

            if(!JFile::exists($tmpl))
            {
                $tmpl = JPATH_ROOT.'/plugins/system/minicck/tmpl/'.$layout;
            }

            //подключение шаблона
            ob_start();
            require $tmpl;
            $html = ob_get_clean();

            if($position == 'top')
            {
                $article->$body = $html.$article->$body;
            }
            else
            {
                $article->$body = $article->$body.$html;
            }
        }
    }

    private function getData($id, $type='')
    {
        $results = null;

        if($id > 0)
        {
            switch($type){
                case 'category':
                    $table = '#__minicck_category_fields';
                    $key = 'category_id';
                    break;
                default:
                    $table = '#__minicck';
                    $key = 'content_id';
                    break;
            }
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from($table);
            $query->where($db->quoteName($key) . ' = ' . $db->Quote($id));
            $db->setQuery($query,0,1);
            $results = $db->loadAssoc();

            if ($db->getErrorNum())
            {
                $this->_subject->setError($db->getErrorMsg());
                return false;
            }
        }

        return (!empty($results)) ? $results : false;
    }

    static function getCustomFields($type='')
    {
        switch($type){
            case 'category':
                $data = self::$categoryCustomfields;
                break;
            default:
                $data = self::$customfields;
                break;
        }
        return $data;
    }

    static function getCustomField($name, $type='content')
    {
        switch($type){
            case 'category':
                $data = self::$categoryCustomfields;
                break;
            default:
                $data = self::$customfields;
                break;
        }

        return isset($data[$name]) ? $data[$name] : null;
    }

    private function setContentTypes($type='content')
    {
        $params = $this->config['params'];
        $cfName = $type.'_types';
        if(!isset($params->$cfName))
        {
            return;
        }

        $types = $params->$cfName;

        if(!is_array($types) || count($types) == 0)
        {
            return;
        }

        $newParams = array();
        foreach($types as $v)
        {
            if(count($v->fields))
            {
                foreach($v->fields as $key => $field)
                {
                    if($type =='content' && (!(isset($field->category) || isset($field->content))))
                    {
                        unset($v->fields->$key);
                    }
                    else if($type == 'category' && empty($field->show))
                    {
                        unset($v->fields->$key);
                    }
                }
            }
            $newParams[$v->name] = $v;
        }

        switch($type){
            case 'category':
                self::$categoryContentTypes = $newParams;
                break;
            default:
                self::$contentTypes = $newParams;
                break;
        }
    }

    private function setCustomFields($type='')
    {
        $customfields = $this->config['params'];
        $cfName = $type != '' ? $type.'_customfields' : 'customfields';
        $customfields = $customfields->$cfName;

        if(!is_array($customfields) || count($customfields) == 0){
            return;
        }

        $newFields = array();
        foreach($customfields as $customfield)
        {
            $k = $customfield->name;
            $newFields[$k]['name'] = $customfield->name;
            $newFields[$k]['title'] = $customfield->title;
            $newFields[$k]['type'] = $customfield->type;
            $newFields[$k]['extraparams'] = !empty($customfield->extraparams) ? $customfield->extraparams : null;

            $className = $this->loadElement($newFields[$k]);

            if($className != false && method_exists($className,'prepareParams'))
            {
                $newFields[$k]['params'] = $className::prepareParams($customfield->params);
            }
            else
            {
                $newFields[$k]['params'] = $customfield->params;
            }
        }

        switch($type){
            case 'category':
                self::$categoryCustomfields = $newFields;
                break;
            default:
                self::$customfields = $newFields;
                break;
        }

    }

    private function getLayout($contentType, $type='content')
    {
        $isCategory = $type == 'category';

        $layout = ($isCategory) ? $this->params->get('category_layout', 'default_cat.php') : $this->params->get('content_layout', 'default.php');

        if($type == 'content')
        {
            if(!self::$contentTypes)
                $this->setContentTypes();
            $contentTypes = self::$contentTypes;
        }
        else
        {
            if(!self::$categoryContentTypes)
                $this->setContentTypes('category');
            $contentTypes = self::$categoryContentTypes;
        }

        if($isCategory){
            $layout = 'category/';
            $layout .= !empty($contentTypes[$contentType]->category_tmpl)
                ? $contentTypes[$contentType]->category_tmpl : $layout;
        }
        else if($isCategory)
        {
            if(!empty($contentTypes[$contentType]->category_tmpl))
            {
                $layout = $contentTypes[$contentType]->category_tmpl;
            }
        }
        else
        {
            if(!empty($contentTypes[$contentType]->content_tmpl))
            {
                $layout = $contentTypes[$contentType]->content_tmpl;
            }
        }

        return $layout;
    }

    private function getValue($fname, $value, $type='content')
    {
        $field = self::getCustomField($fname, $type);

        $className = $this->loadElement($field);

        if($className != false && method_exists($className,'getValue'))
        {
            return $className::getValue($field, $value);
        }
        else
        {
            return $value;
        }
    }

    /** Загружаем элемент, вычисляем имя класса элемента
     * @param $field
     * @return bool|string
     */
    private function loadElement($field)
    {
        $name = $field['type'];
        $file = JPATH_ROOT.'/plugins/system/minicck/fields/'.$name.'/'.$name.'.php';

        if(!JFile::exists($file))
        {
            return false;
        }

        include_once($file);

        $className = 'JFormField'.ucfirst($name);
        return $className;
    }

    private function gerContentTypeOptions($type='content')
    {
        switch($type){
            case 'category':
                $types = self::$categoryContentTypes;
                break;
            default:
                $types = self::$contentTypes;
                break;
        }

        if(!$types)
        {
            $this->setContentTypes($type);
        }

        if (is_array($types) && count($types))
        {
            $options = array();
            $options[] = JHtml::_('select.option', '', JText::_('JSELECT'));

            foreach ($types as $v)
            {
                $options[] = JHtml::_('select.option', $v->name, $v->title);
            }
            return $options;
        }
        return false;
    }



    private function processContentFields($params, $type='content')
    {
        switch($type)
        {
            case 'category':
                $customfields = $params->category_customfields;
                $table = '#__minicck_category_fields';
                $msgPrefix = 'Category';
                break;
            default:
                $customfields = $params->customfields;
                $table = '#__minicck';
                $msgPrefix = '';
                break;
        }

        if(!is_array($customfields) || count($customfields) == 0)
        {
            throw new Exception($msgPrefix.'Custom Fields is Empty');
        }

        $newColumn = $oldColumn = array();
        $db = JFactory::getDbo();

        try{
            $columns = $db->getTableColumns($table);
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }

        if(!is_array($columns) || count($columns) == 0)
        {
            throw new Exception($msgPrefix.'Table Columns is Empty');
        }

        include_once JPATH_ROOT . '/plugins/system/minicck/classes/html.class.php';
        $minicck = MiniCCKHTML::getInstance(self::$customfields);

        //удаляем из массива служебные поля
        if(isset($columns['id']))
            unset($columns['id']);
        if(isset($columns['content_type']))
            unset($columns['content_type']);

        switch($type)
        {
            case 'category':
                if(isset($columns['category_id']))
                    unset($columns['category_id']);
                break;
            default:
                if(isset($columns['content_id']))
                    unset($columns['content_id']);
                if(isset($columns['field_values']))
                    unset($columns['field_values']);
                break;
        }

        //формируем новые и удаленные поля
        foreach($customfields as $k => $v)
        {
            if(!isset($columns[$v->name]))
            {
                $tmp = array('name'=>$v->name, 'type'=>$v->type);
                $classname = $minicck->loadElement($tmp);
                $tmp['columnType'] = (!empty($classname::$columnType)) ? $classname::$columnType : 'varchar(250)';
                $newColumn[] = $tmp;
            }
            else{
                unset($columns[$v->name]);
            }
        }

        //если есть новые поля, то создаем
        if(count($newColumn))
        {
            foreach($newColumn as $v)
            {
                $db->setQuery('ALTER IGNORE TABLE `'.$table.'` ADD `'.$v['name'].'` '.$v['columnType'].' NOT NULL')->execute();
            }
        }

        //если есть удаленные поля, то удаляем
        if(count($columns))
        {
            foreach($columns as $k => $v)
            {
                $db->setQuery('ALTER IGNORE TABLE `'.$table.'` DROP `'.$k.'`')->execute();
            }
        }
    }

    /**
     * @param $itemsModel
     * @throws Exception
     */
    public function onGetContentItems(&$itemsModel)
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $option = $input->getString('option', '');
        $view = $input->getString('view', '');
        $catid = $input->getInt('catid', 0);
        $id = $input->getInt('id', 0);

        if($view == 'category')
        {
            $catid = $id;
        }

        if($catid == 0)
        {
            return;
        }

        $filterData = $app->getUserStateFromRequest('cat_'.$catid.'.minicckfilter', 'minicckfilter', array(), 'array');

        $enable_multi_categories = $this->params->get('enable_multi_categories', 0);


        if(!count($filterData) && !$enable_multi_categories)
        {
            return;
        }

        $filterArticles = $catArticles = array();

        $enableFilter = count($filterData) > 0;

        if($enableFilter)
        {
            if(!self::$customfields)
            {
                $this->setCustomFields();
            }

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('content_id');
            $query->from('#__minicck');

            foreach($filterData as $k=>$v)
            {
                $field = self::getCustomField($k);

                $className = $this->loadElement($field);

                if($className != false && method_exists($className,'buildQuery'))
                {
                    $className::buildQuery($query, $k, $v);
                }
            }

            $filterArticles = $db->setQuery($query)->loadColumn();
            $filterArticles = (empty($filterArticles)) ? array() : $filterArticles;
        }

        if($enable_multi_categories)
        {
            $catArticles = $this->getMultiCatsArticles($catid);
            $catArticles = (empty($catArticles)) ? array() : $catArticles;
        }

        if($enable_multi_categories && $enableFilter)
        {
            $result = array_intersect($catArticles, $filterArticles);
        }
        else if($enable_multi_categories)
        {
            $result = $catArticles;
        }
        else if($enableFilter)
        {
            $result = $filterArticles;
        }
        else
        {
            $result = array();
        }

        if(count($result))
        {
            if($enable_multi_categories)
            {
                $itemsModel->setState('filter.category_id', '');
            }
            $itemsModel->setState('filter.article_id.include', true);
            $itemsModel->setState('filter.article_id', $result);
        }
    }

    /** Подмена модели категории контента.
     * @throws Exception
     */
    public function onAfterRoute()
    {
        if(JFactory::getApplication()->isAdmin()
            || (!$this->params->get('redefine_cat_model',0) && !$this->params->get('enable_multi_categories',0)))
        {
            return;
        }

        $input = JFactory::getApplication()->input;
        $option = $input->getString('option', '');
        $view = $input->getString('view', '');
        $catid = $input->getInt('catid', 0);
        $id = $input->getInt('id', 0);

        if($view == 'category')
        {
            $catid = $id;
        }

        if($option != 'com_content' || $catid == 0)
        {
            return;
        }

        require_once JPATH_ROOT.'/plugins/system/minicck/classes/category.php';
    }


    private function saveMultiCats($multiCats, $articleId)
    {
        $multiCats = array_unique($multiCats);

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->delete('`#__minicck_categories`')
            ->where('`article_id` = '.$db->quote($articleId));
        $db->setQuery($query)->execute();

        if(count($multiCats) > 0)
        {
            foreach($multiCats as $v)
            {
                $v = (int)$v;

                if($v == 0)
                {
                    continue;
                }
                $object = new stdClass();
                $object->category_id = $v;
                $object->article_id = $articleId;
                $db->insertObject('#__minicck_categories', $object);
            }
        }
    }

    private function deleteMultiCats($articleId)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->delete('`#__minicck_categories`')
            ->where('`article_id` = '.$db->quote($articleId));
        $db->setQuery($query)->execute();
    }

    private function getSelectedMultiCats($articleId)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('`category_id`')
            ->from('`#__minicck_categories`')
            ->where('`article_id` = '.$db->quote($articleId));
        $result = $db->setQuery($query)->loadColumn();

        if(!is_array($result) || !count($result))
        {
            return array();
        }
        return $result;
    }

    private function getMultiCatsArticles($categoryId)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('`article_id`')
            ->from('`#__minicck_categories`')
            ->where('`category_id` = '.$db->quote($categoryId));
        $result1 = $db->setQuery($query)->loadColumn();

        if(!is_array($result1) || !count($result1))
        {
            $result1 = array();
        }

        $query->clear()
            ->select('`id`')
            ->from('`#__content`')
            ->where('`catid` = '.$db->quote($categoryId));
        $result2 = $db->setQuery($query)->loadColumn();

        if(!is_array($result2) || !count($result2))
        {
            $result2 = array();
        }

        $result = array_merge($result1, $result2);
        $result = array_unique($result);

        if(!is_array($result) || !count($result))
        {
            return array();
        }
        return $result;
    }
}
