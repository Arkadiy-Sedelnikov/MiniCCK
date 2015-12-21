<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
require_once JPATH_ROOT . '/plugins/system/minicck/classes/fields.class.php';

class JFormFieldMctextarea extends MiniCCKFields
{
    var $attributes = null;
    var $value = null;
    var $name = null;
    static $columnType = 'text';

    function __construct($name, $attributes, $value){
        $this->attributes = $attributes;
        $this->value = $value;
        $this->name = $name;
    }

    static function getTitle()
    {
        return JText::_('PLG_MINICCK_TEXTAREA');
    }

    function getInput($entityType='content')
    {

        $name = $this->attributes['name'];
        $label = $this->attributes['label'];
        $type = $this->attributes['type'];
        $disabled = ($this->attributes['disabled']) ? ' disabled="disabled"' : '';
        $hidden = ($this->attributes['hidden']) ? ' style="display: none;"' : '';
        $value = htmlspecialchars_decode($this->value);
        $field = plgSystemMinicck::getCustomField($name, $entityType);
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
        $return = self::loadTemplate($field, htmlspecialchars_decode($value));
        return $return;
    }

    static function  cleanValue($field, $value){
        $return = htmlspecialchars($value);
        return $return;
    }

    static function getFilterInput($field, $category_id)
    {
        $values = JFactory::getApplication()->getUserState('cat_'.$category_id.'.minicckfilter', array());
        $field['params'] = self::prepareParams($field['params']);
        $field['selectedValues'] = isset($values[$field['name']]) ? $values[$field['name']] : '';
        $return = self::loadTemplate($field, $field, 'filter');
        return $return;
    }

    static function buildQuery(&$query, $fieldName, $value, $type = 'like')
    {
        parent::buildQuery($query, $fieldName, $value, 'like');
    }
}
