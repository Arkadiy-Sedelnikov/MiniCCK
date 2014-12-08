<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
require_once JPATH_ROOT . '/plugins/system/minicck/classes/fields.class.php';

class JFormFieldMcradio extends MiniCCKFields
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
        return JText::_('PLG_MINICCK_RADIO');
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
        if(is_array($field["params"]) && count($field["params"])>0)
        {
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

    static function  getValue($field, $value)
    {
        $return = '';

        if(!empty($field['params'][$value]))
        {
            $return = self::loadTemplate('mcradio', $field['params'][$value]);
        }

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
        $field['selectedValues'] = isset($values[$field['name']]) ? $values[$field['name']] : '';
        $return = self::loadTemplate('mcradio', $field, 'filter');
        return $return;
    }

    static function buildQuery(&$query, $fieldName, $value, $type = 'eq')
    {
        parent::buildQuery($query, $fieldName, $value, 'eq');
    }
}
