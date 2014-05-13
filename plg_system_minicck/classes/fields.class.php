<?php
/**
 * Created by PhpStorm.
 * User: ArkadiyS
 * Date: 25.01.14
 * Time: 23:26
 */
class MiniCCKFields
{
    public static function loadTemplate($fieldName, $data, $type='default')
    {
        //переопределение шаблона поля
        $template = JFactory::getApplication()->getTemplate();

        $tmpl = JPATH_ROOT. '/templates/'.$template.'/html/plg_system_minicck/fields/'.$fieldName.'/'.$type.'.php';

        if(!JFile::exists($tmpl))
        {
            $tmpl = JPATH_ROOT.'/plugins/system/minicck/fields/'.$fieldName.'/tmpl/'.$type.'.php';
        }

        //подключение шаблона
        ob_start();
        require $tmpl;
        $html = ob_get_clean();

        return $html;
    }

    static function loadLang($fieldName, $path='')
    {
        if(empty($path))
        {
            $path = JPATH_ROOT.'/plugins/system/minicck/fields/'.$fieldName;
        }

        $filename = 'plg_minicck_field_'.$fieldName;

        JFactory::getLanguage()->load($filename, $path);
    }

    static function prepareParams($params)
    {
        return trim($params);
    }
}