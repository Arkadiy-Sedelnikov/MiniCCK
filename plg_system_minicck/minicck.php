<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

class plgSystemMinicck extends JPlugin
{
    private static $customfields = null;
    private static $contentTypes = null;

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

    /** Сохранение данных
     * @param $context
     * @param $article
     * @param $isNew
     * @return bool
     * @throws Exception
     */
    public function onContentAfterSave($context, $article, $isNew)
    {
        if($context !== 'com_content.article' && $context !== 'com_content.form')
            return true;

        $articleId	= $article->id;

        $data = (
            isset($_POST['minicck'])
            && is_array($_POST['minicck'])
            && count($_POST['minicck'])>0
        ) ? $_POST['minicck'] : null;

        if ($articleId && $data)
        {
            if(!self::$customfields)
            {
                $this->setCustomFields();
            }

            try
            {
                $object = new stdClass();
                $object->content_id = $articleId;

                $config = $this->config["params"];

                if(!empty($config->enable_multi_categories))
                {
                    $multiCats = (!empty($data['multi_categories'])) ? $data['multi_categories'] : array();
                    $this->saveMultiCats($multiCats, $articleId);
                }

                foreach($data as $k => $v)
                {
                    if($k == 'content_type' || $k == 'multi_categories')
                    {
                        $object->$k = $v;
                        continue;
                    }

                    $cleanedData = array();

                    $field = self::getCustomField($k);

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

                $db = JFactory::getDbo();

                $query = $db->getQuery(true);
                $query->delete('#__minicck');
                $query->where('content_id = ' . $db->Quote($articleId));
                $db->setQuery($query);

                if (!$db->execute())
                {
                    throw new Exception($db->getErrorMsg());
                }

                if (!$db->insertObject('#__minicck', $object))
                {
                    throw new Exception($db->getErrorMsg());
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

    private function getData($articleId)
    {
        $results = null;

        if($articleId > 0)
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('#__minicck');
            $query->where('content_id = ' . $db->Quote($articleId));
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

    /** Форма в редактировании контента
     * @param $context
     * @param $data
     * @return bool
     */
    function onContentPrepareData($context, $data)
    {
        if (!($context == 'com_content.article'
                && $this->input->getCmd('layout', '') === 'edit')
            || (!is_object($data) && !is_array($data))
        ) return true;

        $config = $this->config["params"];
        $articleId = 0;

        if(!self::$contentTypes)
        {
            $this->setContentTypes();
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

        if(!empty($config->enable_multi_categories))
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

        $dataMinicck = $this->getData($articleId);

        $options = $this->gerContentTypeOptions();

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
        
        if($contentType == '' || empty(self::$contentTypes[$contentType]))
        {
            $contentTypeFields = new stdClass();
        }
        else
        {
            $contentTypeFields = self::$contentTypes[$contentType]->fields;
        }

        $js = 'var minicckTypeFields = [];';
        foreach (self::$contentTypes as $type)
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

        if(!self::$customfields)
        {
            $this->setCustomFields();
        }

        if(count(self::$customfields)>0)
        {
            foreach(self::$customfields as $customfield)
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

                $html .= $element->getInput();
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
        if (!( $this->input->getCmd('option', '') == 'com_content'
            && (
                ($isAdmin && $view == 'article')
                || (!$isAdmin && $view == 'form')
            )
            && $this->input->getCmd('layout', '') === 'edit' )
        ) return;

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

    /** Действия при удалении контента
     * @param	string		The context of the content passed to the plugin (added in 1.6)
     * @param	object		A JTableContent object
     * @since   2.5
     */
    public function onContentAfterDelete($context, $article)
    {

        if ($context != 'com_content.article')
            return true;

        $articleId	= $article->id;
        if ($articleId)
        {
            try
            {
                $db = JFactory::getDbo();

                $query = $db->getQuery(true);
                $query->delete();
                $query->from('#__minicck');
                $query->where('content_id = ' . $db->Quote($articleId));
                $db->setQuery($query);

                if (!$db->query())
                {
                    throw new Exception($db->getErrorMsg());
                }

                $this->deleteMultiCats($articleId);
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
            || ($context == 'com_tags.tag' && !$config->allow_in_tags)
            || ($context == 'com_content.featured' && !$config->allow_in_featured)
            || ($context == 'com_content.article' && !$config->allow_in_content)
        ){
            return;
        }

        $isTags = ($context == 'com_tags.tag') ? true : false;

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
            return;
        }

        $body = $isTags ? 'core_body' : 'text';

        $result = $this->getData($articleId);

        if(empty($result))
        {
            return;
        }

        $result = (object)$result;

        if(!self::$customfields)
        {
            $this->setCustomFields();
        }

        $this->context = $context;
        $isCategory = ($this->context == 'com_content.category');
        $context = $isCategory ? 'category' : 'content';

        if(!self::$contentTypes)
        {
            $this->setContentTypes();
        }

        $content_type = $result->content_type;

        if(empty(self::$contentTypes[$content_type]))
        {
            return;
        }

        $typeFields = self::$contentTypes[$content_type]->fields;

        unset($result->content_type);

        foreach($result as $k => $v)
        {
            if(!isset($typeFields->$k->$context))
            {
                unset($result->$k);
            }
        }

        foreach(self::$customfields as $k => $v)
        {
            if($context == 'content'){
                self::$customfields[$k]['template'] = (isset($typeFields->$k) && isset($typeFields->$k->content_tmpl))
                    ? $typeFields->$k->content_tmpl : 'default.php';
            }
            else
            {
                self::$customfields[$k]['template'] = (isset($typeFields->$k) && isset($typeFields->$k->category_tmpl))
                    ? $typeFields->$k->category_tmpl : 'default.php';
            }
        }

        $fields = self::$customfields;

        if($this->params->get('load_object', 0) == 1)
        {
            include_once JPATH_ROOT . '/plugins/system/minicck/classes/html.class.php';
            $article->minicck = MiniCCKHTML::getInstance(self::$customfields);
            $result->content_type = $content_type;
            $article->minicck->set($articleId, $result);
        }
        else
        {
            $layout = $this->getLayout($content_type);
            $position = $isCategory ? $this->params->get('position_cat', 'top') : $this->params->get('position_content', 'top');

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

    static function getCustomFields()
    {
        return self::$customfields;
    }

    static function getCustomField($name)
    {
        return self::$customfields[$name];
    }

    private function setContentTypes()
    {
        $params = $this->config['params'];

        if(!isset($params->content_types))
        {
            return;
        }

        $types = $params->content_types;
        if(!is_array($types) || count($types) == 0)
        {
            return;
        }

        $newParams = array();
        foreach($types as $type)
        {
            if(count($type->fields)){
                foreach($type->fields as $key => $field){
                    if(!(isset($field->category) || isset($field->content))){
                        unset($type->fields->$key);
                    }
                }
            }
            $newParams[$type->name] = $type;
        }
        self::$contentTypes = $newParams;
    }

    private function setCustomFields()
    {
        $customfields = $this->config['params'];
        $customfields = $customfields->customfields;
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
        self::$customfields = $newFields;
    }

    private function getLayout($contentType)
    {
        $isCategory = ($this->context == 'com_content.category');

        $layout = ($isCategory) ? $this->params->get('layout', 'default_cat.php') : $this->params->get('layout', 'default.php');

        if(!self::$contentTypes)
        {
            $this->setContentTypes();
        }

        if($isCategory)
        {
            if(!empty(self::$contentTypes[$contentType]->category_tmpl))
            {
                $layout = self::$contentTypes[$contentType]->category_tmpl;
            }
        }
        else
        {
            if(!empty(self::$contentTypes[$contentType]->content_tmpl))
            {
                $layout = self::$contentTypes[$contentType]->content_tmpl;
            }
        }

        return $layout;
    }

    private function getValue($fname, $value)
    {
        $field = self::getCustomField($fname);
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

    private function gerContentTypeOptions()
    {
        if(!self::$contentTypes)
        {
            $this->setContentTypes();
        }

        if (is_array(self::$contentTypes) && count(self::$contentTypes))
        {
            $options = array();
            $options[] = JHtml::_('select.option', '', JText::_('JSELECT'));

            foreach (self::$contentTypes as $type)
            {
                $options[] = JHtml::_('select.option', $type->name, $type->title);
            }
            return $options;
        }
        return false;
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
        $customfields = $params->customfields;
        $content_types = $params->content_types;

        if(!is_array($customfields) || count($customfields) == 0)
        {
            throw new Exception('Custom Fields is Empty');
        }

        if(!is_array($content_types) || count($content_types) == 0)
        {
            throw new Exception('Content Types is Empty');
        }

        $app = JFactory::getApplication();

        //формируем новые и удаленные поля
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

        //формируем новые и удаленные поля
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
        $customfields = $params->customfields;

        if(!is_array($customfields) || count($customfields) == 0)
        {
            throw new Exception('Custom Fields is Empty');
        }

        $newColumn = $oldColumn = array();
        $db = JFactory::getDbo();
        $table = $db->replacePrefix('#__minicck');
        $query = $db->getQuery(true);
        $query->select('COLUMN_NAME')
            ->from('INFORMATION_SCHEMA.COLUMNS')
            ->where('TABLE_NAME = '.$db->quote($table));
        $db->setQuery($query);
        $columns = $db->loadColumn();

        if(!is_array($columns) || count($columns) == 0)
        {
            throw new Exception('Table Columns is Empty');
            return false;
        }

        include_once JPATH_ROOT . '/plugins/system/minicck/classes/html.class.php';
        $minicck = MiniCCKHTML::getInstance(self::$customfields);

        $flipColumns = array_flip($columns);

        //удаляем из массива служебные поля
        if(isset($flipColumns['id']))
            unset($columns[$flipColumns['id']]);
        if(isset($flipColumns['content_id']))
            unset($columns[$flipColumns['content_id']]);
        if(isset($flipColumns['field_values']))
            unset($columns[$flipColumns['field_values']]);
        if(isset($flipColumns['content_type']))
            unset($columns[$flipColumns['content_type']]);


        //формируем новые и удаленные поля
        foreach($customfields as $k => $v)
        {
            if(!in_array($v->name, $columns))
            {
                $tmp = array('name'=>$v->name, 'type'=>$v->type);
                $classname = $minicck->loadElement($tmp);
                $tmp['columnType'] = (!empty($classname::$columnType)) ? $classname::$columnType : 'varchar(250)';
                $newColumn[] = $tmp;
            }
            else{
                unset($columns[$flipColumns[$v->name]]);
            }
        }

        //если есть новые поля, то создаем
        if(count($newColumn))
        {
            foreach($newColumn as $v)
            {
                $db->setQuery('ALTER IGNORE TABLE `#__minicck` ADD `'.$v['name'].'` '.$v['columnType'].' NOT NULL')->execute();
            }
        }

        //если есть удаленные поля, то удаляем
        if(count($columns))
        {
            foreach($columns as $v)
            {
                $db->setQuery('ALTER IGNORE TABLE `#__minicck` DROP `'.$v.'`')->execute();
            }
        }

        return true;
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
