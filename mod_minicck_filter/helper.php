<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_ROOT . '/plugins/system/minicck/classes/html.class.php';

class ModMinicckFilterHelper
{
	public static function getFields($params, $category_id)
	{
		$fields = array();
        $enabledFields = $params->get('searchfields', array());

        if(!is_array($enabledFields) && !count($enabledFields))
        {
            return $fields;
        }

        $plugin = JPluginHelper::getPlugin('system', 'minicck');
        $pluginParams = new JRegistry($plugin->params);
        $customfields = $pluginParams->get('customfields', array());

        if(!is_array($customfields) && !count($customfields))
        {
            return $fields;
        }

        foreach ($customfields as $k => $v)
        {
            $customfields[$k] = (array)$v;
        }

        $minicck = MiniCCKHTML::getInstance($customfields);

        foreach ($customfields as $v)
        {
            if(!in_array($v['name'], $enabledFields))
            {
                continue;
            }

            $className = $minicck->loadElement($v);

            if($className != false && method_exists($className,'getFilterInput'))
            {
                $fields[] = $className::getFilterInput($v, $category_id);
            }
        }

		return $fields;
	}
}
