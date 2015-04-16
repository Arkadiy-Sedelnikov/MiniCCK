<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
require_once JPATH_ROOT . '/plugins/system/minicck/classes/fields.class.php';

class JFormFieldTable extends MiniCCKFields
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
        self::loadLang('table');
        return JText::_('PLG_MINICCK_TABLE');
    }

    function getInput()
    {
        if(!defined('PLG_MINICCK_TABLE_LOADED')){
            define('PLG_MINICCK_TABLE_LOADED', 1);
            JHtml::_('behavior.modal');
            JHtml::_('behavior.framework');
            JFactory::getDocument()->addScript(JUri::root().'plugins/system/minicck/fields/table/assets/js/script.js');
        }

        self::loadLang('table');

        $name = $this->attributes['name'];
        $label = $this->attributes['label'];
        $type = $this->attributes['type'];
        $disabled = ($this->attributes['disabled']) ? ' disabled="disabled"' : '';
        $hidden = ($this->attributes['hidden']) ? ' style="display: none;"' : '';
        $value = json_decode($this->value, true);

        $field = plgSystemMinicck::getCustomField($name);


        $headers = explode("\n", $field["params"]);
        $countHeaders = count($headers);
        if(!$countHeaders)
        {
            return 'Headers not set';
        }

        $fieldname	= $this->name;
        $id = str_replace(array('][',']','['), array('_', '', '_'), $fieldname);
        $html = '
            <div class="control-group '.$name.'"'.$hidden.'>
                <label class="control-label" title="" >'.$label.'</label>
                <div class="controls">
                <a class="btn btn-small btn-success del_button"
                    style="margin-bottom: 5px"
                    href="#"
                    onclick="tableADDRow(this, \''.$id.'\', \''.$fieldname.'\', \''.$name.'\', '.$countHeaders.');
                    return false;">
                    '.JText::_('PLG_MINICCK_TABLE_ADD_ROW').'
                </a>
            ';
        $html .= '<table id="'.$id.'" class="table"><thead><tr>';
        foreach($headers as $header){
            $html .= '<th>'.$header.'</th>';
        }

        $html .= '<th>'.JText::_('DELETE').'</th></tr></thead><tbody>';

        if(count($value)>0)
        {
            $k = 0;
            foreach($value as $v)
            {
                $html .= '<tr id="tr_'.$id.'_'.$k.'">';
                for($i=0;$i<$countHeaders;$i++){
                    $html .= '<td><input name="'.$fieldname.'['.$k.']['.$i.']" value="'.$v[$i].'"></td>';
                }
                $html .= '<td><a href="#" class="btn" onclick="tableDeleteRow(this); return false;" title="Delete"><i class="icon-remove"></i></a></td>';
                $html .= '</tr>';
                $k++;
            }
        }
        $html .= '</tbody></table>';
        $html .= '</div></div>';
        return $html;
    }

    /** Фронт
     * @param $field
     * @param $value
     * @return string
     */
    static function  getValue($field, $value)
    {
        if(empty($value) || empty($field['params']))
        {
            return '';
        }

        $value = json_decode($value);

        if(!is_array($value) || !count($value))
        {
            return '';
        }

        $params = $field['extraparams'];
        $head = explode("\n", $field['params']);

        $return = self::loadTemplate('table', array('head' => $head, 'body' => $value), 'default', $params);
        return $return;
    }


    static function  cleanValue($field, $value){

        if(count($value)>0)
        {
            foreach($value as $k => $v)
            {
                $value[$k] = htmlspecialchars($v);
            }
        }

        return $value;
    }

    /** Добавляем дополнительные параметры в настройки полей
     * @return string
     */
    static function extraOptions($json = false)
    {
        $extraOptions = array(
            array(
                'title' => JText::_('PLG_MINICCK_TABLE_CLASS'),
                'name' => 'class',
                'type' => 'text',
                'value' => 'table',
                'attr' => array(
                    'class' => 'inputbox'
                )
            ),
        );

        return $json ? json_encode($extraOptions) : $extraOptions;
    }

    public static function prepareToSaveValue($value)
    {
        return json_encode($value);
    }
}
