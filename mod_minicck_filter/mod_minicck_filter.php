<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the helper.
require_once __DIR__ . '/helper.php';

$input = JFactory::getApplication()->input;
$option = $input->getString('option', '');
$view = $input->getString('view', '');
$catid = $input->getInt('catid', 0);
$id = $input->getInt('id', 0);
$allowedCats = $params->get('categories', array());

if($view == 'category')
{
    $catid = $id;
}

if($option != 'com_content' || (!in_array($catid, $allowedCats) && $allowedCats[0] != -1))
{
    return;
}

JHtml::_('behavior.framework');

JFactory::getApplication()->getUserStateFromRequest('minicck.filter', 'minicckfilter', array(), 'array');

$fields = ModMinicckFilterHelper::getFields($params);

require JModuleHelper::getLayoutPath('mod_minicck_filter', $params->get('layout', 'default'));
