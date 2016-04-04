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

class JFormFieldCategorytypes extends JFormField
{
    var $type = 'Categorytypes';
    var $fields;
    var $pluginParams;
    var $fieldTemplates;
    var $prefix = 'category';

    function __construct(){
        $plugin = JPluginHelper::getPlugin('system', 'minicck');
        $this->pluginParams = (!empty($plugin->params)) ? json_decode($plugin->params) : new stdClass();
        $this->fields = (!empty($this->pluginParams->category_customfields)) ? $this->pluginParams->category_customfields : array();
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
                onclick="contentTypeAdd('{$this->prefix}')"
                /><br><br>
            <fieldset class="panelform">
HTML;
        $html .= JHtml::_('bootstrap.startAccordion', $this->prefix.'_minicckTypes', array('active' => 'collapseType0'));
        if (empty($this->pluginParams->category_types))
        {
            $html .= JHtml::_('bootstrap.addSlide', $this->prefix.'_minicckTypes', 'New Type', 'collapseType0');
            $html .= $this->loadType(0);
            $html .= JHtml::_('bootstrap.endSlide');
        }
        else
        {
            if(is_array($this->pluginParams->category_types) && count($this->pluginParams->category_types))
            {
                $numTypes = count($this->pluginParams->category_types);
                $k = 0;
                foreach ($this->pluginParams->category_types as $type)
                {
                    $title = (!empty($type->title)) ? $type->title : 'Empty Title';
                    $html .= JHtml::_('bootstrap.addSlide', $this->prefix.'_minicckTypes', $title, 'collapseType'.$k);
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
                onclick="contentTypeAdd('{$this->prefix}')"
                />
HTML;

        return $html;
    }


    function loadType($id, $type = null)
    {
        $name = JText::_("PLG_MINICCK_TYPE_CONTENT_NAME");
        $title = JText::_("PLG_MINICCK_TYPE_CONTENT_TITLE");
        $del = JText::_('PLG_MINICCK_DEL_TYPE_CONTENT');

        $tplCat = JText::_('PLG_MINICCK_TYPE_TPL');

        $tname  = (!empty($type->name))  ? $type->name  : 'category_type_'.$id;
        $ttitle = (!empty($type->title)) ? $type->title : '';
        $readonly = (is_null($type)) ? '' : ' readonly="readonly"';
        $options = $this->getTplOptions();
        $selectedContent = (!empty($type->content_tmpl)) ? $type->content_tmpl : '';
        $selectedCat = (!empty($type->category_tmpl)) ? $type->category_tmpl : '';
        $tplContentSelect = JHTML::_('select.genericlist', $options, 'jform[params][category_types]['.$id.'][content_tmpl]', 'class="content_type_tmpl inputbox"', 'value', 'text', $selectedContent);
        $tplCatSelect = JHTML::_('select.genericlist', $options, 'jform[params][category_types]['.$id.'][category_tmpl]', 'class="category_type_tmpl inputbox"', 'value', 'text', $selectedCat);

        $html = <<<HTML
        <div id="{$this->prefix}_type_$id" class={$this->prefix}_type_contayner">
        <div class="control-group" style="width: 50%; float: left;">
        	<div class="control-label">
                <label>$name</label>
        	</div>
        	<div class="controls">
                <input
                    type="text"
                    name="jform[params][{$this->prefix}_types][$id][name]"
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
                <label>$tplCat</label>
        	</div>
        	<div class="controls">$tplCatSelect</div>
        </div>
        <div class="control-group" style="width: 50%; float: left;">
        	<div class="control-label">
                <label>$title</label>
        	</div>
        	<div class="controls">
                <input
                    type="text"
                    name="jform[params][{$this->prefix}_types][$id][title]"
                    value="{$ttitle}"
                    size="20"
                    class="title inputbox"
                    aria-invalid="false"
                    />
        	</div>
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
            onclick="contentTypeDel('{$this->prefix}_type_$id', '{$this->prefix}')"
            />
        </div>
HTML;
        return $html;
    }

    function loadField($typeId, $type, $field)
    {
        $fieldName = $field->name;
        $checkedCat = (!empty($type->fields->$fieldName->show)) ? ' checked="checked"' : '';
        $show = JText::_('PLG_MINICCK_TYPE_FIELD_SHOW');
        $tplCat = JText::_('PLG_MINICCK_TYPE_TPL');
        $fieldTemplates = isset($this->fieldTemplates[$field->type]) ? $this->fieldTemplates[$field->type] : array();
        $fname = "jform[params][{$this->prefix}_types][$typeId][fields][$fieldName]";
        $selectedCatTmpl = (!empty($type->fields->$fieldName->category_tmpl)) ? $type->fields->$fieldName->category_tmpl : '';
        $selectedArticleTmpl = (!empty($type->fields->$fieldName->content_tmpl)) ? $type->fields->$fieldName->content_tmpl : '';
        $catTmpl = JHTML::_('select.genericlist', $fieldTemplates, $fname.'[category_tmpl]', 'data-field="'.$fieldName.'" class="field_category_type_tmpl inputbox"', 'value', 'text', $selectedCatTmpl);
        $html = <<<HTML
        <div class="control-group">
        	<div class="control-label">
                <label style="font-weight: bold;">{$field->title}</label>
        	</div>
        	<div class="controls row">
        	    <div class="span1">
        	        <label for="$fieldName-$typeId-content1" style="width: 100%; max-width: 100%;">$show</label>
                </div>
                <div class="span1">
                    <input
                        type="checkbox"
                        id="$fieldName-$typeId-category-{$this->prefix}"
                        name="{$fname}[show]"
                        value="1"
                        class="field_name inputbox"
                        aria-invalid="false"
                        $checkedCat
                        />
                </div>
        	    <div class="span1">
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

    function getTplOptions()
    {
        $path = JPATH_ROOT . '/plugins/system/minicck/tmpl/'.$this->prefix;
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
