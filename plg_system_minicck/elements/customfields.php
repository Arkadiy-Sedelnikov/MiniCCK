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
require_once JPATH_ROOT . '/plugins/system/minicck/classes/html.class.php';

class JFormFieldCustomfields extends JFormField
{
    var $type = 'Customfields';

    function getInput()
    {
        JHtml::_('behavior.framework');
        $doc = JFactory::getDocument();
        $doc->addScript(JUri::root() . 'plugins/system/minicck/assets/js/minicck_jq.js');
        $htmlClass = new MiniCCKHTML(0,'');
        $numFields = 1;


        $plugin = JPluginHelper::getPlugin('system', 'minicck');
        $pluginParams = (!empty($plugin->params)) ? json_decode($plugin->params) : new stdClass();
        $typeOptions = array(JHtml::_('select.option', '', JText::_('JSELECT')));

        $fields = JFolder::folders(JPATH_ROOT . '/plugins/system/minicck/fields');



        $scriptArr = $extraOptionsSettings = array();
        foreach($fields as $field)
        {
            $className = $htmlClass->loadElement(array('type' => $field));
            if(!$className)
            {
                continue;
            }
            $typeOptions[] = JHtml::_('select.option', $field, $className::getTitle());

            if(method_exists($className, 'extraOptions'))
            {
                $scriptArr[] = "$field: [".$className::extraOptions(true)."]";
                $extraOptionsSettings[$field] = $className::extraOptions(false);
            }
        }

        $script = "\nvar fieldsExtraOptions = {\n";
        $script .= implode(', ', $scriptArr)."\n";
        $script .= "};\n";

        $doc->addScriptDeclaration($script);

        $fadd = JText::_('PLG_MINICCK_ADD_FIELD');
        $fname = JText::_("PLG_MINICCK_FIELD_NAME");
        $ftitle = JText::_("PLG_MINICCK_FIELD_TITLE");
        $ftype = JText::_("PLG_MINICCK_FIELD_TYPE");
        $fparams = JText::_("PLG_MINICCK_FIELD_PARAMS");
        $fdel = JText::_('PLG_MINICCK_DEL_FIELD');

        $html = <<<HTML
    <input type="button" class="btn btn-small btn-success del_button" value="$fadd" onclick="fieldAdd()" />
    <fieldset class="panelform">
HTML;


        if (empty($pluginParams->customfields))
        {
            $selectType = JHTML::_('select.genericlist', $typeOptions, 'jform[params][customfields][0][type]', 'class="type inputbox" onchange="loadExtraFields(this, 0)"', 'value', 'text');

            $html .= <<<HTML
<div id="field_0" class="field_contayner">
<hr style="clear:both"/>
<div style="width: 50%; float: left">
<div class="control-group">
	<div class="control-label">
        <label for="jform_params_name_0">$fname</label>
	</div>
	<div class="controls">
        <input type="text" id="name_field_1" name="jform[params][customfields][0][name]" id="jform_params_name_0" value="field_1" size="20" class="name inputbox" aria-invalid="false">
	</div>
</div>
<div class="control-group">
	<div class="control-label">
        <label for="jform_params_title_0">$ftitle</label>
	</div>
	<div class="controls">
        <input type="text" name="jform[params][customfields][0][title]" id="jform_params_title_0" value="" size="20" class="title inputbox" aria-invalid="false">
	</div>
</div>
<div class="control-group">
	<div class="control-label">
        <label for="jform_params_title_0">$ftype</label>
	</div>
	<div class="controls">$selectType</div>
</div>
<div class="control-group">
	<div class="control-label">
        <label for="jform_params_params_0">$fparams</label>
	</div>
	<div class="controls">
        <textarea name="jform[params][customfields][0][params]" id="jform_params_params_0" cols="40" rows="5" class="params inputbox"></textarea>
	</div>
</div>
</div>
<div style="width: 50%; float: left" id="extra_params_0" class="extra_params"></div>
<div style="clear: both;"></div>
<input type="button" class="btn btn-danger del-button" value="$fdel" onclick="fieldDel(\'field_0\')">
</div>
HTML;
        }
        else
        {
            $numFields = count($pluginParams->customfields);
            $k = 0;
            foreach ($pluginParams->customfields as $custom)
            {
                $extraparams = '';

                if(!empty($extraOptionsSettings[$custom->type])){

                    foreach($extraOptionsSettings[$custom->type] as $extraparam)
                    {
                        $value = !empty($custom->extraparams->$extraparam['name']) ? $custom->extraparams->$extraparam['name'] : '';

                        $attr = '';
                        if(isset($extraparam['attr']) && is_array($extraparam['attr']) && count($extraparam['attr']))
                        {
                            foreach($extraparam['attr'] as $key => $val)
                            {
                                $attr .= $key.'="'.$val.'" ';
                            }
                        }


                        if($extraparam['type'] == 'textarea')
                        {
                            $input = '<textarea name="jform[params][customfields]['.$k.'][extraparams]['.$extraparam['name'].']" '.$attr.'>'.$value.'</textarea>';
                        }
                        else if($extraparam['type'] == 'select')
                        {
                            $options = array();
                            if(isset($extraparam['options']) && is_array($extraparam['options']) && count($extraparam['options']))
                            {
                                foreach($extraparam['options'] as $key => $val)
                                {
                                    $options[] = JHtml::_('select.option', $key, $val);
                                }
                            }

                            $value = ($value == '') ? 0 : $value;

                            $input = JHTML::_('select.genericlist', $options, 'jform[params][customfields][' . $k . '][extraparams]['.$extraparam['name'].']', $attr, 'value', 'text', $value);
                        }
                        else
                        {
                            $input = '<input type="'.$extraparam['type'].'" name="jform[params][customfields]['.$k.'][extraparams]['.$extraparam['name'].']" value="'.$value.'" '.$attr.'/>';
                        }
                        $extraparams .= <<<HTML
                        <div class="control-group">
                        	<div class="control-label">
                                <label>{$extraparam['title']}</label>
                        	</div>
                        	<div class="controls">
                                $input
                        	</div>
                        </div>
HTML;
                    }
                }

                $selectType = JHTML::_('select.genericlist', $typeOptions, 'jform[params][customfields][' . $k . '][type]', 'class="type inputbox" onchange="loadExtraFields(this, '.$k.')"', 'value', 'text', $custom->type);
                $html .= <<<HTML
<div id="field_$k" class="field_contayner">
<hr style="clear:both"/>
<div style="width: 50%; float: left">
<div class="control-group">
	<div class="control-label">
        <label>$fname</label>
	</div>
	<div class="controls">
        <input type="text" id="name_{$custom->name}" name="jform[params][customfields][$k][name]" value="{$custom->name}" size="20" class="name inputbox" aria-invalid="false" readonly="readonly" onblur="checkEnter(this)"/>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
        <label>$ftitle</label>
	</div>
	<div class="controls">
        <input type="text" name="jform[params][customfields][$k][title]" value="{$custom->title}" size="20" class="title inputbox" aria-invalid="false" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
        <label>$ftype</label>
	</div>
	<div class="controls">$selectType</div>
</div>
<div class="control-group">
	<div class="control-label">
        <label>$fparams</label>
	</div>
	<div class="controls">
        <textarea name="jform[params][customfields][$k][params]" cols="40" rows="5" class="params inputbox">{$custom->params}</textarea>
	</div>
</div>
</div>
<div style="width: 50%; float: left" id="extra_params_$k" class="extra_params">$extraparams</div>
<div style="clear: both;"></div>
<input type="button" class="btn btn-danger del-button" value="$fdel" onclick="fieldDel('field_$k')">
</div>
HTML;
                $k++;
            }
        }
        $html .= <<<HTML
        <input type="hidden" id="numFields" value="$numFields"/>
        </fieldset>
HTML;
        return $html;
    }
}
