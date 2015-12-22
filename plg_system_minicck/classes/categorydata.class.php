<?php
/**
 * Created by PhpStorm.
 * User: ArkadiyS
 * Date: 25.01.14
 * Time: 23:26
 */

jimport('joomla.string.string');
require_once JPATH_ROOT . '/plugins/system/minicck/classes/html.class.php';

class MiniCCKCategoryData
{
    private static $_instance, $data;

    public function __construct()
    {
        self::$data = array();
    }

    public static function getInstance()
    {
        if(!(self::$_instance instanceof self))
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function setData($categoryId, $customfields, $values){
        self::$data[$categoryId] = array(
            'customfields' => $customfields,
            'values' => $values
        );
    }

    public function getData($categoryId){
        return isset(self::$data[$categoryId]) ? self::$data[$categoryId] : null;
    }
    public function getAllData(){
        return self::$data;
    }

    public function getObject($categoryId)
    {
        if(!isset(self::$data[$categoryId]))
        {
            $data = array(
                'customfields' => array(),
                'values' => ''
            );
        }
        else
        {
            $data = self::$data[$categoryId];
        }

        $minicck = &MiniCCKHTML::getInstance($data['customfields']);
        $minicck->set($categoryId, $data['values']);
        return $minicck;
    }
}