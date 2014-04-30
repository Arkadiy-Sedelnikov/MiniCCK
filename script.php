<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;

class plgSystemMinicckInstallerScript
{
    /**
     * Метод для обновления компонента.
     *
     * @param   object  $parent  Класс, который вызывает этом метод.
     *
     * @return  void
     */
    public function update($parent)
    {
        $plugin = JPluginHelper::getPlugin('system', 'minicck');
        $params = json_decode($plugin->params);
        $customfields = $params->customfields;

        if(is_array($customfields) && count($customfields) > 0)
        {
            $newColumn = array();
            $db = JFactory::getDbo();
            $table = $db->replacePrefix('#__minicck');
            $query = $db->getQuery(true);
            $query->select('COLUMN_NAME')
                ->from('INFORMATION_SCHEMA.COLUMNS')
                ->where('TABLE_NAME = '.$db->quote($table));
            $db->setQuery($query);
            $columns = $db->loadColumn();

            if(!is_array($columns) || count($columns) == 0 || !in_array('field_values', $columns))
            {
                return;
            }

            include_once JPATH_ROOT . '/plugins/system/minicck/classes/html.class.php';
            $minicck = MiniCCKHTML::getInstance(array());

            $flipColumns = array_flip($columns);

            //удаляем из массива служебные поля
            unset($columns[$flipColumns['id']],$columns[$flipColumns['content_id']],$columns[$flipColumns['field_values']]);

            $db->setQuery('ALTER IGNORE TABLE `#__minicck` ADD `content_type` varchar(50) NOT NULL')->execute();

            //формируем новые и удаленные поля
            foreach($customfields as $v)
            {
                if(!in_array($v->name, $columns))
                {
                    $tmp = array('name'=>$v->name, 'type'=>$v->type);
                    $classname = $minicck->loadElement($tmp);
                    $tmp['columnType'] = (!empty($classname::$columnType)) ? $classname::$columnType : 'varchar(250)';
                    $newColumn[] = $tmp;
                    $db->setQuery('ALTER IGNORE TABLE `#__minicck` ADD `'.$v->name.'` '.$tmp['columnType'].' NOT NULL')->execute();
                }
            }

            //запрашиваем все данные
            $query = $db->getQuery(true);
            $query->select('*')
                ->from('`#__minicck`');
            $data = $db->setQuery($query)->loadObjectList();

            if(!$data || !is_array($data) || !count($data))
            {
                return;
            }

            //очищаем таблицу
            $query = "TRUNCATE `#__minicck`";
            $db->setQuery($query);
            $db->execute();

            foreach ($data as $v)
            {
                $values = (array)json_decode($v->field_values);
                $object = new stdClass();
                $object->content_id = $v->content_id;
                $object->content_type = $values['content_type'];

                foreach($newColumn as $column)
                {
                    $columnName = $column['name'];

                    if(isset($values[$columnName]))
                    {
                        $value = $values[$columnName];

                        $classname = $minicck->loadElement($column);

                        if(method_exists($classname, 'prepareToSaveValue'))
                        {
                            $value = $classname::prepareToSaveValue($value);
                        }
                        else
                        {
                            $value = is_array($value) ? implode(',', $value) : $value;
                        }

                        $object->$columnName = $value;
                    }
                }
                $db->insertObject('#__minicck', $object);
            }
            $db->setQuery('ALTER IGNORE TABLE `#__minicck` DROP `field_values`')->execute();
        }
    }

    /**
     * Метод для установки компонента.
     *
     * @param   object  $parent  Класс, который вызывает этом метод.
     *
     * @return  void
     */
    public function install($parent)
    {
        //$parent->getParent()->setRedirectURL('index.php?option=com_helloworld');
    }

    /**
     * Метод для удаления компонента.
     *
     * @param   object  $parent  Класс, который вызывает этом метод.
     *
     * @return  void
     */
    public function uninstall($parent)
    {
        //echo '<p>' . JText::_('COM_HELLOWORLD_UNINSTALL_TEXT') . '</p>';
    }

    /**
     * Метод, который исполняется до install/update/uninstall.
     *
     * @param   object  $type    Тип изменений: install, update или discover_install
     * @param   object  $parent  Класс, который вызывает этом метод. Класс, который вызывает этом метод.
     *
     * @return  void
     */
    public function preflight($type, $parent)
    {
        //echo '<p>' . JText::_('PLG_MINICCK_PREFLIGHT_' . strtoupper($type) . '_TEXT') . '</p>';
    }

    /**
     * Метод, который исполняется после install/update/uninstall.
     *
     * @param   object  $type    Тип изменений: install, update или discover_install
     * @param   object  $parent  Класс, который вызывает этом метод. Класс, который вызывает этом метод.
     *
     * @return  void
     */
    public function postflight($type, $parent)
    {
        //echo '<p>' . JText::_('PLG_MINICCK_POSTFLIGHT_' . strtoupper($type) . '_TEXT') . '</p>';
    }
}