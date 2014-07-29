<?php
/**
 * Created by PhpStorm.
 * User: ArkadiyS
 * Date: 25.01.14
 * Time: 23:26
 */
class MiniCCKHTML
{
    private $data;
    private static $_instance;
    private static $customfields;


    public function getFieldValue($articleId, $fname)
    {
        $data = !empty($this->data->$articleId->$fname) ? $this->data->$articleId->$fname : '';
        $result = $this->getValue($fname,$data);
        $result= empty($result) ? '' : $result;
        return $result;
    }

    public function getFieldLabel($fname)
    {
        $result= empty(self::$customfields[$fname]["title"]) ? '' : self::$customfields[$fname]["title"];
        return $result;
    }

    public function get($articleId)
    {
        return isset($this->data->$articleId) ? $this->data->$articleId : null;
    }

    public function set($articleId, $value=null)
    {
        $this->data->$articleId = $value;
    }

    public static function getCustomField($name)
    {
        return self::$customfields[$name];
    }

    public static function getCustomFields()
    {
        return self::$customfields;
    }

    public function __construct($customfields)
    {
        $this->data = empty($this->data) ? new stdClass() : $this->data;
        self::$customfields = $customfields;
    }

    public static function getInstance($customfields)
    {
        if(!(self::$_instance instanceof self))
        {
            self::$_instance = new self($customfields);
        }

        return self::$_instance;
    }

    private function getValue($fname, $value)
    {
        $field = self::getCustomField($fname);
        $className = $this->loadElement($field);

        if($className != false && method_exists($className,'getValue'))
        {
            return $className::getValue($field, $value);
        }
        else
        {
            return $value;
        }
    }

    /** Загружаем элемент, вычисляем имя класса элемента
     * @param $field
     * @return bool|string
     */
    public function loadElement($field)
    {
        $name = $field['type'];
        $file = JPATH_ROOT.'/plugins/system/minicck/fields/'.$name.'/'.$name.'.php';

        if(!JFile::exists($file))
        {
            return false;
        }

        include_once($file);

        $className = 'JFormField'.ucfirst($name);
        return $className;
    }
}