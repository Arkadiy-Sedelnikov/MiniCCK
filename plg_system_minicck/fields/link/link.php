<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
require_once JPATH_ROOT . '/plugins/system/minicck/classes/fields.class.php';

class JFormFieldLink extends MiniCCKFields
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
        self::loadLang('link');
        return JText::_('PLG_MINICCK_LINK');
    }

    function getInput($entityType='content')
    {
        self::loadLang('link');
        $name = $this->attributes['name'];
        $label = $this->attributes['label'];
        $type = $this->attributes['type'];
        $disabled = ($this->attributes['disabled']) ? ' disabled="disabled"' : '';
        $hidden = ($this->attributes['hidden']) ? ' style="display: none;"' : '';
        $value = $this->value;
        $field = plgSystemMinicck::getCustomField($name, $entityType);
        $fieldname	= $this->name;
        $id = str_replace(array('][',']','['), array('_', '', '_'), $fieldname);
        $html = '<div class="control-group '.$name.'"'.$hidden.'>';
        $html .= '<label for="'.$id.'" class="control-label" title="" >'.$label.'</label>';
        $html .= '<div class="controls">';
        $html .= '<input type="text" id="'.$id.'" name="'.$fieldname.'" value="'.$value.'" class="inputbox '.$name.'"'.$disabled.'>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    static function  getValue($field, $value)
    {
        if(empty($value))
        {
            return '';
        }

        $params = $field['extraparams'];
        $data= new stdClass();
        $data->link = $value;
        $data->text = (!empty($params->text)) ? $params->text : $value;
        $data->class = (!empty($params->class)) ? ' class="'.$params->class.'"' : '';

        $return = self::loadTemplate($field, $data);
        return $return;
    }

    /** Добавляем дополнительные параметры в настройки полей
     * @return string
     */
    static function extraOptions($json = false)
    {
        $extraOptions = array(
            array(
                'title' => JText::_('PLG_MINICCK_LINK_TEXT'),
                'name' => 'text',
                'type' => 'text',
                'value' => '',
                'attr' => array(
                    'class' => 'inputbox'
                )
            ),
            array(
                'title' => JText::_('PLG_MINICCK_LINK_CLASS'),
                'name' => 'class',
                'type' => 'text',
                'value' => '',
                'attr' => array(
                    'class' => 'inputbox'
                )
            )
        );

        return $json ? json_encode($extraOptions) : $extraOptions;
    }
}
