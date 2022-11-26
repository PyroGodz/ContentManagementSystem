<?php
/**
 * @package   Nicepage Website Builder
 * @author    Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

namespace NP;

defined('_JEXEC') or die;

use NP\Utility\ColorHelper;
use \NicepageHelpersNicepage;
use \JFactory, \JURI, \JPluginHelper, \JEventDispatcher, \JHtml, \JRegistry, \JApplicationHelper;

/**
 * Class Page
 */
class Page
{
    private static $_instance;

    private $_originalName = 'nicepage';
    private $_isNicepageTheme = '0';
    private $_pageTable = null;

    private $_pageView = 'landing';
    private $_config = null;
    private $_siteSettings = null;
    private $_props = null;

    private $_scripts = '';
    private $_styles = '';
    private $_backlink = '';
    private $_sectionsHtml = '';
    private $_cookiesConsent = '';
    private $_cookiesConfirmCode = '';
    private $_backToTop = '';
    private $_canonicalUrl = '';

    private $_context;
    private $_row;
    private $_params;

    private $_header = '';
    private $_footer = '';

    private $_buildedPageElements = false;
    private $_pagePasswordProtected = false;

    private $_publishDialogs = array();

    /**
     * Page constructor.
     *
     * @param null   $pageTable Page table
     * @param string $context   Component context
     * @param null   $row       Component row
     * @param null   $params    Component params
     */
    public function __construct($pageTable, $context, &$row, &$params) {
        $this->_pageTable = $pageTable;
        $this->_context = $context;
        $this->_row = $row;
        $this->_params = $params;

        $props = $this->_pageTable->getProps();
        $this->_config = NicepageHelpersNicepage::getConfig($props['isPreview']);
        if (isset($this->_config['siteSettings'])) {
            $this->_siteSettings = json_decode($this->_config['siteSettings'], true);
        }
        $this->_props = $this->prepareProps($props);

        if (isset($props['pageView'])) {
            $this->_pageView = $props['pageView'];
        }

        $originalName = $this->_originalName;
        if ($this->_row) {
            $this->_row->{$originalName} = true;
        }

        $this->_isNicepageTheme = JFactory::getApplication()->getTemplate(true)->params->get($originalName . 'theme', '0');
    }

    /**
     * Get page id
     *
     * @return mixed
     */
    public function getPageId() {
        return $this->_props['pageId'];
    }

