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

// Import Joomla! libraries
jimport( 'joomla.application.component.view');


class CreativeContactFormViewSubmissions extends JViewLegacy {
	
	protected $items;
	protected $pagination;
	protected $state;
	
	/**
	 * Display the view
	 *
	 * @return	void
	 */
    public function display($tpl = null) {
    	
    	$this->items		= $this->get('Items');
    	$this->pagination	= $this->get('Pagination');
    	$this->state		= $this->get('State');

    	$forms	= $this->get('creativeForms');
    	//get form options
    	$options        = array();
    	foreach($forms AS $form) {
    		$options[]      = JHtml::_('select.option', $form->id, $form->name . ' (' . $form->count_cs . ')');
    	}
 
       	if(JV == 'j2') {
       	}
       	else {
	    		JHtmlSidebar::addFilter(
    				JText::_('COM_CREATIVECONTACTFORM_SELECT_FORM'),
    				'filter_form_id',
    				JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.form_id'))
    			);

	    		$options = array(
	    						"1"=>array(
	    									"value"=>"1",
	    									"text"=>JText::_('COM_CREATIVECONTACTFORM_SELECT_SUB_STARRED'),
	    									"disable"=>false
	    									),
	    						"2"=>array(
	    									"value"=>"2",
	    									"text"=>JText::_('COM_CREATIVECONTACTFORM_SELECT_SUB_IMPORTANT'),
	    									"disable"=>false
	    									)
	    						);
    			JHtmlSidebar::addFilter(
    				JText::_('COM_CREATIVECONTACTFORM_SELECT_SUB_STATUS'),
    				'filter_status_id',
    				JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.status_id'))
    			);

       		// JHtmlSidebar::addFilter(
       		// 		JText::_('JOPTION_SELECT_PUBLISHED'),
       		// 		'filter_published',
       		// 		JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
       		// );
       		
       		// JHtmlSidebar::addFilter(
       		// 		JText::_('JOPTION_SELECT_ACCESS'),
       		// 		'filter_access',
       		// 		JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
       		// );
       	}
       	$this->addToolbar();
       	if(JV == 'j3')
       		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
    }
    
    /**
     * Add the page title and toolbar.
     *
     * @since	1.6
     */
	protected function addToolbar()
	{
		JToolBarHelper::divider();
		JToolBarHelper::deleteList('', 'submissions.delete', 'JTOOLBAR_DELETE');
		JToolBarHelper::custom('submissions.make_read', 'publish', 'publish', 'Make Read');
		JToolBarHelper::custom('submissions.make_unread', 'unpublish', 'unpublish', 'Make Unread');

		// Version 4.6.3
		JToolBarHelper::custom('submissions.export', 'box-remove', 'box-remove', 'Export'); 
	}
	
	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
				'sp.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}