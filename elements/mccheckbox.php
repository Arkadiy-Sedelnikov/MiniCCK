<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;

class JFormFieldMccheckbox
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
        $value = $this->value;
        $field = plgSystemMinicck::getCustomField($name);
        $fieldname	= $this->name;
        $id = str_replace(array('][',']','['), array('_', '', '_'), $fieldname);
        $html = '<div class="control-group">';
        $html .= '<label for="'.$id.'" class="control-label" title="" >'.$label.'</label>';
        $html .= '<div class="controls" id="'.$id.'">';

        if(is_array($field["params"]) && count($field["params"])>0){
            foreach($field["params"] as $key => $val){
                $id = str_replace(array('][',']','['), '_', $fieldname).$key;
                $checked = (is_array($value) && in_array($key, $value)) ? 'checked="checked"' : '';
                $html .= '
                <label for="'.$id.'" id="'.$id.'-lbl" class="checkbox">'.$val.'
                <input style="float: left;" type="checkbox" name="'.$fieldname.'[]" id="'.$id.'" value="'.$key.'" '.$checked.' class="checkbox"/>
                </label>';
                //$options[] = JHtml::_('select.option', $key,     JText::_($val));
            }
        }

        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    static function  getValue($field, $value){
        if(is_array($value) && count($value)>0){
            $tmp = array();
            foreach($value as $v){
                $tmp[] = $field['params'][$v];
            }
            $return = implode(' | ', $tmp);
        }
        else if(!is_array($value) && !empty($value)){
            $return = (!empty($field['params'][$value])) ? $field['params'][$value] : $value;
        }
        else{
            $return = $value;
        }
        return $return;
    }
}
