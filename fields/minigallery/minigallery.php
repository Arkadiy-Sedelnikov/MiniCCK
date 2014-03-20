<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
require_once JPATH_ROOT . '/plugins/system/minicck/classes/fields.class.php';

class JFormFieldMinigallery extends MiniCCKFields
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
        self::loadLang('minigallery');
        return JText::_('PLG_MINICCK_MINIGALLERY');
    }

    function getInput()
    {
        if(!defined('PLG_MINICCK_MINIGALLERY_LOADED')){
            define('PLG_MINICCK_MINIGALLERY_LOADED', 1);
            JHtml::_('behavior.modal');
            JHtml::_('behavior.framework');
            JFactory::getDocument()->addScript(JUri::root().'plugins/system/minicck/fields/minigallery/assets/js/script.js');
        }

        self::loadLang('minigallery');

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
        $html = '
            <div class="control-group '.$name.'"'.$hidden.'>
                <label for="'.$id.'_image" class="control-label" title="" >'.$label.'</label>
                <div class="controls">
                <a class="btn btn-small btn-success del_button"
                    style="margin-bottom: 5px"
                    href="#"
                    onclick="minigalleryADDField(this, \''.$id.'\', \''.$fieldname.'\', \''.$name.'\', \''.$directory.'\');
                    return false;">
                    '.JText::_('PLG_MINICCK_MINIGALLERY_ADD_FIELD').'
                </a>
            ';
        if(count($value)>0)
        {
            foreach($value as $k => $v){



                $html .= '
                <div class="minicck_minigallery" style="margin-bottom: 5px">
                    <input type="text" placeholder="image" id="'.$id.'_'.$k.'_image" name="'.$fieldname.'['.$k.'][image]" value="'.$v['image'].'" class="input-big '.$name.'"'.$disabled.'/>
                    <a class="modal btn" title="' . JText::_('JLIB_FORM_BUTTON_SELECT') . '" href="'
                    .  'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=com_content&amp;author=&amp;fieldid=' . $id.'_'.$k . '_image&amp;folder=' . $directory . '"'
                    . ' rel="{handler: \'iframe\', size: {x: 800, y: 600}}">'
                    . JText::_('JLIB_FORM_BUTTON_SELECT') . '
                    </a>
                    <a class="btn hasTooltip" title="Delete"
                        href="#" onclick="minigalleryDeleteField(this); return false;">
                        <i class="icon-remove"></i>
                    </a>
                    <input type="text" placeholder="alt" id="'.$id.'_'.$k.'_alt" name="'.$fieldname.'['.$k.'][alt]" value="'.$v['alt'].'" class="input-big '.$name.'"'.$disabled.'/>
                </div>
                ';
            }
        }
         $html .= '
                </div>
            </div>';
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

        $return = self::loadTemplate('minigallery', array('value' => $value, 'extraparams' => $field['extraparams']));
        return $return;
    }

    static function  cleanValue($field, $value){

        if(count($value)>0)
        {
            foreach($value as $k => $v)
            {
                $v['image'] = strip_tags($v['image']);
                $v['alt'] = strip_tags($v['alt']);
                $value[$k] = $v;
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
                'title' => JText::_('PLG_MINICCK_MINIGALLERY_HEIGTH'),
                'name' => 'heigth',
                'type' => 'text',
                'value' => '',
                'attr' => array(
                    'class' => 'inputbox'
                )
            ),
            array(
                'title' => JText::_('PLG_MINICCK_MINIGALLERY_WIDTH'),
                'name' => 'width',
                'type' => 'text',
                'value' => '',
                'attr' => array(
                    'class' => 'inputbox'
                )
            ),
            array(
                'title' => JText::_('PLG_MINICCK_MINIGALLERY_AUTOPLAY'),
                'name' => 'autoplay',
                'type' => 'select',
                'options' => array(
                    '1' => JText::_('JYES'),
                    '0' => JText::_('JNO')
                ),
                'attr' => array(
                    'class' => 'inputbox'
                )
            ),
            array(
                'title' => JText::_('PLG_MINICCK_MINIGALLERY_CROP'),
                'name' => 'crop',
                'type' => 'select',
                'options' => array(
                    '1' => JText::_('JYES'),
                    '0' => JText::_('JNO')
                ),
                'attr' => array(
                    'class' => 'inputbox'
                )
            ),
            array(
                'title' => JText::_('PLG_MINICCK_MINIGALLERY_DELAY'),
                'name' => 'delay',
                'type' => 'text',
                'value' => '3',
                'attr' => array(
                    'class' => 'inputbox'
                )
            )
        ,
            array(
                'title' => JText::_('PLG_MINICCK_MINIGALLERY_MAX_ZOOM'),
                'name' => 'maxZoom',
                'type' => 'select',
                'value' => '1.5',
                'options' => array(
                    '1.0' => '1',
                    '1.5' => '1.5',
                    '2.0' => '2',
                    '2.5' => '2.5',
                    '3.0' => '3',
                ),
                'attr' => array(
                    'class' => 'inputbox'
                )
            ),
        );

        return $json ? json_encode($extraOptions) : $extraOptions;
    }
}
