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



                foreach($data as $k => $v)
                {
                    if($k == 'content_type')
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

        if(!self::$contentTypes)
        {
            $this->setContentTypes();
        }

        $articleId = 0;

        if(is_object($data) && !empty($data->id))
        {
            $articleId = (int)$data->id;
        }
        else if(is_array($data) && !empty($data["id"]))
        {
            $articleId = (int)$data["id"];
        }


        $dataMinicck = $this->getData($articleId);

        if($dataMinicck === false)
        {
            return false;
        }
        $options = $this->gerContentTypeOptions();
        if(!$options)
        {
            echo '
            <div class="tab-pane" id="minicck">
                <div class="control-group" id="minicck_content_type_contayner">
                    '.JText::_('PLG_MINICCK_NO_TYPES_CREATED').'
                </div>
                <hr style="clear:both"/>
            </div>
            ';
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
            if(count($type->fields))
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
        $html = <<<HTML
        <div class="tab-pane" id="minicck">
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

        if(($context != 'com_content.article' && $context != 'com_content.category' && $context != 'com_tags.tag')
            || ($context == 'com_content.category' && !$config->allow_in_category)
            || ($context == 'com_tags.tag' && !$config->allow_in_category)
            || ($context == 'com_content.article' && !$config->allow_in_content)
        ){
            return;
        }

        $isTags = ($context == 'com_tags.tag') ? true : false;

        if($isTags && $article->type_alias != 'com_content.article')
        {
            return;
        }

        if($this->params->get('load_object', 0) == 1)
        {
            if(!self::$customfields)
            {
                $this->setCustomFields();
            }
            include_once JPATH_ROOT . '/plugins/system/minicck/classes/html.class.php';
            $article->minicck = MiniCCKHTML::getInstance(self::$customfields);
        }

        $articleId = $isTags ? $article->content_item_id : $article->id;
        $body = $isTags ? 'core_body' : 'text';

        $result = (object)$this->getData($articleId);

        if(empty($result))
        {
            return;
        }

        if(!self::$customfields)
        {
            $this->setCustomFields();
        }

        $fields = self::$customfields;

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

        if($this->params->get('load_object', 0) == 1)
        {
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
        $types = $params->content_types;
        if(!is_array($types) || count($types) == 0)
        {
            return;
        }

        $newParams = array();
        foreach($types as $type)
        {
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

            if(in_array($customfield->type, array('mcselect', 'mcradio', 'mccheckbox')))
            {
                $tmpRows = array();
                if(!empty($customfield->params))
                {
                    $tmpRows = explode("\n", $customfield->params);
                    if(count($tmpRows)>0)
                    {
                        $elements = array();
                        foreach($tmpRows as $tmpRow)
                        {
                          $elements = explode("::", $tmpRow);
                            if(count($elements) > 1)
                            {
                                $newFields[$k]['params'][$elements[0]] = trim($elements[1]);
                            }
                        }
                    }
                }
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
            return false;
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
        unset($columns[$flipColumns['id']],$columns[$flipColumns['content_id']],$columns[$flipColumns['field_values']]);

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
}
