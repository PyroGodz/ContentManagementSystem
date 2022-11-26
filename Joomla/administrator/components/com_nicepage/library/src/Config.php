<?php
/**
 * @package   Nicepage Website Builder
 * @author    Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

namespace NP;

defined('_JEXEC') or die;

use \NicepageHelpersNicepage;

/**
 * Class Config
 */
class Config
{
    private static $_instance;

    private $_config;
    private $_settings;

    /**
     * Config constructor.
     */
    public function __construct() {
        $this->_config = NicepageHelpersNicepage::getConfig();
        $this->_settings = isset($this->_config['siteSettings']) ? json_decode($this->_config['siteSettings'], true) : array();
    }

    /**
     * Apply site settings to content\
     *
     * @param string $pageContent Document content
     *
     * @return mixed
     */
    public function applySiteSettings($pageContent) {
        if ($this->_settings && is_array($this->_settings) && count($this->_settings) < 1) {
            return $pageContent;
        }
        if (isset($this->_settings['captchaScript']) && $this->_settings['captchaScript'] && strpos($pageContent, 'recaptchaResponse') !== false) {
            $pageContent = str_replace('</head>', $this->_settings['captchaScript'] . '</head>', $pageContent);
        }
        if (isset($this->_settings['metaTags']) && $this->_settings['metaTags'] && strpos($pageContent, $this->_settings['metaTags']) === false) {
            $pageContent = str_replace('</head>', $this->_settings['metaTags'] . '</head>', $pageContent);
        }
        if (isset($this->_settings['headHtml']) && $this->_settings['headHtml'] && strpos($pageContent, $this->_settings['headHtml']) === false) {
            $pageContent = str_replace('</head>', $this->_settings['headHtml'] . '</head>', $pageContent);
        }
        if (isset($this->_settings['analyticsCode']) && $this->_settings['analyticsCode'] && strpos($pageContent, $this->_settings['analyticsCode']) === false) {
            $pageContent = str_replace('</head>', $this->_settings['analyticsCode'] . '</head>', $pageContent);
        }
        if (isset($this->_settings['keywords']) && $this->_settings['keywords'] && strpos($pageContent, $this->_settings['keywords']) === false) {
            if (preg_match('/<meta\s+?name="keywords"\s+?content="([^"]+?)"\s+?\/>/', $pageContent, $keywordsMatches)) {
                $pageContent = str_replace($keywordsMatches[0], '<meta name="keywords" content="' . $this->_settings['keywords'] . ', ' . $keywordsMatches[1] . '" />', $pageContent);
            } else {
                $pageContent = str_replace('<title>', '<meta name="keywords" content="' . $this->_settings['keywords'] . '" />' . '<title>', $pageContent);
            }
        }
        if (isset($this->_settings['description']) && $this->_settings['description'] && strpos($pageContent, $this->_settings['description']) === false) {
            if (preg_match('/<meta\s+?name="description"\s+?content="([^"]+?)"\s+?\/>/', $pageContent, $descMatches)) {
                $pageContent = str_replace($descMatches[0], '<meta name="description" content="' . $this->_settings['description'] . ', ' . $descMatches[1] . '" />', $pageContent);
            } else {
                $pageContent = str_replace('<title>', '<meta name="description" content="' . $this->_settings['description'] . '" />' . '<title>', $pageContent);
            }
        }
        $googleTagManager = isset($this->_settings['googleTagManager']) && $this->_settings['googleTagManager'] ? $this->_settings['googleTagManager'] : '';
        if ($googleTagManager && strpos($pageContent, $googleTagManager) === false
            && isset($this->_config['googleTagManagerCode']) && $this->_config['googleTagManagerCode']
        ) {
            $pageContent = str_replace('</title>', '</title>' . $this->_config['googleTagManagerCode'], $pageContent);
            $pageContent = preg_replace('/(<body[^>]*?>)/', '$1' . $this->_config['googleTagManagerCodeNoScript'], $pageContent);
        }
        return $pageContent;
    }

    /**
     * Get config instance
     *
     * @return Config
     */
    public static function getInstance()
    {
        if (!is_object(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}