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
        $this->value = $value;
        $this->name = $name;
    }

    function getInput()
    {

        $name = $this->attributes['name'];
        $label = $this->attributes['label'];
        $type = $this->attributes['type'];
        $value = htmlspecialchars_decode($this->value);


        $field = plgSystemMinicck::getCustomField($name);
        $fieldname	= $this->name;
        $id = str_replace(array('][',']','['), array('_', '', '_'), $fieldname);
        $html = '<div class="control-group">';
        $html .= '<label for="'.$id.'" class="control-label" title="" >'.$label.'</label>';
        $html .= '<div class="controls">';
        $html .= '<textarea id="'.$id.'" name="'.$fieldname.'" cols="20" rows="5" class="inputbox">'.$value.'</textarea>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    static function  getValue($field, $value){
        $return = htmlspecialchars_decode($value);
        return $return;
    }

    static function  cleanValue($field, $value){
        $return = htmlspecialchars($value);
        return $return;
    }
}
