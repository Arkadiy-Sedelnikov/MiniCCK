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
                />
            <fieldset class="panelform">
HTML;

        if (empty($this->pluginParams->content_types))
        {
            $html .= $this->loadType(0);
        }
        else
        {
            if(is_array($this->pluginParams->content_types) && count($this->pluginParams->content_types))
            {
                $numTypes = count($this->pluginParams->content_types);
                $k = 0;
                foreach ($this->pluginParams->content_types as $type)
                {
                    $html .= $this->loadType($k, $type);
                    $k++;
                }
            }
            else
            {
                $numTypes = 1;
                $html .= $this->loadType(0, null);
            }
        }
        $html .= <<<HTML
        <input type="hidden" id="numTypes" value="$numTypes"/>
        </fieldset>
HTML;
        return $html;
    }


    function loadType($id, $type = null)
    {
        $name = JText::_("PLG_MINICCK_TYPE_CONTENT_NAME");
        $title = JText::_("PLG_MINICCK_TYPE_CONTENT_TITLE");
        $del = JText::_('PLG_MINICCK_DEL_TYPE_CONTENT');
        $tpl = JText::_('PLG_MINICCK_TYPE_CONTENT_TPL');

        $tname  = (!empty($type->name))  ? $type->name  : 'content_type_'.$id;
        $ttitle = (!empty($type->title)) ? $type->title : '';
        $readonly = (is_null($type)) ? '' : ' readonly="readonly"';
        $options = $this->gerTplOptions();
        $selected = (!empty($type->tmpl)) ? $type->tmpl : '';
        $tplSelect = JHTML::_('select.genericlist', $options, 'jform[params][content_types]['.$id.'][tmpl]', 'class="content_type_tmpl inputbox"', 'value', 'text', $selected);

        $html = <<<HTML
        <div id="content_type_$id" class="content_type_contayner">
        <hr style="clear:both"/>
        <div class="control-group">
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
        <div class="control-group">
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
        <div class="control-group">
        	<div class="control-label">
                <label>$tpl</label>
        	</div>
        	<div class="controls">$tplSelect</div>
        </div>
HTML;
        if(count($this->fields))
        {
            foreach($this->fields as $field)
            {
                $html .= $this->loadField($id, $type, $field);
            }
        }
        $html .= <<<HTML
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
        $checked = (isset($type->fields->$fieldName)) ? ' checked="checked"' : '';
        $html = <<<HTML
        <div class="control-group">
        	<div class="control-label">
                <label>{$field->title}</label>
        	</div>
        	<div class="controls">
                <input
                    type="checkbox"
                    name="jform[params][content_types][$typeId][fields][$fieldName]"
                    value="1"
                    class="field_name inputbox"
                    aria-invalid="false"
                    $checked
                    />
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
