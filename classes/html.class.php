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


    public function getFieldValue($fname)
    {
        $data = $this->data->$fname;
        $result = $this->getValue($fname,$data);
        $result= empty($result) ? '' : $result;
        return $result;
    }

    public function getFieldLabel($fname)
    {
        $result= empty(self::$customfields[$fname]["title"]) ? '' : self::$customfields[$fname]["title"];
        return $result;
    }

    public function get($property)
    {
        return $this->$property;
    }

    public function set($property, $value=null)
    {
        $this->$property = $value;
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
    private function loadElement($field)
    {
        if(!is_file(JPATH_ROOT.'/plugins/system/minicck/elements/'.$field['type'].'.php'))
            return false;
        include_once(JPATH_ROOT.'/plugins/system/minicck/elements/'.$field['type'].'.php');

        $className = 'JFormField'.ucfirst($field['type']);
        return $className;
    }
}