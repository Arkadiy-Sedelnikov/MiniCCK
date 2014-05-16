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

$app = JFactory::getApplication();
$input = $app->input;

$option = $input->getString('option', '');
$view = $input->getString('view', '');
$catid = $input->getInt('catid', 0);
$id = $input->getInt('id', 0);
$minicckfilter = $input->get('minicckfilter', array(), 'array');

$allowedCats = $params->get('categories', array());

if($view == 'category')
{
    $catid = $id;
}

if($option != 'com_content' || (!in_array($catid, $allowedCats) && $allowedCats[0] != -1) || $catid == 0)
{
    return;
}

JHtml::_('behavior.framework');

$url = JUri::getInstance();
$action = $url->toString();

if(count($minicckfilter))
{
    $app->setUserState('cat_'.$catid.'.minicckfilter', $minicckfilter);
}

$fields = ModMinicckFilterHelper::getFields($params, $catid);

require JModuleHelper::getLayoutPath('mod_minicck_filter', $params->get('layout', 'default'));
