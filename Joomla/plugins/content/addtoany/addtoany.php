<?php
/**
 * AddToAny Plugin
 * 
 * @package    addtoany
 * @subpackage Social
 * @copyright (C) AddToAny
 * @license GNU/GPLv3
 */

defined('_JEXEC') or die('Restricted access');

defined('DS') or define('DS', DIRECTORY_SEPARATOR);

jimport('joomla.plugin.plugin');

/**
 * AddToAny Plugin
 *
 */
class plgContentAddToAny extends JPlugin
{
	
	/**
	 * Constructor.
	 * 
	 * @access public
	 * @param mixed &$subject
	 * @param mixed $config
	 * @return void
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
		$this->setBaseURL();
	}
	
	/**
	 * onContentPrepare function.
	 * 
	 * @access public
	 * @param mixed $context
	 * @param mixed &$article
	 * @param mixed &$params
	 * @param int $page (default: 0)
	 * @return void
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		$this->createAddToAny($article);
	}
	
	/**
	 * onContentPrepareForm function.
	 * 
	 * @access public
	 * @param mixed $form
	 * @param mixed $data
	 * @return void
	 */
	public function onContentPrepareForm($form, $data)
	{
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$css = '.addtoany_larger_textarea { width: 100%; max-width: 100%; } ';
		$css .= '.addtoany_smaller_text { width: 6rem; }';
		// Hide deprecated field if empty
		$js = 'if (typeof jQuery != "undefined") { jQuery(document).ready(function() { '
			. '  var label = jQuery(".addtoany_deprecated_categories_excluded"); '
			. '  var field_id = label.attr("for"); '
			. '  var field = jQuery("#" + field_id); '
			. '  if (field.val() == "") { label.hide(); field.hide(); } '
		. '}) }';
		
		// Make AddToAny config textareas larger in admin
		if ($app->isClient('administrator'))
		{
			$doc->addStyleDeclaration($css);
			$doc->addScriptDeclaration($js);
		}
	}
	
	/**
	 * createAddToAny function.
	 * 
	 * @access private
	 * @param mixed $article
	 * @return void
	 */
	private function createAddToAny($article)
	{
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$lang = JFactory::getLanguage();
		$menu = $app->getMenu();
		$menu_active = $menu->getActive();
		$menu_default = $menu->getDefault($lang->getTag());

		$front_page = 
		(
			isset($menu_active) &&
			isset($menu_default) &&
			$menu_active == $menu_default &&
			$app->input->get('view') == $menu_default->query['view']
		) ? true : false;
		
		$share_url = $this->getArticleUrl($article);
		$share_title = isset($article->title) ? $article->title : '';
		
		// Add AddToAny CSS
		$doc->addStyleSheet(JURI::base() . 'plugins/content/addtoany/addtoany.css');
		
		// Add Additional JavaScript
		$this->createAdditionalJSOnce($doc);

		// Add AddToAny JavaScript asynchronously
		$doc->addScript('https://static.addtoany.com/menu/page.js', array(), array(
			'type' => 'text/javascript',
			'async' => 'async',
		));
		
		// Get AddToAny Universal Share Button HTML
		$service_buttons_html = $this->createServiceButtons();
		
		// Create AddToAny Kit HTML
		$output = '<div class="addtoany_container">';
		$output .= '<span class="a2a_kit a2a_kit_size_' . $this->params->get('icon_size', '32') . ' addtoany_list" data-a2a-url="' . urldecode($share_url);
		$output .= '" data-a2a-title="' . htmlspecialchars($share_title, ENT_QUOTES) . '">' . PHP_EOL;
		$output .= $service_buttons_html;
		$output .= '</span>' . PHP_EOL;
		$output .= '</div>';
		$output .= '';
		
		// If no text or no title
		if (!isset($article->text) || !isset($article->title))
		{
			// Disable sharing
			$output = '';
		}
		
		// If Display in Content is off 
		elseif ($this->params->get('content', '1') != '1')
		{
			// Disable sharing
			$output = '';
		}
		
		// If <!--noshare--> is in text
		elseif (strpos($article->text, '<!--noshare-->') !== false)
		{
			// Disable sharing
			$output = '';
			
			// Remove <!--noshare-->
			$article->text = str_replace('<!--noshare-->', '', $article->text);
		}
		
		// If front page, and front page is disabled
		elseif (
			// Front page?
			$front_page &&
			// Front page disabled?
			$this->params->get('front_page', '1') != '1'
		) {
			// Disable sharing
			$output = '';
		}
		
		// If featured page, and featured page is disabled
		elseif (
			// Featured page?
			$app->input->get('view') == 'featured' &&
			// Featured page disabled?
			$this->params->get('featured_page', '1') != '1'
		) {
			// Disable sharing
			$output = '';
		}
		
		// If category (but not front page), and display on category pages is disabled
		elseif (
			// Category page?
			$app->input->get('view') == 'category' &&
			// And not front page?
			!$front_page &&
			// Category pages disabled?
			$this->params->get('category_pages', '1') != '1'
		) {
			// Disable sharing
			$output = '';
		}
		
		// If categories are excluded
		elseif ($this->params->get('categories_excluded_2', 0) != 0)
		{
			// If current (sub)category is excluded
			if (isset($article->catid) && in_array($article->catid, $this->params->get('categories_excluded_2', array())))
			{
				// Disable sharing
				$output = '';
			}
		}
		
		// @deprecated
		// If categories are excluded
		elseif ($this->params->get('categories_excluded', 0) != 0)
		{
			$categories_excluded = $this->params->get('categories_excluded', 0);
			$categories_excluded = trim($categories_excluded);
			$categories_excluded = str_replace(' ', '', $categories_excluded);
			$categories_excluded = explode(',', $categories_excluded);
			
			// If current (sub)category is excluded
			if (isset($article->catid) && in_array($article->catid, $categories_excluded))
			{
				// Disable sharing
				$output = '';
			}
		}
		
		// If there is still output
		if ($output != '')
		{
			// Append, prepend, or both
			if ('bottom' == $this->params->get('content_position', 'bottom'))
			{
				// Append buttons to text
				$article->text = $article->text . $output;
			}
			elseif ('top' == $this->params->get('content_position'))
			{
				// Append text to buttons
				$article->text = $output . $article->text;
			}
			elseif ('both' == $this->params->get('content_position'))
			{
				// Text sandwich
				$article->text = $output . $article->text . $output;
			}
		}
	}
	
