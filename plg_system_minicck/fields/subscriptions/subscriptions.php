<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
require_once JPATH_ROOT . '/plugins/system/minicck/classes/fields.class.php';

class JFormFieldSubscriptions extends MiniCCKFields
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
        self::loadLang('subscriptions');
        return JText::_('PLG_MINICCK_SUBS');
    }

    function getInput()
    {
        self::loadLang('subscriptions');
        $options = $this->getSelectOptions();
        $name = $this->attributes['name'];
        $label = $this->attributes['label'];
        $type = $this->attributes['type'];
        $disabled = ($this->attributes['disabled']) ? ' disabled="disabled"' : '';
        $hidden = ($this->attributes['hidden']) ? ' style="display: none;"' : '';
        $value = $this->value;
        $field = plgSystemMinicck::getCustomField($name);
        $fieldname	= $this->name;
        $id = str_replace(array('][',']','['), array('_', '', '_'), $fieldname);
        $html = '<div class="control-group '.$name.'"'.$hidden.'>';
        $html .= '<label for="'.$id.'" class="control-label" title="" >'.$label.'</label>';
        $html .= '<div class="controls">';
        $html .= JHTML::_('select.genericlist', $options, $fieldname, 'id="'.$id.'"'.$disabled.' class="type inputbox '.$name.'"', 'value', 'text', $value);
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

        $input = JFactory::getApplication()->input;
        $Itemid = $input->getInt('Itemid', null);
        $Itemid = empty($Itemid) ? '' : $Itemid;

        $href = JRoute::_( 'index.php?option=com_akeebasubs&view=level&slug='.$value.'&format=html&layout=default');

        $params = $field['extraparams'];
        $data= new stdClass();
        $data->link = $href;
        $data->text = (!empty($params->text)) ? $params->text : 'Download';
        $data->class = (!empty($params->class)) ? ' class="'.$params->class.'"' : '';

        $return = self::loadTemplate('subscriptions', $data);
        return $return;
    }

    /** Добавляем дополнительные параметры в настройки полей
     * @return string
     */
    static function extraOptions($json = false)
    {
        $extraOptions = array(
            array(
                'title' => JText::_('PLG_MINICCK_SUBS_TEXT'),
                'name' => 'text',
                'type' => 'text',
                'value' => '',
                'attr' => array(
                    'class' => 'inputbox'
                )
            ),
            array(
                'title' => JText::_('PLG_MINICCK_SUBS_CLASS'),
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

    /**
     * @param $component = 1 - Akeeba Subscription, 2 - Akeeba Release System
     */
    private function getSelectOptions()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $isAdmin = JFactory::getApplication()->isAdmin();

        $query->select('title as name, slug as value');
        $query->from('#__akeebasubs_levels');
        $query->order('name ASC');

        if(!$isAdmin){
            $query->where('created_by = '.(int)JFactory::getUser()->id);
        }
        $db->setQuery((string)$query);
        $result = $db->loadObjectList();

        $options	= array();
        $options[]	= JHtml::_('select.option', '', JText::_('PLG_MINICCK_SUBS_SELECT'));
        foreach($result as $v){
            $options[]	= JHtml::_('select.option', $v->value, $v->name);
        }

        return $options;
    }
}
