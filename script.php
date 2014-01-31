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
        $db = JFactory::getDBO();
        $fields = $db->getTableColumns('#__minicck');
        if (!array_key_exists('field', $fields))
        {
            //добавляем недостающие поля

            $query = "ALTER TABLE `#__minicck` ADD `field` VARCHAR( 50 ) NOT NULL AFTER `content_id`";
            $db->setQuery($query);
            $db->execute();

            $query = "ALTER TABLE `#__minicck` ADD INDEX (`content_id`)";
            $db->setQuery($query);
            $db->execute();

            $query = "ALTER TABLE `#__minicck` ADD INDEX (`field`)";
            $db->setQuery($query);
            $db->execute();

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

            //конвертируем и вносим данные
            $query = $db->getQuery(true);
            $query->insert('`#__minicck`');
            $query->columns
            (
                array
                (
                    $db->quoteName('content_id'),
                    $db->quoteName('field'),
                    $db->quoteName('field_values'),
                )
            );

            foreach ($data as $v)
            {
                $content_id = $v->content_id;
                $values = json_decode($v->field_values);
                foreach($values as $key => $value)
                {
                    if(is_array($value))
                    {
                        foreach($value as $vv)
                        {
                            $query->values($db->quote($content_id) . ',' . $db->quote($key) . ',' . $db->quote($vv));
                        }
                    }
                    else
                    {
                        $query->values($db->quote($content_id) . ',' . $db->quote($key) . ',' . $db->quote($value));
                    }
                }
            }

            $db->setQuery($query);
            $db->execute();
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