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
    var $fieldTemplates;

    function __construct(){
        $plugin = JPluginHelper::getPlugin('system', 'minicck');
        $this->pluginParams = (!empty($plugin->params)) ? json_decode($plugin->params) : new stdClass();
        $this->fields = (!empty($this->pluginParams->customfields)) ? $this->pluginParams->customfields : array();
        $this->fieldTemplates = $this->getFieldTemplates();
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
                onclick="contentTypeAdd('content')"
                /><br><br>
            <fieldset class="panelform">
HTML;
        $html .= JHtml::_('bootstrap.startAccordion', 'minicckTypes', array('active' => 'collapseType0'));
        if (empty($this->pluginParams->content_types))
        {
            $html .= JHtml::_('bootstrap.addSlide', 'minicckTypes', 'New Type', 'collapseType0');
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
                    $title = (!empty($type->title)) ? $type->title : 'Empty Title';
                    $html .= JHtml::_('bootstrap.addSlide', 'minicckTypes', $title, 'collapseType'.$k);
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
                onclick="contentTypeAdd('content')"
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
        <hr style="clear: both"/>
HTML;
        if(count($this->fields))
        {   $i = 0;
            foreach($this->fields as $field)
            {
                $i++;
                $html .= $this->loadField($id, $type, $field);
                $html .= '<hr style="clear: both"/>';
            }
        }
        $html .= <<<HTML

        <input
            type="button"
            class="btn btn-danger del-button"
            value="$del"
            onclick="contentTypeDel('content_type_$id', 'content')"
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
        $tplContent = JText::_('PLG_MINICCK_TYPE_CONTENT_TPL');
        $tplCat = JText::_('PLG_MINICCK_TYPE_CATEGORY_TPL');
        $fname = "jform[params][content_types][$typeId][fields][$fieldName]";
        $selectedCatTmpl = (!empty($type->fields->$fieldName->category_tmpl)) ? $type->fields->$fieldName->category_tmpl : '';
        $selectedArticleTmpl = (!empty($type->fields->$fieldName->content_tmpl)) ? $type->fields->$fieldName->content_tmpl : '';
        $catTmpl = JHTML::_('select.genericlist', $this->fieldTemplates[$field->type], $fname.'[category_tmpl]', 'data-field="'.$fieldName.'" class="field_category_type_tmpl inputbox"', 'value', 'text', $selectedCatTmpl);
        $itemTmpl = JHTML::_('select.genericlist', $this->fieldTemplates[$field->type], $fname.'[content_tmpl]', 'data-field="'.$fieldName.'" class="field_article_type_tmpl inputbox"', 'value', 'text', $selectedArticleTmpl);

        $html = <<<HTML
        <div class="control-group">
        	<div class="control-label">
                <label style="font-weight: bold;">{$field->title}</label>
        	</div>
        	<div class="controls row">
        	    <div class="span1">
                    <label for="$fieldName-$typeId-category">$cat</label>
                </div>
                <div class="span1">
                    <input
                        type="checkbox"
                        id="$fieldName-$typeId-category"
                        name="{$fname}[category]"
                        value="1"
                        class="field_name inputbox"
                        aria-invalid="false"
                        $checkedCat
                        />
                </div>
        	    <div class="span1">
        	    <label for="$fieldName-$typeId-content">$content</label>
                </div>
        	    <div class="span1">
                    <input
                        type="checkbox"
                        id="$fieldName-$typeId-content"
                        name="{$fname}[content]"
                        value="1"
                        class="field_name inputbox"
                        aria-invalid="false"
                        $checkedContent
                        />
                </div>
        	    <div class="span2">
        	    <label for="$fieldName-$typeId-content1" style="width: 100%; max-width: 100%;">$tplContent</label>
                </div>
        	    <div class="span2">
                    $itemTmpl
                </div>
        	    <div class="span2">
        	        <label for="$fieldName-$typeId-content1" style="width: 100%; max-width: 100%;">$tplCat</label>
                </div>
                <div class="span2">
                    $catTmpl
                </div>
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

    function getFieldTemplates()
    {
        $return = array();
        $path = JPATH_ROOT . '/plugins/system/minicck/fields';
        $folders = JFolder::folders($path);

        if(is_array($folders) && count($folders))
        {
            foreach($folders as $v)
            {
                $options = array();
                $files = JFolder::files($path.'/'.$v.'/tmpl', '.', false, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'index.html'));
                if (is_array($files) && count($files))
                {
                    foreach ($files as $file)
                    {
                        $options[] = JHtml::_('select.option', $file, $file);
                    }
                }
                $return[$v] = $options;
            }
        }

        return $return;
    }
}
