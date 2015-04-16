<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
require_once JPATH_ROOT . '/plugins/system/minicck/classes/fields.class.php';

class JFormFieldMcselect extends MiniCCKFields
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
        self::loadLang('mcselect');
        return JText::_('PLG_MINICCK_SELECT');
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

        $params = $field['extraparams'];
        $multi = '';
        if(!empty($params->multi))
        {
            $value = explode(',', $value);
            $multi = ' multiple="multiple"';
            if(!empty($params->rows))
            {
                $multi .= ' rows="'.$params->rows.'"';
            }
        }


        $fieldname	= (empty($params->multi)) ? $this->name : $this->name.'[]';
        $id = str_replace(array('][',']','['), array('_', '', '_'), $this->name);

        $html = '<div class="control-group '.$name.'"'.$hidden.'>';
        $html .= '<label for="'.$id.'" class="control-label" title="" >'.$label.'</label>';
        $html .= '<div class="controls">';
        $html .= JHTML::_('select.genericlist', $options, $fieldname, 'id="'.$id.'"'.$disabled.$multi.' class="type inputbox '.$name.'"', 'value', 'text', $value);
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    static function  getValue($field, $value)
    {
        $params = $field['extraparams'];

        if(!empty($params->multi))
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
        }
        else
        {
            $data = !empty($field['params'][$value]) ? $field['params'][$value] : null;
        }

        $return = '';

        if(!empty($data))
        {
            $return = self::loadTemplate('mcselect', $data, 'default', $params);
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
        $return = self::loadTemplate('mcselect', $field, 'filter');
        return $return;
    }

    static function buildQuery(&$query, $fieldName, $value, $type = 'eq')
    {
        parent::buildQuery($query, $fieldName, $value, 'find_in_set_multi');
    }

    /** Добавляем дополнительные параметры в настройки полей
     * @return string
     */
    static function extraOptions($json = false)
    {
        $extraOptions = array(
            array(
                'title' => JText::_('PLG_MINICCK_MCSELECT_MULTI'),
                'name' => 'multi',
                'type' => 'select',
                'value' => '0',
                'options' => array(
                    '1' => JText::_('JYES'),
                    '0' => JText::_('JNO')
                ),
                'attr' => array(
                    'class' => 'inputbox'
                )
            ),
            array(
                'title' => JText::_('PLG_MINICCK_MCSELECT_ROWS'),
                'name' => 'rows',
                'type' => 'text',
                'value' => '1',
                'attr' => array(
                    'class' => 'inputbox'
                )
            ),
            array(
                'title' => JText::_('PLG_MINICCK_MCSELECT_SEPARATOR'),
                'name' => 'separator',
                'type' => 'text',
                'value' => ' | ',
                'attr' => array(
                    'class' => 'inputbox'
                )
            ),
        );

        return $json ? json_encode($extraOptions) : $extraOptions;
    }

    public static function prepareToSaveValue($value)
    {
        if(is_array($value))
        {
            $value = implode(',', $value);
        }
        return $value;
    }
}
