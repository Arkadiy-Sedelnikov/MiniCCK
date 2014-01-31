<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;

class JFormFieldMctextarea
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
        $value = htmlspecialchars_decode($this->value);


        $field = plgSystemMinicck::getCustomField($name);
        $fieldname	= $this->name;
        $id = str_replace(array('][',']','['), array('_', '', '_'), $fieldname);
        $html = '<div class="control-group '.$name.'"'.$hidden.'>';
        $html .= '<label for="'.$id.'" class="control-label" title="" >'.$label.'</label>';
        $html .= '<div class="controls">';
        $html .= '<textarea id="'.$id.'" name="'.$fieldname.'" cols="20" rows="5" class="inputbox '.$name.'"'.$disabled.'>'.$value.'</textarea>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    static function  getValue($field, $value){
        $return = htmlspecialchars_decode($value[0]);
        return $return;
    }

    static function  cleanValue($field, $value){
        $return = htmlspecialchars($value[0]);
        return $return;
    }
}
