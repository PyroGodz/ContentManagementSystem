<?php
/**
 * AddToAny Module Entry Point
 * 
 * @package    addtoany
 * @subpackage Modules
 * @copyright (C) AddToAny
 * @license GNU/GPLv3
 */
 
// No direct access
defined('_JEXEC') or die;
// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';
 
$addtoany = modAddToAnyHelper::getAddToAny($params);

$kit_size = $addtoany['kit_size'];
$services_html = $addtoany['services_html'];
$follow_classname = $addtoany['follow_classname'];
$icon_color_attr = $addtoany['icon_color_attr'];
$url_attr = $addtoany['url_attr'];
$title_attr = $addtoany['title_attr'];

require JModuleHelper::getLayoutPath('mod_addtoany');