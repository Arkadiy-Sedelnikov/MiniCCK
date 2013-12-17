<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;

class plgSystemMinicck extends JPlugin
{
    private static $customfields = null;
    private static $contentTypes = null;

    private
        $input,
        $isAdmin,
        $config;


	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

        $this->input = new JInput();
        $this->config = $config;
        $this->isAdmin = JFactory::getApplication()->isAdmin();
		$this->loadLanguage();
	}

    function onAfterDispatch()
    {
        $option = $this->input->getCmd('option', '');
        $view = $this->input->getCmd('view', '');
        $layout = $this->input->getCmd('layout', '');

        if($this->isAdmin || ($option == 'com_content' && $view == 'form' && $layout == 'edit'))
        {
            $document = JFactory::getDocument();
            $document->addScript('/plugins/system/minicck/assets/js/minicck_jq.js');
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
        ) ? $_POST['minicck'] : false;

        if ($articleId && $data)
        {
            try
            {
                $cleanedData = array();
                foreach($data as $k => $v)
                {
                    $field = self::getCustomField($k);

                    $className = $this->loadElement($field);
                    if($className != false && method_exists($className,'cleanValue'))
                    {
                        $cleanedData[$k] = $className::cleanValue($field, $v);
                    }
                    else
                    {
                        if(is_array($v) && count($v)>0)
                        {
                            foreach($v as $val)
                            {
                                $cleanedData[$k][] = htmlspecialchars(strip_tags($val));
                            }
                        }
                        else
                        {
                            $cleanedData[$k] = htmlspecialchars(strip_tags($v));
                        }
                    }
                }

                $data = json_encode($cleanedData);
                $db = JFactory::getDbo();

                $query = $db->getQuery(true);
                $query->delete('#__minicck');
                $query->where('content_id = ' . $db->Quote($articleId));
                $db->setQuery($query);
                if (!$db->query())
                {
                    throw new Exception($db->getErrorMsg());
                }

                $query->clear();
                $query->insert('#__minicck');
                $query->columns(array($db->quoteName('content_id'), $db->quoteName('field_values')));
                $query->values($articleId.', '.$db->quote($data));
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

        $results = null;

        if($articleId > 0)
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('field_values');
            $query->from('#__minicck');
            $query->where('content_id = ' . $db->Quote($articleId));
            $db->setQuery($query);
            $results = $db->loadResult();

            if ($db->getErrorNum())
            {
                $this->_subject->setError($db->getErrorMsg()); return false;
            }
        }

        $dataMinicck = (!empty($results)) ? json_decode($results, true) : array();

        $options = $this->gerContentTypeOptions();
        if(!$options)
        {
            echo JText::_('PLG_MINICCK_NO_TYPES_CREATED');
            return true;
        }

        $contentType = (!empty($dataMinicck['content_type'])) ? $dataMinicck['content_type'] : '';
        if($contentType == '')
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
                if(!is_file(JPATH_ROOT.'/plugins/system/minicck/elements/'.$customfield['type'].'.php'))
                    continue;

                include_once(JPATH_ROOT.'/plugins/system/minicck/elements/'.$customfield['type'].'.php');

                $className = 'JFormField'.ucfirst($customfield['type']);

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
        if($context != 'com_content.article') return;

        $db = JFactory::getDbo();
        $q = $db->getQuery(true);
        $q->select('field_values')
            ->from('#__minicck')
            ->where('content_id = '.(int)$article->id);
        $db->setQuery($q, 0, 1);
        $result = $db->loadResult();

        if(empty($result)) return;

        $result = json_decode($result);

        if(!is_object($result) || !count($result)) return;

        if($this->params->get('load_css', '1') == 1){
            $doc = JFactory::getDocument();
            $doc->addStyleSheet(JURI::base(true).'/plugins/system/minicck/minicck/minicck.css');
        }

        if(!self::$customfields)
        {
            $this->setCustomFields();
        }
        // populate
        $rownr = 0;

        $fields = self::$customfields;

        $layout = $this->getLauout($result->content_type);

        unset($result->content_type);

        $position = $this->params->get('position', 'top');

        ob_start();
        require JPATH_ROOT.'/plugins/system/minicck/tmpl/'.$layout;
        $html = ob_get_clean();

        if($position == 'top')
        {
            $article->text = $html.$article->text;
        }
        else
        {
            $article->text = $article->text.$html;
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
        $params = json_decode($this->config['params']);
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
        $customfields = json_decode($this->config['params']);
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

    private function getLauout($contentType)
    {
        $layout = $this->params->get('layout', 'default.php');

        if(!self::$contentTypes)
        {
            $this->setContentTypes();
        }

        if(!empty(self::$contentTypes[$contentType]->tmpl))
        {
            $layout = self::$contentTypes[$contentType]->tmpl;
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
        if(!is_file(JPATH_ROOT.'/plugins/system/minicck/elements/'.$field['type'].'.php'))
            return false;
        include_once(JPATH_ROOT.'/plugins/system/minicck/elements/'.$field['type'].'.php');

        $className = 'JFormField'.ucfirst($field['type']);
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
}
