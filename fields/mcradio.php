<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;

class JFormFieldMcradio
{
    var $attributes = null;
    var $value = null;
    var $name = null;

    function __construct($name, $attributes, $value){
        $this->attributes = $attributes;
        $this->value = $value[0];
        $this->name = $name;
    }

    function getInput()
    {
        $name = $this->attributes['name'];
        $label = $this->attributes['label'];
        $type = $this->attributes['type'];
        $disabled = ($this->attributes['disabled']) ? ' disabled="disabled"' : '';
        $hidden = ($this->attributes['hidden']) ? ' style="display: none;"' : '';
        $value = $this->value;
        $field = plgSystemMinicck::getCustomField($name);
        $options = array();
        if(is_array($field["params"]) && count($field["params"])>0){
            foreach($field["params"] as $key => $val){
                $options[] = JHtml::_('select.option', $key,     JText::_($val));
            }
        }

        $fieldname	= $this->name;
        $id = str_replace(array('][',']','['), array('_', '', '_'), $fieldname);
        $html = '<div class="control-group '.$name.'"'.$hidden.'>';
        $html .= '<label for="'.$id.'" class="control-label" title="" >'.$label.'</label>';

        $html .=  JHTML::_('select.radiolist', $options, $fieldname, ' id="'.$id.'"'.$disabled.' class="type inputbox '.$name.'"', 'value', 'text', $value);

        $html .= '</div>';
        return $html;
    }

    static function  getValue($field, $value){
        return $field['params'][$value[0]];
    }
}
