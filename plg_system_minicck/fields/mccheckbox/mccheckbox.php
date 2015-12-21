<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
require_once JPATH_ROOT . '/plugins/system/minicck/classes/fields.class.php';

class JFormFieldMccheckbox extends MiniCCKFields
{
    var $attributes = null;
    var $value = null;
    var $name = null;
    static $columnType = 'varchar(250)';

    function __construct($name, $attributes, $value){
        $this->attributes = $attributes;
        $this->value = $value;
        $this->name = $name;
    }

    static function getTitle()
    {
        return JText::_('PLG_MINICCK_CHECKBOX');
    }

    function getInput($entityType='content')
    {
        $name = $this->attributes['name'];
        $label = $this->attributes['label'];
        $type = $this->attributes['type'];
        $disabled = ($this->attributes['disabled']) ? ' disabled="disabled"' : '';
        $hidden = ($this->attributes['hidden']) ? ' style="display: none;"' : '';
        $value = explode(',', $this->value);
        $field = plgSystemMinicck::getCustomField($name, $entityType);
        $fieldname	= $this->name;
        $id = str_replace(array('][',']','['), array('_', '', '_'), $fieldname);
        $html = '<div class="control-group '.$name.'"'.$hidden.'>';
        $html .= '<label for="'.$id.'" class="control-label" title="" >'.$label.'</label>';
        $html .= '<div class="controls" id="'.$id.'">';

        if(is_array($field["params"]) && count($field["params"])>0){
            foreach($field["params"] as $key => $val){
                $id = str_replace(array('][',']','['), '_', $fieldname).$key;
                $checked = (is_array($value) && in_array($key, $value)) ? 'checked="checked"' : '';
                $html .= '
                <label for="'.$id.'" id="'.$id.'-lbl" class="checkbox">'.$val.'
                <input style="float: left;" type="checkbox" name="'.$fieldname.'[]" id="'.$id.'" value="'.$key.'" '.$checked.' class="checkbox '.$name.'"'.$disabled.'/>
                </label>';
                //$options[] = JHtml::_('select.option', $key,     JText::_($val));
            }
        }

        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    static function getValue($field, $value)
    {
        $value = explode(',', $value);
        if(is_array($value) && count($value)>0){
            $tmp = array();
            foreach($value as $v){
                $tmp[] = $field['params'][$v];
            }
            $data = $tmp;
        }
        else if(!is_array($value) && !empty($value)){
            $data = (!empty($field['params'][$value])) ? array($field['params'][$value]) : array($value);
        }
        else{
            $data = array($value);
        }

        $return = self::loadTemplate($field, $data);

        return $return;
    }

    static function prepareParams($params)
    {
        $data = array();
        $tmpRows = explode("\n", $params);
        if(count($tmpRows)>0)
        {
            foreach($tmpRows as $tmpRow)
            {
                $elements = explode("::", $tmpRow);
                if(count($elements) > 1)
                {
                    $data[$elements[0]] = isset($elements[1]) ? trim($elements[1]) : '';
                }
            }
        }
        return $data;
    }


    static function getFilterInput($field, $category_id)
    {
        $values = JFactory::getApplication()->getUserState('cat_'.$category_id.'.minicckfilter', array());
        $field['params'] = self::prepareParams($field['params']);
        $value = isset($values[$field['name']]) ? $values[$field['name']] : array();
        $field['selectedValues'] = (!is_array($value)) ? array($value) : $value;
        $return = self::loadTemplate($field, $field, 'filter');
        return $return;
    }

    static function buildQuery(&$query, $fieldName, $value, $type = 'eq')
    {
        parent::buildQuery($query, $fieldName, $value, 'find_in_set_multi');
    }
}
