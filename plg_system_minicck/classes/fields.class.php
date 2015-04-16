<?php
/**
 * Created by PhpStorm.
 * User: ArkadiyS
 * Date: 25.01.14
 * Time: 23:26
 */
class MiniCCKFields
{
    public static function loadTemplate($fieldName, $data, $type='default', $params=null)
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

    static function buildQuery(&$query, $fieldName, $value, $type = 'eq')
    {
        $db = JFactory::getDbo();

        if((is_string($value) && empty($value)) || (is_array($value) && !count($value)))
        {
            return;
        }

        switch($type)
        {
            case 'like' :
                    $query->where($db->quoteName($fieldName).' LIKE '.$db->quote('%'.$value.'%'));
                break;

            case 'find_in_set_multi' :
                    $q = array();
                    foreach($value as $v)
                    {
                        $q[] = '(FIND_IN_SET('.$db->quote($v).', '.$db->quoteName($fieldName).')>0)';
                    }
                    $q = '('.implode(' OR ', $q).')';
                    $query->where($q);
                break;

            case 'find_in_set' :
                    $query->where('(FIND_IN_SET('.$db->quote($value).', '.$db->quoteName($fieldName).')>0)');
                break;

            case 'eq' :
            default :
                    $query->where($db->quoteName($fieldName).' = '.$db->quote($value));
                break;
        }




    }
}