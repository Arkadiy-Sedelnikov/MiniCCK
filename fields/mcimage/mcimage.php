<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
require_once JPATH_ROOT . '/plugins/system/minicck/classes/fields.class.php';

class JFormFieldMcimage extends MiniCCKFields
{
    var $attributes = null;
    var $value = null;
    var $name = null;

    function __construct($name, $attributes, $value){
        $this->attributes = $attributes;
        $this->value = $value;
        $this->name = $name;
    }

    static function getTitle()
    {
        return JText::_('PLG_MINICCK_IMAGE');
    }

    function getInput()
    {




        if(!defined('PLG_MINICCK_MCIMAGE_LOADED')){
            define('PLG_MINICCK_MCIMAGE_LOADED', 1);
            JHtml::_('behavior.modal');
            // Build the script.
            $script = array();
            $script[] = '	function jInsertFieldValue(value, id) {';
            $script[] = '		var old_value = document.id(id).value;';
            $script[] = '		if (old_value != value) {';
            $script[] = '			var elem = document.id(id);';
            $script[] = '			elem.value = value;';
            $script[] = '			elem.fireEvent("change");';
            $script[] = '			if (typeof(elem.onchange) === "function") {';
            $script[] = '				elem.onchange();';
            $script[] = '			}';
            $script[] = '			jMediaRefreshPreview(id);';
            $script[] = '		}';
            $script[] = '	}';
            JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
        }



        $name = $this->attributes['name'];
        $label = $this->attributes['label'];
        $type = $this->attributes['type'];
        $disabled = ($this->attributes['disabled']) ? ' disabled="disabled"' : '';
        $hidden = ($this->attributes['hidden']) ? ' style="display: none;"' : '';
        $value = $this->value;


        $field = plgSystemMinicck::getCustomField($name);
        $directory = trim($field["params"]);
        $directory = (!empty($directory)) ? $directory : '/images';
        if(substr($directory, 0, 1) !== '/') $directory = '/'.$directory;
        if(!is_dir(JPATH_ROOT.$directory))$directory = '/images/';
        if(substr($directory, -1) !== '/') $directory = $directory.'/';

        $fieldname	= $this->name;
        $id = str_replace(array('][',']','['), array('_', '', '_'), $fieldname);
        $html = '<div class="control-group '.$name.'"'.$hidden.'>';
        $html .= '<label for="'.$id.'" class="control-label" title="" >'.$label.'</label>';
        $html .= '<div class="controls">';
        $html .= '<input type="text" id="'.$id.'" name="'.$fieldname.'" value="'.$value.'" class="input-small '.$name.'"'.$disabled.'>';
        $html .= '<a class="modal btn" title="' . JText::_('JLIB_FORM_BUTTON_SELECT') . '" href="'
            //  index.php?option=com_media&view=images&tmpl=component&asset=com_content&author=&fieldid=jform_images_image_intro&folder=
            .  'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=com_content&amp;author=&amp;fieldid=' . $id . '&amp;folder=' . $directory . '"'
            . ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
        $html .= JText::_('JLIB_FORM_BUTTON_SELECT') . '</a><a class="btn hasTooltip" title="' . JText::_('JLIB_FORM_BUTTON_CLEAR') . '" href="#" onclick="';
        $html .= 'jInsertFieldValue(\'\', \'' . $id . '\');';
        $html .= 'return false;';
        $html .= '">';
        $html .= '<i class="icon-remove"></i></a>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    static function  getValue($field, $value)
    {
        if(!empty($value) && substr($value, 0, 1) !== '/')
        {
            $value = '/'.$value;
        }
        else{
            $value = '';
        }

        $return = self::loadTemplate('mcimage', array('value' => $value, 'extraparams' => $field['extraparams']));
        return $return;
    }

    /** Добавляем дополнительные параметры в настройки полей
     * @return string
     */
    static function extraOptions($json = false)
    {
        $extraOptions = array(
            array(
                'title' => 'Высота',
                'name' => 'heigth',
                'type' => 'text',
                'value' => '',
                'attr' => array(
                    'class' => 'inputbox'
                )
            ),
            array(
                'title' => 'Ширина',
                'name' => 'width',
                'type' => 'text',
                'value' => '',
                'attr' => array(
                    'class' => 'inputbox'
                )
            ),
            array(
                'title' => 'Бордюр',
                'name' => 'border',
                'type' => 'select',
                'options' => array(
                    '0' => '0px',
                    '1' => '1px',
                    '2' => '2px'
                ),
                'attr' => array(
                    'class' => 'inputbox'
                )
            )
        );

        return $json ? json_encode($extraOptions) : $extraOptions;
    }
}