    /**
     * Check page is protected
     *
     * @return bool
     */
    public function pagePasswordProtected() {
        if (isset($this->_props['passwordProtection']) && $this->_props['passwordProtection']) {
            $originalPassword = $this->_props['passwordProtection'];
            $uri = JUri::getInstance()->toString();
            $cookieHash = JApplicationHelper::getHash($uri);
            $cookieName = 'joomla-postpass_' . $cookieHash;
            $app = JFactory::getApplication();

            $userPassword = $app->input->get('password', '');
            $userPasswordHash = $app->input->get('password_hash', '');
            if ($userPassword && ($userPassword === $originalPassword || $userPasswordHash === $originalPassword)) {
                // Create ten days cookies.
                $cookieLifeTime = time() + 10 * 24 * 60 * 60;
                $cookieDomain   = $app->get('cookie_domain', '');
                $cookiePath     = $app->get('cookie_path', '/');
                $isHttpsForced  = $app->isHttpsForced();

                $app->input->cookie->set(
                    $cookieName,
                    $userPasswordHash === $originalPassword ? $userPasswordHash : md5($userPassword),
                    $cookieLifeTime,
                    $cookiePath,
                    $cookieDomain,
                    $isHttpsForced,
                    true
                );
            }

            $cookie = $app->input->cookie;
            $cookiePass = $cookie ? $cookie->get($cookieName, '') : '';
            if (!$cookiePass || ($cookiePass !== $originalPassword && $cookiePass !== md5($originalPassword))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build page elements
     */
    public function buildPageElements() {
        if ($this->_buildedPageElements) {
            return;
        }

        if ($this->pagePasswordProtected()) {
            $this->_pagePasswordProtected = true;
        }

        $this->setPageProperties();
        $this->setScripts();
        $this->setStyles();
        $this->setBacklink();
        $this->setSectionsHtml();
        $this->setCookiesConsent();
        $this->setBackToTop();
        $this->setCanonicalUrl();

        if ($this->_pageView == 'landing') {
            $this->setHeader();
            $this->setFooter();
        }

        $this->_buildedPageElements = true;
    }

    /**
     * Build page header
     */
    public function setHeader() {
        $content = $this->fixImagePaths($this->_config['header']);
        $hideHeader = $this->_props['hideHeader'];
        if ($content && !$hideHeader) {
            $headerItem = json_decode($content, true);
            if ($headerItem) {
                $translations = json_decode($this->fixImagePaths($this->_config['headerTranslations']), true);
                $lang = explode('-', JFactory::getApplication()->input->get('lang', ''))[0];
                if ($lang && $translations && isset($translations[$lang])) {
                    $headerItem['php'] = $translations[$lang]['php'];
                }
                ob_start();
                echo $headerItem['styles'];
                echo $headerItem['php'];
                $publishHeader = ob_get_clean();
                $this->setPublishDialogs($publishHeader, 'header');
                $this->_header = NicepageHelpersNicepage::processSectionsHtml($publishHeader, true, 'header', $this->_siteSettings);
            }
        }
    }

    /**
     * Build page footer
     */
    public function setFooter() {
        $content = $this->fixImagePaths($this->_config['footer']);
        $hideFooter = $this->_props['hideFooter'];
        if ($content && !$hideFooter) {
            $footerItem = json_decode($content, true);
            if ($footerItem) {
                $translations = json_decode($this->fixImagePaths($this->_config['footerTranslations']), true);
                $lang = explode('-', JFactory::getApplication()->input->get('lang', ''))[0];
                if ($lang && $translations && isset($translations[$lang])) {
                    $footerItem['php'] = $translations[$lang]['php'];
                }
                ob_start();
                echo $footerItem['styles'];
                echo $footerItem['php'];
                $publishFooter = ob_get_clean();
                $this->setPublishDialogs($publishFooter, 'footer');
                $this->_footer = NicepageHelpersNicepage::processSectionsHtml($publishFooter, true, 'footer');
            }
        }
    }

    /**
     * Get page header
     *
     * @return string
     */
    public function getHeader() {
        return $this->_header;
    }

    /**
     * Get page footer
     *
     * @return string
     */
    public function getFooter() {
        return $this->_footer;
    }

    /**
     * Set publish dialogs
     *
     * @param string $html Content
     * @param string $type Type
     */
    public function setPublishDialogs($html, $type = '') {
        $dialogs = array();
        if ($type == 'header' || $type == 'footer') {
            if (isset($this->_config[$type]) && $this->_config[$type]) {
                $item = json_decode($this->_config[$type], true);
                $dialogs = isset($item['dialogs']) ? json_decode($item['dialogs'], true) : array();
            }
        } else {
            $dialogs = json_decode($this->_props['dialogs'], true);
        }

        foreach ($dialogs as $dialog) {
            $this->_publishDialogs[$dialog['sectionAnchorId']] = $this->fixImagePaths($dialog['publishHtml']) . '<style>' . $dialog['publishCss'] . '</style>';
        }
        // All dialogs
        if (isset($this->_config['publishDialogs']) && $this->_config['publishDialogs']) {
            $publishDialogs = json_decode($this->_config['publishDialogs'], true);
            foreach ($publishDialogs as $dialog) {
                $anchorId = $dialog['sectionAnchorId'];
                $showOnList = isset($dialog['showOnList']) ? $dialog['showOnList'] : array();
                $shownOn = isset($dialog['showOn']) ? $dialog['showOn'] : '';
                $foundDialogUsage = false;

                if (($shownOn == 'timer' || $shownOn == 'page_exit') && in_array($this->getPageId(), $showOnList)) {
                    $foundDialogUsage = true;
                }

                if (strpos($html, $anchorId) !== false) {
                    $foundDialogUsage = true;
                }

                if ($foundDialogUsage && !array_key_exists($anchorId, $this->_publishDialogs)) {
                    $this->_publishDialogs[$anchorId] = $this->fixImagePaths($dialog['publishHtml']) . '<style>' . $dialog['publishCss'] . '</style>';
                }
            }
        }
    }

    /**
     * Apply dialogs to content
     *
     * @param string $html Content
     *
     * @return mixed|string|string[]|null
     */
    public function applyPublishDialogs($html) {
        $publishDialogsHtml = '';
        foreach ($this->_publishDialogs as $anchor => $dialog) {
            $publishDialogsHtml .= $dialog;
        }
        if ($publishDialogsHtml && $this->getPageView() !== 'landing' && $this->_isNicepageTheme != '1') {
            $publishDialogsHtml =  '<div class="nicepage-container"><div class="'. $this->_props['bodyClass'] .'">' . $publishDialogsHtml . '</div></div>';
        }
        $publishDialogsHtml = JHtml::_('content.prepare', $publishDialogsHtml, new JRegistry, 'com_content.article');
        $publishDialogsHtml = NicepageHelpersNicepage::processSectionsHtml($publishDialogsHtml, true, $this->_props['pageId']);
        $publishDialogsHtml = $this->fixImagePaths($publishDialogsHtml);
        $html = str_replace('</body>', $publishDialogsHtml . '</body>', $html);
        return $html;
    }

    /**
     * Build page
     */
    public function prepare() {
        $isBlog = $this->_context === 'com_content.featured' || $this->_context === 'com_content.category';
        if ($isBlog) {
            $introImgStruct = isset($this->_props['introImgStruct']) ? $this->_props['introImgStruct'] : '';
            if ($introImgStruct) {
                $this->_row->pageIntroImgStruct = json_decode($this->fixImagePaths($introImgStruct), true);
            }
        } else {
            $this->buildPageElements();
            $type = $this->_pageView === 'landing' ? 'landing' : 'content';
            $content = "<!--np_" . $type ."-->" . $this->getSectionsHtml() . $this->getEditLinkHtml() . "<!--/np_" . $type . "-->";
            $content .= "<!--np_page_id-->" . $this->_row->id . "<!--/np_page_id-->";
            $this->_row->introtext = $this->_row->text = $content;
        }
    }

    /**
     * Get page content
     *
     * @param string $pageContent Page content
     *
     * @return mixed|string|string[]|null
     */
    public function get($pageContent = '') {
        $this->buildPageElements();

        if ($this->_pageView === 'thumbnail') {
            return $this->buildThumbnail();
        } else if ($this->_pageView === 'landing') {
            $pageContent = $this->buildNpHeaderFooter($pageContent);
            $pageContent = str_replace('</head>', $this->appendOpenGraphTags() . '</head>', $pageContent);
        } else if ($this->_pageView === 'landing_with_header_footer') {
            $pageContent = $this->buildThemeHeaderFooter($pageContent);
        } else {
            $pageContent = preg_replace('/<!--\/?np\_content-->/', '', $pageContent);
        }
        if (strpos($pageContent, '<meta name="viewport"') === false) {
            $pageContent = str_replace('<head>', '<head><meta name="viewport" content="width=device-width, initial-scale=1.0">', $pageContent);
        }
        $pageContent = str_replace('</head>', $this->getStyles() . $this->getScripts() . $this->getCookiesConfirmCode() . '</head>', $pageContent);
        $pageContent = str_replace('</body>', $this->getBacklink() . $this->getCookiesConsent() . $this->getBackToTop() . '</body>', $pageContent);
        $pageCanonical = $this->getCanonicalUrl();
        if ($pageCanonical) {
            if (preg_match('/<link\s+?rel="canonical"\s+?href="[^"]+?"\s*>/', $pageContent, $canonicalMatches)) {
                $pageContent = str_replace($canonicalMatches[0], $pageCanonical, $pageContent);
            } else {
                $pageContent = str_replace('<head>', '<head>' . $pageCanonical, $pageContent);
            }
        }
        $pageContent = $this->applyPublishDialogs($pageContent);
        return $pageContent;
    }

    /**
     * Generate og tags
     *
     * @return string|void
     */
    public function appendOpenGraphTags() {
        $settings = isset($this->_config['siteSettings']) ? json_decode($this->_config['siteSettings'], true) : array();
        $siteDisableOpenGraph = isset($settings['disableOpenGraph']) && $settings['disableOpenGraph'] == 'true' ? true : false;
        if ($siteDisableOpenGraph) {
            return;
        }
        $config = JFactory::getConfig();
        $document = JFactory::getDocument();
        $pageTitle = $document->getTitle();
        if ($this->_props['ogTags'] && $this->_props['ogTags']['title']) {
            $pageTitle = $this->_props['ogTags']['title'];
        }
        $pageDesc = $document->getDescription() ? $document->getDescription() : $config->get('sitename');
        if ($this->_props['ogTags'] && $this->_props['ogTags']['description']) {
            $pageDesc = $this->_props['ogTags']['description'];
        }
        $pageUrl = JUri::getInstance()->toString();
        if ($this->_props['ogTags'] && $this->_props['ogTags']['url']) {
            $pageUrl = $this->_props['ogTags']['url'];
        }
        $pageImage = '';
        if ($this->_props['ogTags'] && $this->_props['ogTags']['image']) {
            $pageImage = $this->_props['ogTags']['image'];
        }
        $seoTags = array();
        $seoTags['og:site_name'] = array('tag' => 'meta', 'property' => 'og:site_name', 'content' => $config->get('sitename'));
        $seoTags['og:type'] = array('tag' => 'meta', 'property' => 'og:type', 'content' => 'website');
        $seoTags['og:title'] = array('tag' => 'meta', 'property' => 'og:title', 'content' => $pageTitle);
        $seoTags['og:description'] = array('tag' => 'meta', 'property' => 'og:description', 'content' =>  $pageDesc);
        if ($pageImage) {
            $seoTags['og:image'] = array('tag' => 'meta', 'property' => 'og:image', 'content' => $pageImage);
        }
        $seoTags['og:url'] = array('tag' => 'meta', 'property' => 'og:url', 'content' => $pageUrl);
        $result = '';
        foreach ($seoTags as $values) {
            $tag = '<';
            foreach ($values as $property => $value) {
                if ($property == 'tag') {
                    $tag .= $value;
                    continue;
                }
                $tag .= ' ' . $property . '="' . $value . '"';
            }
            $tag .= '>';
            $result .= $tag;
        }
        if (isset($settings['colorScheme']) && isset($settings['colorScheme']['colors']) && count($settings['colorScheme']['colors']) > 0) {
            $result .= '<meta name="theme-color" content="' . $settings['colorScheme']['colors'][0] . '">';
        }
        return $result;
    }

    /**
     * Build thumbnail page
     *
     * @return mixed
     */
    public function buildThumbnail()
    {
        $ret = <<<EOF
<!DOCTYPE html>
<html>        
    <head>
    <style>
        body {
            cursor: pointer;
        }
    </style>
    {$this->getStyles()}
    </head>
    <body class="{$this->getBodyClass()}" style="{$this->getBodyStyle()}" {$this->getBodyDataBg(true)}>
        {$this->getSectionsHtml()}
    </body>
</html>
EOF;
        return $ret;
    }

    /**
     * Build page with np header&footer option
     *
     * @param string $pageContent Page content
     *
     * @return mixed
     */
    public function buildNpHeaderFooter($pageContent)
    {
        $systemMsgPlaceholderRe = '/<\!--np\_message-->([\s\S]+?)<\!--\/np\_message-->/';
        $systemMsg = '';
        if (preg_match($systemMsgPlaceholderRe, $pageContent, $systemMsgPlaceHolderMatches)) {
            $systemMsg = $systemMsgPlaceHolderMatches[1];
        }

        $placeholderRe = '/<\!--np\_landing-->([\s\S]+?)<\!--\/np\_landing-->/';
        if (!preg_match($placeholderRe, $pageContent, $placeHolderMatches)) {
            return $pageContent;
        }
        $sectionsHtml = $systemMsg . $placeHolderMatches[1];

        $bodyRe = '/(<body[^>]+>)([\s\S]*)(<\/body>)/';
        if (!preg_match($bodyRe, $pageContent, $bodyMatches)) {
            return $pageContent;
        }

        list($bodyStartTag, $bodyContent, $bodyEndTag) = array($bodyMatches[1], $bodyMatches[2], $bodyMatches[3]);

        $bodyStartTagUpdated = str_replace('{bodyClass}', $this->getBodyClass(), $bodyStartTag);
        $bodyStartTagUpdated = str_replace('{bodyStyle}', $this->getBodyStyle(), $bodyStartTagUpdated);
        $bodyStartTagUpdated = str_replace('{bodyDataBg}', $this->getBodyDataBg(true), $bodyStartTagUpdated);

        return str_replace(
            array(
                $bodyStartTag,
                $bodyContent,
                $bodyEndTag,
            ),
            array(
                $bodyStartTagUpdated . $this->getHeader(),
                $sectionsHtml,
                $this->getFooter() . $bodyEndTag,
            ),
            $pageContent
        );
    }

    /**
     * Build page with theme header&footer option
     *
     * @param string $pageContent Page content
     *
     * @return mixed
     */
    public function buildThemeHeaderFooter($pageContent)
    {
        $placeholderRe = '/<\!--np\_content-->([\s\S]+?)<\!--\/np\_content-->/';
        if (!preg_match($placeholderRe, $pageContent, $placeHolderMatches)) {
            return $pageContent;
        }
        $sectionsHtml = $placeHolderMatches[1];

        $bodyRe = '/(<body[^>]+>)([\s\S]*)(<\/body>)/';
        if (!preg_match($bodyRe, $pageContent, $bodyMatches)) {
            return $pageContent;
        }

        list($bodyStartTag, $bodyContent, $bodyEndTag) = array($bodyMatches[1], trim($bodyMatches[2]), $bodyMatches[3]);

        if ($bodyContent == '') {
            $newPageContent = $bodyStartTag . $sectionsHtml . $bodyEndTag;
        } else {
            $newPageContent = $bodyStartTag;
            if (preg_match('/<header[^>]+>[\s\S]*<\/header>/', $bodyContent, $headerMatches)) {
                $newPageContent .= $headerMatches[0];
            }
            $newPageContent .= $sectionsHtml;
            if (preg_match('/<footer[^>]+>[\s\S]*<\/footer>/', $bodyContent, $footerMatches)) {
                $newPageContent .= $footerMatches[0];
            }
            if (preg_match('/<\/footer>([\s\S]*)/', $bodyContent, $afterFooterContentMatches)) {
                $newPageContent .= $afterFooterContentMatches[1];
            }
            $newPageContent .= $bodyEndTag;
        }
        $pageContent = preg_replace('/(<body[^>]+>)([\s\S]*)(<\/body>)/', '[[body]]', $pageContent);
        $pageContent = str_replace('[[body]]', $newPageContent, $pageContent);
        return $pageContent;
    }

    /**
     * Add custom page properties
     */
    public function setPageProperties()
    {
        $document = JFactory::getDocument();
        if ($this->_props['metaTags']) {
            $document->addCustomTag($this->_props['metaTags']);
        }
        if ($this->_props['customHeadHtml']) {
            $document->addCustomTag($this->_props['customHeadHtml']);
        }
        if ($this->_props['metaGeneratorContent'] && $this->_pageView === 'landing') {
            $document->setMetaData('generator', $this->_props['metaGeneratorContent']);
        }
    }

    /**
     * Set plugin scripts
     */
    public function setScripts()
    {
        if ($this->_isNicepageTheme !== '1' || $this->_pageView == 'landing') {
            $assets = JURI::root(true) . '/components/com_nicepage/assets';
            if (isset($this->_config['jquery']) && $this->_config['jquery'] == '1') {
                $this->_scripts .= '<script src="' . $assets . '/js/jquery.js"></script>';
            }
            $this->_scripts .= '<script src="' . $assets . '/js/nicepage.js"></script>';
        }
    }

    /**
     * Get plugin scripts
     *
     * @return string
     */
    public function getScripts()
    {
        return $this->_scripts;
    }

    /**
     * Set plugin styles
     */
    public function setStyles()
    {
        $assets = JURI::root(true) . '/components/com_nicepage/assets';

        $siteStyleCss = ColorHelper::buildSiteStyleCss(
            $this->_config,
            $this->_props['pageCssUsedIds'],
            $this->_pagePasswordProtected
        );

        if (!$this->_pagePasswordProtected) {
            $sectionsHead = $this->_props['head'];
        }

        if ($this->_pageView == 'landing' || $this->_pageView == 'thumbnail') {
            $this->_styles = '<link rel="stylesheet" type="text/css" media="all" href="' . $assets . '/css/nicepage.css" rel="stylesheet" id="nicepage-style-css">';
            $this->_styles .= '<link rel="stylesheet" type="text/css" media="all" href="' . $assets . '/css/media.css" rel="stylesheet" id="theme-media-css">';
            $this->_styles .= $this->_props['fonts'];
            $this->_styles .= '<style>' . $siteStyleCss . $sectionsHead . '</style>';
        } else {

            $autoResponsive = isset($this->_config['autoResponsive']) ? !!$this->_config['autoResponsive'] : true;

            if ($autoResponsive && $this->_isNicepageTheme == '0') {
                $sectionsHead = preg_replace('#\/\*RESPONSIVE_MEDIA\*\/([\s\S]*?)\/\*\/RESPONSIVE_MEDIA\*\/#', '', $sectionsHead);
                $this->_styles .= '<link href="' . $assets . '/css/responsive.css" rel="stylesheet">';
            } else {
                $sectionsHead = preg_replace('#\/\*RESPONSIVE_CLASS\*\/([\s\S]*?)\/\*\/RESPONSIVE_CLASS\*\/#', '', $sectionsHead);
                if ($this->_isNicepageTheme == '0') {
                    $this->_styles .= '<link href="' . $assets . '/css/media.css" rel="stylesheet">';
                }
            }
            $dynamicCss = $siteStyleCss . $sectionsHead;
            if ($this->_isNicepageTheme !== '1') {
                $this->_styles .= '<link href="' . $assets . '/css/page-styles.css" rel="stylesheet">';
                $dynamicCss = $this->wrapStyles($dynamicCss);
            }
            $this->_styles .= $this->_props['fonts'];
            $this->_styles .= '<style id="nicepage-style-css">' . $dynamicCss . '</style>';
        }
        $customFontsFilePath = JPATH_BASE . '/images/nicepage-fonts/fonts_' . $this->_props['pageId'] . '.css';
        if (file_exists($customFontsFilePath)) {
            $customFontsFileHref = JURI::root(true) . '/images/nicepage-fonts/fonts_' .  $this->_props['pageId'] . '.css';
            $this->_styles .= '<link href="' . $customFontsFileHref . '" rel="stylesheet">';
        }
    }

    /**
     * Wrap styles by container
     *
     * @param string $dynamicCss Additional styles
     *
     * @return null|string|string[]
     */
    public function wrapStyles($dynamicCss)
    {
        return preg_replace_callback(
            '/([^{}]+)\{[^{}]+?\}/',
            function ($match) {
                $selectors = $match[1];
                $parts = explode(',', $selectors);
                $newSelectors = implode(
                    ',',
                    array_map(
                        function ($part) {
                            if (!preg_match('/html|body|sheet|keyframes/', $part)) {
                                return ' .nicepage-container ' . $part;
                            } else {
                                return $part;
                            }
                        },
                        $parts
                    )
                );
                return str_replace($selectors, $newSelectors, $match[0]);
            },
            $dynamicCss
        );
    }

    /**
     * Get plugin styles
     *
     * @return string
     */
    public function getStyles()
    {
        return $this->_styles;
    }

    /**
     * Set page backlink
     */
    public function setBacklink()
    {
        $backlink = $this->_props['backlink'];
        if ($backlink && ($this->_pageView == 'default' || $this->_pageView === 'landing_with_header_footer')) {
            if ($this->_isNicepageTheme !== '1') {
                $backlink = '<div class="nicepage-container"><div class="'. $this->_props['bodyClass'] .'">' . $backlink . '</div></div>';
            } else {
                $backlink = '';
            }
        }
        $this->_backlink = $backlink;
    }

    /**
     * Get page backlink
     *
     * @return string
     */
    public function getBacklink()
    {
        return $this->_backlink;
    }

    /**
     * Set sections html
     */
    public function setSectionsHtml()
    {
        $isPublic = $this->_pageView == 'thumbnail' ? false : true;
        $this->_sectionsHtml = NicepageHelpersNicepage::processSectionsHtml($this->_props['publishHtml'], $isPublic, $this->_props['pageId']);

        if ($this->_pageView == 'thumbnail') {
            preg_match_all('/<section[\s\S]+?<\/section>/', $this->_sectionsHtml, $matches, PREG_SET_ORDER);
            $count = count($matches);
            if ($count > 4) {
                for ($i = 4; $i < $count; $i++) {
                    $this->_sectionsHtml = str_replace($matches[$i], '', $this->_sectionsHtml);
                }
            }
            return;
        }

        $this->setPublishDialogs($this->_sectionsHtml);

        if ($this->_pageView !== 'landing') {
            $autoResponsive = isset($this->_config['autoResponsive']) ? !!$this->_config['autoResponsive'] : true;
            if ($autoResponsive && $this->_isNicepageTheme == '0') {
                $responsiveScript = <<<SCRIPT
<script>
    (function ($) {
        var ResponsiveCms = window.ResponsiveCms;
        if (!ResponsiveCms) {
            return;
        }
        ResponsiveCms.contentDom = $('script:last').parent();
        
        if (typeof ResponsiveCms.recalcClasses === 'function') {
            ResponsiveCms.recalcClasses();
        }
    })(jQuery);
</script>
SCRIPT;
                $this->_sectionsHtml = $responsiveScript . $this->_sectionsHtml;
            }

            if ($this->_isNicepageTheme === '0') {
                $this->_sectionsHtml = '<div class="nicepage-container"><div ' . $this->getBodyDataBg(true) . 'style="' . $this->_props['bodyStyle'] . '" class="' . $this->_props['bodyClass'] . '">' . $this->_sectionsHtml . '</div></div>';
            } else {
                $bodyScript = <<<SCRIPT
<script>
var body = document.body;
    
    body.className += " {$this->_props['bodyClass']}";
    body.style.cssText += " {$this->_props['bodyStyle']}";
    var dataBg = '{$this->getBodyDataBg()}';
    if (dataBg) {
        body.setAttribute('data-bg', dataBg);
    }
</script>
SCRIPT;
                $this->_sectionsHtml = $bodyScript . $this->_sectionsHtml;
            }
        }

        if ($this->_pagePasswordProtected) {
            $this->_sectionsHtml = $this->buildPasswordProtectionTemplate();
        }
    }

    /**
     * Build password protection template
     *
     * @return array|string|string[]
     */
    public function buildPasswordProtectionTemplate() {
        $publishPassword = '';
        $content = $this->fixImagePaths($this->_config['password']);
        if ($content) {
            $passwordItem = json_decode($content, true);
            if ($passwordItem) {
                ob_start();
                echo $passwordItem['styles'];
                echo $passwordItem['php'];
                $publishPassword = ob_get_clean();
                $publishPassword = NicepageHelpersNicepage::processSectionsHtml($publishPassword, true, 'password');
            }
        }
        if (!$publishPassword) {
            $publishPassword = $this->defaultPasswordProtectionTemplate();
        }
        $uri = JUri::getInstance()->toString();
        $publishPassword = str_replace('[[action]]', $uri, $publishPassword);
        $publishPassword = str_replace('[[method]]', 'post', $publishPassword);
        $passwordHash = JFactory::getApplication()->input->get('password_hash', '');
        if ($passwordHash) {
            $publishPassword .=<<<SCRIPT
<script>
jQuery(function($) {    
    var form = $('.u-password-control form');
    var errorContainer = form.find('.u-form-send-error');
    errorContainer.show();
    setTimeout(function () {
        errorContainer.hide();
    }, 2000);
});
</script>
SCRIPT;
        }
        return $publishPassword;
    }

    /**
     * Get default password protection template
     */
    public function defaultPasswordProtectionTemplate() {
        return <<<FORM
<section>
    <div class="u-clearfix u-sheet">
        <div class="u-form u-password-control" style="text-align: center;">
            <form action="[[action]]" method="[[method]]">
                <p>PROTECTED CONTENT</p>
                <p>
                    <label for="pwbox"> 
                        <input placeholder="Enter your password" name="password" id="pwbox" type="password" size="20">
                        <input name="password_hash" id="hashbox" type="hidden" size="20">
                    </label>                    
                    <div class="u-form-submit">
                        <a href="#" class="u-btn u-btn-submit u-button-style">Submit</a>
                        <input type="submit" name="Submit" value="Enter" class="u-form-control-hidden">
                        <div class="u-form-send-error">Password is incorrect</div>
                    </div>
                </p>
            </form>
        </div>
    </div>
</section>
FORM;
    }

    /**
     * Get sections html
     *
     * @return string
     */
    public function getSectionsHtml()
    {
        return $this->_sectionsHtml;
    }

    /**
     * Set page cookies consent
     */
    public function setCookiesConsent()
    {
        if ($this->_isNicepageTheme === '1' && $this->_pageView !== 'landing') {
            return;
        }

        if (isset($this->_config['cookiesConsent'])) {
            $cookiesConsent = json_decode($this->_config['cookiesConsent'], true);
            if ($cookiesConsent && (!$cookiesConsent['hideCookies'] || $cookiesConsent['hideCookies'] === 'false')) {
                $content = $this->fixImagePaths($cookiesConsent['publishCookiesSection']);
                if ($this->_pageView == 'landing') {
                    $this->_cookiesConsent = $content;
                } else {
                    $this->_cookiesConsent = '<div class="nicepage-container"><div class="' . $this->_props['bodyClass'] . '">' . $content . '</div></div>';
                }
                $this->_cookiesConfirmCode = $cookiesConsent['cookieConfirmCode'];
            }
        }
    }

    /**
     * Get page cookies consent
     *
     * @return string
     */
    public function getCookiesConsent()
    {
        return $this->_cookiesConsent;
    }

    /**
     * Get page cookies confirm code
     *
     * @return string
     */
    public function getCookiesConfirmCode()
    {
        return $this->_cookiesConfirmCode;
    }

    /**
     * Set backtotop in content
     */
    public function setBackToTop() {
        $hideBackToTop = $this->_props['hideBackToTop'];
        if (isset($this->_config['backToTop']) && !$hideBackToTop) {
            if ($this->_pageView == 'landing') {
                $this->_backToTop = $this->_config['backToTop'];
            } else {
                $this->_backToTop = '<div class="nicepage-container"><div class="' . $this->_props['bodyClass'] . '">' . $this->_config['backToTop'] . '</div></div>';
            }
        }
    }

    /**
     * Get page backlink
     *
     * @return string
     */
    public function getBackToTop()
    {
        return $this->_backToTop;
    }

    /**
     * Set canonical url
     */
    public function setCanonicalUrl() {
        $this->_canonicalUrl = $this->_props['canonical'];
    }

    /**
     * @return string
     */
    public function getCanonicalUrl() {
        $canonical = $this->_canonicalUrl;
        if (!$canonical && $this->_pageView == 'landing') {
            $canonical = JURI::getInstance()->toString();
        }
        return $canonical ? '<link rel="canonical" href="' . $canonical . '">' : '';
    }

    /**
     * Get page view
     *
     * @return mixed|string
     */
    public function getPageView() {
        return $this->_pageView;
    }

    /**
     * Set page view
     *
     * @param string $view Page view
     */
    public function setPageView($view) {
        $this->_pageView = $view;
    }

    /**
     * Get body style
     *
     * @return mixed
     */
    public function getBodyStyle() {
        return $this->_props['bodyStyle'];
    }

    /**
     * Get body class
     *
     * @return mixed
     */
    public function getBodyClass() {
        return $this->_props['bodyClass'];
    }

    /**
     * Get body data bg attr value
     *
     * @param bool $withAttr Build with attr
     *
     * @return mixed|string
     */
    public function getBodyDataBg($withAttr = false) {
        $bodyDataBg = $this->_props['bodyDataBg'];
        if ($bodyDataBg && $withAttr) {
            $bodyDataBg = 'data-bg="' . $bodyDataBg . '"';
        }
        return $bodyDataBg;
    }

    /**
     * Get edit link html
     *
     * @return string
     */
    public function getEditLinkHtml() {
        $html = '';
        $adminUrl = JURI::root() . '/administrator';
        $icon = dirname($adminUrl) . '/components/com_nicepage/assets/images/button-icon.png?r=' . md5(mt_rand(1, 100000));
        $link = $adminUrl . '/index.php?option=com_nicepage&task=nicepage.autostart&postid=' . $this->_row->id;
        if ($this->_params->get('access-edit')) {
            $html= <<<HTML
        <div><a href="$link" target="_blank" class="edit-nicepage-button">Edit Page</a></div>
        <style>
            a.edit-nicepage-button {
                position: fixed;
                top: 0;
                right: 0;
                background: url($icon) no-repeat 5px 6px;
                background-size: 16px;
                color: #4184F4;
                font-family: Georgia;
                margin: 10px;
                display: inline-block;
                padding: 5px 5px 5px 25px;
                font-size: 14px;
                line-height: 18px;
                background-color: #fff;
                border-radius: 3px;
                border: 1px solid #eee;
                z-index: 9999;
                text-decoration: none;
            }
            a.edit-nicepage-button:hover {
                color: #BC5A5B;
            }
        </style>
HTML;
        }
        return $html;
    }

    /**
     * Prepare page props
     *
     * @param array $props Page props
     *
     * @return mixed
     */
    public function prepareProps($props)
    {
        $props['bodyClass']   = isset($props['bodyClass']) ? $props['bodyClass'] : '';
        if (strpos($props['bodyClass'], '-mode') === false) {
            $props['bodyClass'] = $props['bodyClass'] . ' u-xl-mode';
        }
        $props['bodyStyle']   = isset($props['bodyStyle']) ? $props['bodyStyle'] : '';
        $props['bodyDataBg']  = isset($props['bodyDataBg']) ? $props['bodyDataBg'] : '';
        if ($props['bodyDataBg']) {
            $props['bodyDataBg'] = str_replace('"', '\'', $props['bodyDataBg']);
        }
        $props['head']        = isset($props['head']) ? $props['head'] : '';
        $props['fonts']       = isset($props['fonts']) ? $props['fonts'] : '';
        $props['publishHtml'] = isset($props['publishHtml']) ? $props['publishHtml'] : '';
        $props['publishHtmlTranslations'] = isset($props['publishHtmlTranslations']) ? $props['publishHtmlTranslations'] : array();

        $onContentPrepare = true;
        $lang = explode('-', JFactory::getApplication()->input->get('lang', ''))[0];
        $isDefaultLanguage = true;
        if ($lang && isset($props['publishHtmlTranslations'][$lang])) {
            $props['publishHtml'] = $props['publishHtmlTranslations'][$lang];
            $isDefaultLanguage = false;
        }
        $publishHtml = $props['publishHtml'];
        if (!$props['isPreview'] && $this->_row && property_exists($this->_row, 'text') && $isDefaultLanguage) {
            $text = $this->_row->text;
            if (preg_match('/<\!--np\_fulltext-->([\s\S]+?)<\!--\/np\_fulltext-->/', $text, $fullTextMatches)) {
                $publishHtml = $fullTextMatches[1];
                $onContentPrepare = false;
            }
        }

        // Process image paths
        $publishHtml          = $this->fixBackgroundImageQuots($publishHtml);
        $props['publishHtml'] = $this->fixImagePaths($publishHtml);
        $props['head']        = $this->fixImagePaths($props['head']);
        $props['bodyStyle']   = $this->fixImagePaths($props['bodyStyle']);
        $props['bodyDataBg']  = $this->fixImagePaths($props['bodyDataBg']);
        $props['fonts']       = $this->fixImagePaths($props['fonts']);

        // Process backlink
        if ($this->_config) {
            $hideBacklink = isset($this->_config['hideBacklink']) ? (bool)$this->_config['hideBacklink'] : false;
            $backlink = $props['backlink'];
            $props['backlink'] = $hideBacklink ? str_replace('u-backlink', 'u-backlink u-hidden', $backlink) : $backlink;
        }

        // Process content
        if ($onContentPrepare && $this->_row) {
            $this->_row->doubleСall = true;
            $currentText = $this->_row->text;
            $currentPostId = $this->_row->id;
            $this->_row->text = $props['publishHtml'];
            $this->_row->id = '-1';
            JPluginHelper::importPlugin('content');
            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onContentPrepare', array($this->_context, &$this->_row, &$this->_params, 0));
            $props['publishHtml'] = $this->_row->text;
            $this->_row->text = $currentText;
            $this->_row->id = $currentPostId;
        }

        $props['backlink']       = isset($props['backlink']) ? $props['backlink'] : '';
        $props['pageCssUsedIds'] = isset($props['pageCssUsedIds']) ? $props['pageCssUsedIds'] : '';
        $props['hideHeader']     = isset($props['hideHeader']) ? $props['hideHeader'] : false;
        $props['hideFooter']     = isset($props['hideFooter']) ? $props['hideFooter'] : false;
        $props['hideBackToTop']  = isset($props['hideBackToTop']) ? $props['hideBackToTop'] : false;
        $props['ogTags']         = isset($props['ogTags']) && $props['ogTags'] ? $props['ogTags'] : '';
        if ($props['ogTags']) {
            $props['ogTags']['image'] = $this->fixImagePaths($props['ogTags']['image']);
        }
        $props['metaTags']       = isset($props['metaTags']) ? $props['metaTags'] : '';
        $props['customHeadHtml'] = isset($props['customHeadHtml']) ? $props['customHeadHtml'] : '';
        $props['metaGeneratorContent'] = isset($props['metaGeneratorContent']) ? $props['metaGeneratorContent'] : '';
        $props['canonical'] = isset($props['canonical']) ? $props['canonical'] : '';
        $props['dialogs'] = isset($props['dialogs']) ? $props['dialogs'] : json_encode(array());
        $props['passwordProtection'] = isset($props['passwordProtection']) ? $props['passwordProtection'] : '';

        return $props;
    }

    /**
     * Fixing background image
     *
     * @param string $content Page content
     *
     * @return string
     */
    public function fixBackgroundImageQuots($content) {
        $content = str_replace('url(&quot;', 'url(\'', $content);
        $content = str_replace('&quot;)', '\')', $content);
        return $content;
    }

    /**
     * Fix image paths
     *
     * @param string $content Content
     *
     * @return mixed
     */
    public function fixImagePaths($content) {
        return str_replace('[[site_path_live]]', JURI::root(), $content);
    }

    /**
     * Get page instance
     *
     * @param null   $pageId  Page id
     * @param string $context Component context
     * @param null   $row     Component row
     * @param null   $params  Component params
     *
     * @return Page
     */
    public static function getInstance($pageId, $context, &$row, &$params)
    {
        $pageTable = NicepageHelpersNicepage::getSectionsTable();
        if (!$pageTable->load(array('page_id' => $pageId))) {
            return null;
        }

        if (!is_object(self::$_instance)) {
            self::$_instance = new self($pageTable, $context, $row, $params);
        }

        return self::$_instance;
    }
}
