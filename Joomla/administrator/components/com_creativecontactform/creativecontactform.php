<?php
/**
 * Joomla! component Creative Contact Form
 *
 * @version $Id: 2012-04-05 14:30:25 svn $
 * @author creative-solutions.net
 * @package Creative Contact Form
 * @subpackage com_creativecontactform
 * @license GNU/GPL
 *
 */

// no direct access
defined('_JEXEC') or die('Restircted access');

/*
 * Define constants for all pages
 */
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}
define('JV', 'j3');
define('J4', (version_compare(JVERSION, '4', '>=')) ? true : false);

// Require the base controller
require_once JPATH_COMPONENT.DS.'helpers'.DS.'helper.php';

// Initialize the controller
$controller	= JControllerLegacy::getInstance('creativecontactform');

$document = JFactory::getDocument();
$cssFile = JURI::base(true).'/components/com_creativecontactform/assets/css/icons_'.JV.'.css';
$document->addStyleSheet($cssFile, 'text/css', null, array());

// Perform the Request task
if(JV == 'j2')
	$controller->execute( JRequest::getCmd('task'));
else
	$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();