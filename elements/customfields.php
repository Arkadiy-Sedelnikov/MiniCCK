<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldCustomfields extends JFormField
{

    var $type = 'Customfields';

    function getInput()
    {
        JHtml::_('behavior.framework');
        $doc = JFactory::getDocument();
        $doc->addScript(JUri::root().'plugins/system/minicck/assets/js/minicck_jq.js');

        $numFields = 1;
        $plugin = JPluginHelper::getPlugin('system', 'minicck');
        $pluginParams = (!empty($plugin->params)) ? json_decode($plugin->params) : new stdClass();
        $typeOptions = array(
            JHtml::_('select.option', 'mcselect',     JText::_('PLG_MINICCK_SELECT')),
            JHtml::_('select.option', 'mcradio',      JText::_('PLG_MINICCK_RADIO')),
            JHtml::_('select.option', 'mccheckbox',   JText::_('PLG_MINICCK_CHECKBOX')),
            JHtml::_('select.option', 'mctext',       JText::_('PLG_MINICCK_TEXT')),
            JHtml::_('select.option', 'mctextarea',   JText::_('PLG_MINICCK_TEXTAREA')),
            JHtml::_('select.option', 'mcimage',      JText::_('PLG_MINICCK_IMAGE')),
        );
        $html = '';
$html = '<input type="button" class="btn btn-small btn-success del_button" value="'.JText::_('PLG_MINICCK_ADD_FIELD').'" onclick="fieldAdd()"><fieldset class="panelform">';

if (empty($pluginParams->customfields)) {

$html .= '
<div id="field_0" class="field_contayner">
<hr style="clear:both"/>
<div class="control-group">
	<div class="control-label">
        <label for="jform_params_name_0">'.JText::_("PLG_MINICCK_FIELD_NAME").'</label>
	</div>
	<div class="controls">
        <input type="text" name="jform[params][customfields][0][name]" id="jform_params_name_0" value="field_1" size="20" class="name inputbox" aria-invalid="false">
	</div>
</div>
<div class="control-group">
	<div class="control-label">
        <label for="jform_params_title_0">'.JText::_("PLG_MINICCK_FIELD_TITLE").'</label>
	</div>
	<div class="controls">
        <input type="text" name="jform[params][customfields][0][title]" id="jform_params_title_0" value="" size="20" class="title inputbox" aria-invalid="false">
	</div>
</div>
<div class="control-group">
	<div class="control-label">
        <label for="jform_params_title_0">'.JText::_("PLG_MINICCK_FIELD_TYPE").'</label>
	</div>
	<div class="controls">
        '.JHTML::_('select.genericlist', $typeOptions, 'jform[params][customfields][0][type]', 'class="type inputbox"', 'value', 'text').'
	</div>
</div>
<div class="control-group">
	<div class="control-label">
        <label for="jform_params_params_0">'.JText::_("PLG_MINICCK_FIELD_PARAMS").'</label>
	</div>
	<div class="controls">
        <textarea name="jform[params][customfields][0][params]" id="jform_params_params_0" cols="40" rows="5" class="params inputbox"></textarea>
	</div>
</div>
<input type="button" class="btn btn-danger del-button" value="'.JText::_('PLG_MINICCK_DEL_FIELD').'" onclick="fieldDel(\'field_0\')">
</div>
';

        }
        else{
        $numFields = count($pluginParams->customfields);
        $k = 0;
        foreach ($pluginParams->customfields as $custom) {

$html .= '
<div id="field_'.$k.'" class="field_contayner">
<hr style="clear:both"/>
<div class="control-group">
	<div class="control-label">
        <label>'.JText::_("PLG_MINICCK_FIELD_NAME").'</label>
	</div>
	<div class="controls">
        <input type="text" name="jform[params][customfields]['.$k.'][name]" value="'.$custom->name.'" size="20" class="name inputbox" aria-invalid="false">
	</div>
</div>
<div class="control-group">
	<div class="control-label">
        <label>'.JText::_("PLG_MINICCK_FIELD_TITLE").'</label>
	</div>
	<div class="controls">
        <input type="text" name="jform[params][customfields]['.$k.'][title]" value="'.$custom->title.'" size="20" class="title inputbox" aria-invalid="false">
	</div>
</div>
<div class="control-group">
	<div class="control-label">
        <label>'.JText::_("PLG_MINICCK_FIELD_TYPE").'</label>
	</div>
	<div class="controls">
        '.JHTML::_('select.genericlist', $typeOptions, 'jform[params][customfields]['.$k.'][type]', 'class="type inputbox"', 'value', 'text', $custom->type).'
	</div>
</div>
<div class="control-group">
	<div class="control-label">
        <label>'.JText::_("PLG_MINICCK_FIELD_PARAMS").'</label>
	</div>
	<div class="controls">
        <textarea name="jform[params][customfields]['.$k.'][params]" cols="40" rows="5" class="params inputbox">'.$custom->params.'</textarea>
	</div>
</div>
<input type="button" class="btn btn-danger del-button" value="'.JText::_('PLG_MINICCK_DEL_FIELD').'" onclick="fieldDel(\'field_'.$k.'\')">
</div>
';
       $k++;
        }
    }
        $html .= '<input type="hidden" id="numFields" value="'.$numFields.'"/></fieldset>';
        return $html;
    }
}
