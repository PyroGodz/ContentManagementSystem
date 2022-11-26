<?php
/**
 * @package   Nicepage Website Builder
 * @author    Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

if (isset($controlProps) && isset($controlTemplate)) {
    $uri = clone JUri::getInstance();
    $uri->delVar('lang');
    $uri->setVar('lang', $controlProps['lang']);
    $controlTemplate = str_replace('[[href]]', $uri->toString(), $controlTemplate);
    $controlTemplate = str_replace('[[lang]]', $controlProps['lang'], $controlTemplate);
    $controlTemplate = str_replace('[[langText]]', $controlProps['langText'], $controlTemplate);
    echo $controlTemplate;
}