	/**
	 * createServiceButtons function.
	 * 
	 * @access private
	 * @return void
	 */
	private function createServiceButtons()
	{
		$universal_after = '';
		$universal_before = '';
		$universal_html = '<a class="a2a_dd" href="https://www.addtoany.com/share">' . $this->params->get('universal_button_innerhtml', '') . '</a>' . PHP_EOL;
		
		if ('after' == $this->params->get('universal_button_position', 'after'))
		{
			$universal_after = $universal_html;
		}
		elseif ('before' == $this->params->get('universal_button_position'))
		{
			$universal_before = $universal_html;
		}
		
		$html = $universal_before . $this->params->get('service_buttons_html_code') . PHP_EOL . $universal_after;
		
		return $html;
	}
	
	/**
	 * createAdditionalJSOnce function.
	 * 
	 * @access private
	 * @return void
	 */
	private function createAdditionalJSOnce($doc)
	{
		static $created = false;
		$additional_js = $this->params->get('additional_js', '');
		
		if (!$created && $additional_js != '')
		{
			$doc->addScriptDeclaration(
				'window.a2a_config=window.a2a_config||{};'
				. 'a2a_config.callbacks=[];'
				. 'a2a_config.overlays=[];'
				. 'a2a_config.templates={};'
				. $additional_js
			);
			$created = true;
		}
	}
	
	/**
	 * getArticleUrl function.
	 * 
	 * @access private
	 * @param mixed &$article
	 * @return void
	 */
	private function getArticleUrl(&$article)
	{
		if (!is_null($article))
		{
			if (isset($article->id) && isset($article->catid))
			{
				// If a K2 item
				if (class_exists('K2HelperRoute') && is_object($article->params) && $article->params->exists('k2Sef'))
				{
					$url = JRoute::_(K2HelperRoute::getItemRoute($article->id, $article->catid));
				}
				// Otherwise, a standard Joomla article
				else
				{
					$url = JRoute::_(ContentHelperRoute::getArticleRoute($article->id, $article->catid));
				}
				
				return JRoute::_($this->baseURL . $url, true, 0);
			}
			else
			{
				return $this->baseURL;
			}
		}
	}
	
	/**
	 * setBaseURL function.
	 * 
	 * @access private
	 * @return void
	 */
	private function setBaseURL(){
		$uri = JURI::getInstance();
		$this->baseURL = $uri->toString(array('scheme', 'host', 'port'));
	}
	
}