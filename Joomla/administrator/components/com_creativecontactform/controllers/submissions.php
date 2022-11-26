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

jimport('joomla.application.component.controlleradmin');

class CreativeContactFormControllerSubmissions extends JControllerAdmin
{
	/**
	 * Constructor.
	 *
	 * @param	array	$config	An optional associative array of configuration settings.
	 *
	 * @return	ContactControllerContacts
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('delete', 'deleteSubmission');
		$this->registerTask('make_read', 'make_read');
		$this->registerTask('make_unread', 'make_unread');

		// make_read
	}

	public function deleteSubmission() {
		$pks   = $this->input->post->get('cid', array(), 'array');

		// Get the model
		$model = $this->getModel();

		$result = $model->deleteSubmission($pks);

		$link = 'index.php?option=com_creativecontactform&view=submissions';
		$msg_type = 'message';
		$msg = JText::_( 'COM_CREATIVECONTACTFORM_SUBMISSION_DELETED' );
		$this->setRedirect($link, $msg, $msg_type);
	}
	public function make_read() {
		$pks   = $this->input->post->get('cid', array(), 'array');

		// Get the model
		$model = $this->getModel();

		$result = $model->make_read($pks);

		$link = 'index.php?option=com_creativecontactform&view=submissions';
		$msg_type = 'message';
		$msg = JText::_( 'COM_CREATIVECONTACTFORM_SUBMISSION_SET_READ' );
		$this->setRedirect($link, $msg, $msg_type);
	}
	
	public function make_unread() {
		$pks   = $this->input->post->get('cid', array(), 'array');

		// Get the model
		$model = $this->getModel();

		$result = $model->make_unread($pks);

		$link = 'index.php?option=com_creativecontactform&view=submissions';
		$msg_type = 'message';
		$msg = JText::_( 'COM_CREATIVECONTACTFORM_SUBMISSION_SET_UNREAD' );
		$this->setRedirect($link, $msg, $msg_type);
	}
	
	// 4.6.3 added
	public function export() {
		$pks   = $this->input->post->get('cid', array(), 'array');

		// Get the model
		$model = $this->getModel();

		$model->export($pks);
	}
	

	/**
	 * Proxy for getModel.
	 *
	 * @param	string	$name	The name of the model.
	 * @param	string	$prefix	The prefix for the PHP class name.
	 *
	 * @return	JModel
	 * @since	1.6
	 */
	public function getModel($name = 'submissions', $prefix = 'CreativeContactFormModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
	
}
