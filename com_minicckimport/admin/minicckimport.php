<?php
defined( '_JEXEC' ) or die; // No direct access
/**
 * Component minicckimport
 * @author Arkadiy
 */
$controller = JControllerLegacy::getInstance( 'minicckimport' );
$controller->execute( JFactory::getApplication()->input->get( 'task' ) );
$controller->redirect();