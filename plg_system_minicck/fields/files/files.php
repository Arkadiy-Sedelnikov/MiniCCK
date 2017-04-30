<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
require_once JPATH_ROOT . '/plugins/system/minicck/classes/fields.class.php';

class JFormFieldFiles extends MiniCCKFields
{
    var $attributes = null;
    var $value = null;
    var $name = null;
    static $columnType = 'text';
    protected $uid;

    function __construct($name, $attributes, $value){
        $this->attributes = $attributes;
        $this->value = $value;
        $this->name = $name;

    }

    static function getTitle()
    {
        self::loadLang('files');
        return JText::_('PLG_MINICCK_FILES');
    }

    function getInput($entityType='content')
    {
        self::loadLang('files');

        $name = $this->attributes['name'];
        $label = $this->attributes['label'];
        $hidden = ($this->attributes['hidden']) ? ' style="display: none;"' : '';
        $value = $this->value;
        $field = plgSystemMinicck::getCustomField($name, $entityType);
        $fieldname	= $this->name;
        $id = str_replace(array('][',']','['), array('_', '', '_'), $fieldname);

        $dir = JPATH_ROOT;
        if(!empty($field["extraparams"]) && !empty($field["extraparams"]->root)){
            $dir .= strpos($field["extraparams"]->root, '/') === 0 ? $field["extraparams"]->root : '/'.$field["extraparams"]->root;
        }
        $pattern = !empty($field["extraparams"]) && !empty($field["extraparams"]->pattern) ? $field["extraparams"]->pattern : '';

        JHtml::_('jquery.framework');
        JHtml::_('behavior.modal', 'a.modal-button');

        JFactory::getDocument()->addScriptDeclaration('
            function selectThisFile_'.$name.'(filePath)
            {
                var div = jQuery(\'#minicck-field-file-articles-'.$name.'\');
                var span = jQuery(\'<span>\').attr(\'onclick\', \'jQuery(this).parents("div.article").remove();\').text(\' x\');
                var input = jQuery(\'<input>\').attr(\'type\', \'hidden\').attr(\'name\', \''.$fieldname.'[]\').val(filePath);
                var article = jQuery(\'<div>\').addClass(\'file btn btn-small btn-success\').text(filePath).append(span).append(input);
                div.html(\'\').append(article);
                jQuery(\'#myModal-'.$name.'\').modal(\'hide\')
            }
        ');

        $html = '<div class="control-group '.$name.'"'.$hidden.'>';
        $html .= '<label for="'.$id.'" class="control-label" title="" >'.$label.'</label>';
        $html .= '<div class="controls files-field">';

        $html .= '
            <a href="#myModal-'.$name.'" role="button" class="btn" data-toggle="modal">
		        <span class="icon-tree"></span> '.JText::_('PLG_MINICCK_FILES_SELECT').'
	        </a>
	        <br><br>
            <div id="minicck-field-file-articles-'.$name.'">';

            if(!empty($value)){
                $html .= '
                <div class="file btn btn-small btn-success">'
                    .$value.'<span onclick="jQuery(this).parents(\'div.file\').remove();"> x</span>
                    <input name="'.$this->name.'[]" value="'.$value.'" type="hidden">
                </div>';
            }
        $html .= '</div>';

        $html .= '
            <div id="myModal-'.$name.'" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel-'.$name.'" aria-hidden="true">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel-'.$name.'">Modal header</h3>
              </div>
              <div class="modal-body">
                <div style="display:block; overflow: auto; height: 400px;">'.$this->showdir($dir, $pattern).'</div>
              </div>
              <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
              </div>
            </div>
        ';

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

        self::loadLang('files');

        $fpath = $value;
        $parts = explode('/', $fpath);
        $fname = array_pop($parts);
        $return = self::loadTemplate($field, array('fpath' => $fpath, 'fname' => $fname));
        return $return;
    }


    /** Добавляем дополнительные параметры в настройки полей
     * @return string
     */
    static function extraOptions($json = false)
    {
        $extraOptions = array(
            array(
                'title' => 'Корневая директория',
                'name' => 'root',
                'type' => 'text',
                'value' => '',
                'attr' => array(
                    'class' => 'inputbox'
                )
            ),
            array(
                'title' => 'Паттерн',
                'name' => 'pattern',
                'type' => 'text',
                'value' => '',
                'attr' => array(
                    'class' => 'inputbox'
                )
            )
        );

        return $json ? json_encode($extraOptions) : $extraOptions;
    }

    private function showdir
    (
        $dir,
        $pattern='',
        $folderOnly = false,
        $showRoot = false,
        $level = 0,  // do not use!!!
        $ef = ''     // do not use!!!
    )
    {
        $html = '';
        $fieldName = $this->attributes['name'];

        if ((int)$level == 0)
        {
            $dir = realpath($dir);
            $ef = ($showRoot ? realpath($dir . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR : $dir . DIRECTORY_SEPARATOR);
        }
        if (!file_exists($dir))
            return '';

        if ($showRoot && (int)$level == 0)
        {
            $html = '<ul id="' . $this->uid . '" class="av-folderlist level-0' . '">';
            $subdir = $this->showdir($dir, $pattern, $folderOnly, $showRoot, $level + 1, $ef);
            $name = substr(strrchr($dir, DIRECTORY_SEPARATOR), 1);
            $html .= '<li>' . ($subdir ? '<span class="av-folderlist-tree"></span>' : '') . '<span class="av-folderlist-label" path="' . $name . '">' . $name . '</span>' . $subdir . '</li>';
            $html .= '</ul>';
        }
        else
        {
            $list = scandir($dir);
            if (is_array($list))
            {
                $list = array_diff($list, array('.', '..'));
                if ($list)
                {
                    $folders = array();
                    $files = array();

                    foreach ($list as $name)
                        if (is_dir($dir . DIRECTORY_SEPARATOR . $name)) {$folders[] = $name;} else {$files[] = $name;}

                    if (!($folderOnly && !$folders) || !(!$folders || !$files))
                        $html .= '<ul' . ((int)$level == 0 ? ' id="' . $this->uid . '"' : '') . ' class="' . ((int)$level == 0 ? 'av-folderlist ' : '') . 'level-' . (int)$level . '">';

                    sort($folders);
                    sort($files);

                    foreach ($folders as $name)
                    {
                        $fpath = $dir . DIRECTORY_SEPARATOR . $name;
                        $subdir = $this->showdir($fpath, $pattern, $folderOnly, $showRoot, $level + 1, $ef);
                        $fpath = str_replace($ef, '', $fpath);
                        $html .= '<li class="av-folderlist-item av-folderlist-dir">' . ($subdir ? '<span class="av-folderlist-tree"></span>' : '') . '<span class="av-folderlist-label">' . $name . '</span>' . $subdir . '</li>';
                    }

                    foreach ($files as $name)
                    {
                        if(!empty($pattern) && !preg_match($pattern, $name)){
                            continue;
                        }
                        $fpath = $dir . DIRECTORY_SEPARATOR . $name;
                        $fpath = str_replace(str_replace('\\', '/', JPATH_ROOT).'/', '', str_replace('\\', '/', $fpath));
                        $ext = substr(strrchr($name, '.'), 1);
                        $html .= '<li class="av-folderlist-item av-folderlist-file' . ($ext ? ' av-folderlist-file-' . $ext : '') . '"><span class="av-folderlist-label" style="cursor: pointer;" onclick="selectThisFile_'.$fieldName.'(\'' . $fpath . '\')">' . $name . '</span></li>';
                    }


                    if (!($folderOnly && !$folders) || !(!$folders || !$files))
                        $html .= '</ul>';
                    unset($folders, $files, $fpath, $ext);
                }
            }
        }

        return $html;
    }
}
