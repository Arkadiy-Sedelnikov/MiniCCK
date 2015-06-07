<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.filesystem.folder');

class JFormFieldTypes extends JFormField
{
    var $type = 'Types';
    var $fields;
    var $pluginParams;

    function __construct(){
        $plugin = JPluginHelper::getPlugin('system', 'minicck');
        $this->pluginParams = (!empty($plugin->params)) ? json_decode($plugin->params) : new stdClass();
        $this->fields = (!empty($this->pluginParams->customfields)) ? $this->pluginParams->customfields : array();
        parent::__construct();
    }
    function getInput()
    {
        JHtml::_('behavior.framework');
        $doc = JFactory::getDocument();
        $doc->addScript(JUri::root() . 'plugins/system/minicck/assets/js/minicck_jq.js');

        $numTypes = 1;

        $add = JText::_('PLG_MINICCK_ADD_TYPE_CONTENT');

        $html = <<<HTML
            <input
                type="button"
                class="btn btn-small btn-success del_button"
                value="$add"
                onclick="contentTypeAdd()"
                /><br><br>
            <fieldset class="panelform">
HTML;
        $html .= JHtml::_('bootstrap.startAccordion', 'minicckTypes', array('active' => 'collapseType0'));
        if (empty($this->pluginParams->content_types))
        {
            $html .= JHtml::_('bootstrap.addSlide', 'minicckTypes1', 'New Type', 'collapseType0');
            $html .= $this->loadType(0);
            $html .= JHtml::_('bootstrap.endSlide');
        }
        else
        {
            if(is_array($this->pluginParams->content_types) && count($this->pluginParams->content_types))
            {
                $numTypes = count($this->pluginParams->content_types);
                $k = 0;
                foreach ($this->pluginParams->content_types as $type)
                {
                    $html .= JHtml::_('bootstrap.addSlide', 'minicckTypes1', $type->title, 'collapseType'.$k);
                    $html .= $this->loadType($k, $type);
                    $html .= JHtml::_('bootstrap.endSlide');
                    $k++;
                }
            }
            else
            {
                $numTypes = 1;
                $html .= $this->loadType(0, null);
            }
        }
        $html .= JHtml::_('bootstrap.endAccordion');
        $html .= <<<HTML
        <input type="hidden" id="numTypes" value="$numTypes"/>
        </fieldset>
        <input
                type="button"
                class="btn btn-small btn-success del_button"
                value="$add"
                onclick="contentTypeAdd()"
                />
HTML;

        return $html;
    }


    function loadType($id, $type = null)
    {
        $name = JText::_("PLG_MINICCK_TYPE_CONTENT_NAME");
        $title = JText::_("PLG_MINICCK_TYPE_CONTENT_TITLE");
        $del = JText::_('PLG_MINICCK_DEL_TYPE_CONTENT');
        $tplContent = JText::_('PLG_MINICCK_TYPE_CONTENT_TPL');
        $tplCat = JText::_('PLG_MINICCK_TYPE_CATEGORY_TPL');

        $tname  = (!empty($type->name))  ? $type->name  : 'content_type_'.$id;
        $ttitle = (!empty($type->title)) ? $type->title : '';
        $readonly = (is_null($type)) ? '' : ' readonly="readonly"';
        $options = $this->gerTplOptions();
        $selectedContent = (!empty($type->content_tmpl)) ? $type->content_tmpl : '';
        $selectedCat = (!empty($type->category_tmpl)) ? $type->category_tmpl : '';
        $tplContentSelect = JHTML::_('select.genericlist', $options, 'jform[params][content_types]['.$id.'][content_tmpl]', 'class="content_type_tmpl inputbox"', 'value', 'text', $selectedContent);
        $tplCatSelect = JHTML::_('select.genericlist', $options, 'jform[params][content_types]['.$id.'][category_tmpl]', 'class="category_type_tmpl inputbox"', 'value', 'text', $selectedCat);

        $html = <<<HTML
        <div id="content_type_$id" class="content_type_contayner">
        <div class="control-group" style="width: 50%; float: left;">
        	<div class="control-label">
                <label>$name</label>
        	</div>
        	<div class="controls">
                <input
                    type="text"
                    name="jform[params][content_types][$id][name]"
                    value="{$tname}"
                    size="20"
                    class="name inputbox"
                    aria-invalid="false"
                    onblur="checkEnter(this)"
                    $readonly
                    />
        	</div>
        </div>
        <div class="control-group" style="width: 50%; float: left;">
        	<div class="control-label">
                <label>$tplContent</label>
        	</div>
        	<div class="controls">$tplContentSelect</div>
        </div>
        <div class="control-group" style="width: 50%; float: left;">
        	<div class="control-label">
                <label>$title</label>
        	</div>
        	<div class="controls">
                <input
                    type="text"
                    name="jform[params][content_types][$id][title]"
                    value="{$ttitle}"
                    size="20"
                    class="title inputbox"
                    aria-invalid="false"
                    />
        	</div>
        </div>
        <div class="control-group" style="width: 50%; float: left;">
        	<div class="control-label">
                <label>$tplCat</label>
        	</div>
        	<div class="controls">$tplCatSelect</div>
        </div>
        <div style="clear: both;"></div>
HTML;
        if(count($this->fields))
        {   $i = 0;
            foreach($this->fields as $field)
            {
                $i++;
                $html .= $this->loadField($id, $type, $field);
                if($i % 2 == 0){
                    $html .= '<hr style="clear: both"/>';
                }
            }
        }
        $html .= <<<HTML
        <hr style="clear: both"/>
        <input
            type="button"
            class="btn btn-danger del-button"
            value="$del"
            onclick="contentTypeDel('content_type_$id')"
            />
        </div>
HTML;
        return $html;
    }

    function loadField($typeId, $type, $field)
    {
        $fieldName = $field->name;
        $checkedCat = (!empty($type->fields->$fieldName->category)) ? ' checked="checked"' : '';
        $checkedContent = (!empty($type->fields->$fieldName->content)) ? ' checked="checked"' : '';
        $cat = JText::_('PLG_MINICCK_CAT');
        $content = JText::_('PLG_MINICCK_CONTENT');
        $html = <<<HTML
        <div class="control-group" style="width: 50%; float: left;">
        	<div class="control-label">
                <label style="font-weight: bold;">{$field->title}</label>
        	</div>
        	<div class="controls">
        	<label for="$fieldName-$typeId-category" style="float: left;">$cat
                <input
                    type="checkbox"
                    id="$fieldName-$typeId-category"
                    name="jform[params][content_types][$typeId][fields][$fieldName][category]"
                    value="1"
                    class="field_name inputbox"
                    aria-invalid="false"
                    $checkedCat
                    /></label>
        	<label for="$fieldName-$typeId-content" style="float: left;">$content
                <input
                    type="checkbox"
                    id="$fieldName-$typeId-content"
                    name="jform[params][content_types][$typeId][fields][$fieldName][content]"
                    value="1"
                    class="field_name inputbox"
                    aria-invalid="false"
                    $checkedContent
                    /></label>
        	</div>
        </div>
HTML;
        return $html;
    }

    function gerTplOptions()
    {
        $path = JPATH_ROOT . '/plugins/system/minicck/tmpl';
        $options = array();
        $options[] = JHtml::_('select.option', '', JText::_('JOPTION_USE_DEFAULT'));
        $files = JFolder::files($path, '.', false, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'index.html'));
        if (is_array($files))
        {
            foreach ($files as $file)
            {
                $options[] = JHtml::_('select.option', $file, $file);
            }
        }
        return $options;
    }
}
