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

error_reporting(0);

$module_id = JFactory::getApplication()->input->getInt('creativecontactform_module_id', 0, 'post');
$form_id = JFactory::getApplication()->input->getInt('creativecontactform_form_id', 0, 'post');
$get_token = JFactory::getApplication()->input->getInt('get_token', 0, 'get');

// $comparams = JComponentHelper::getParams( 'com_creativecontactform' );

$db = JFactory::getDBO();
//get form configuration
$query = "
			SELECT
				sp.`email_to`,
				sp.`email_bcc`,
				sp.`email_subject`,
				sp.`email_from`,
				sp.`email_from_name`,
				sp.`email_replyto`,
				sp.`email_replyto_name`,
				sp.`email_info_show_referrer`,
				sp.`email_info_show_ip`,
				sp.`email_info_show_browser`,
				sp.`email_info_show_os`,
				sp.`email_info_show_sc_res`,
				sp.`check_token`
			FROM
				`#__creative_forms` sp
			WHERE sp.published = '1'
			AND sp.id = '".$form_id."'";
$db->setQuery($query);
$form_data = $db->loadAssoc();
$check_token = $form_data['check_token'];

JFactory::getApplication()->allowCache( false );
JFactory::getApplication()->setHeader( 'Content-Type', 'text/plain' );

$check_token_result = J4 ? JFactory::getApplication()->checkToken() : JRequest::checkToken();

if($get_token == 0) {
	if (!$check_token_result && $check_token == 0) {
		echo '[{"invalid":"invalid_token"}]';
	}
	else {
		
		$info = Array();
		
		$config = JFactory::getConfig();
		//get from

		$fromname = $config->get( 'fromname' );
		$fromname = $fromname == '' ? $config->get( 'sitename' ) : $fromname;
		$mailfrom = $config->get('mailfrom');
		if (!$mailfrom ) {
			$info[] = 'Mail from not set in Joomla Global Configuration';
		}
		
		//get email to
		$email_to = array();
		if ( $form_data['email_to'] != '' ) {
			$email_to = explode(',', $form_data['email_to']);
		}
		if (count($email_to) == 0) {
			$email_to = $mailfrom;
		}
		
		// Email subject
		$creativecontactform_subject = $form_data['email_subject'] == '' ? JText::_('COM_CREATIVECONTACTFORM_MESSAGE_SENT_FROM') . ' ' . $config->get('sitename') : $form_data['email_subject'];
		
		$mail = JFactory::getMailer();
		
		//generate the body
		$body = '';
		$user_email = '';
		$user_name = '';
		if(isset($_POST['creativecontactform_fields'])) {
			foreach($_POST['creativecontactform_fields'] as $field_data) {

				$field_label = strip_tags(trim($field_data[1]));
				$field_type = strip_tags(trim($field_data[2]));

				if(isset($field_data[0])) {
					if(is_array($field_data[0])) {
						$field_value = implode(', ',$field_data[0]);
						$field_value = strip_tags(trim($field_value));
					}
					else
						$field_value = strip_tags(trim($field_data[0]));
				}
				else {
					$field_value = '';
				}
				$field_value = str_replace('creative_empty', '', $field_value);

				// start separator
				if($field_type == 'text-area')
					$fields_seperator = ":\n";
				else
					$fields_seperator = ": ";

				// ens separator
				if($field_type == 'text-area')
					$fields_end_seperator = "\r\n\n";
				else
					$fields_end_seperator = "\r\n";

				$body .= $field_label.$fields_seperator.$field_value.$fields_end_seperator;
				
				if($field_type == 'email')
					$user_email = $field_value;

				if($field_type == 'name')
					$user_name = $field_value;
			}
		}

		// data for database
		$sub_name = $user_name;
		$sub_email = $user_email;
		$sub_message = addslashes($body);
		$sub_uploads = '';
		
		// add email info
		$creativecontactform_ip 		= strip_tags( JFactory::getApplication()->input->getVar( 'creativecontactform_ip', '', 'post' ));
		$creativecontactform_referrer 		= strip_tags( JFactory::getApplication()->input->getVar( 'creativecontactform_referrer', '', 'post' ));
		$creativecontactform_page_title 		= strip_tags( JFactory::getApplication()->input->getVar( 'creativecontactform_page_title', '', 'post' ));
		$creativecontactform_browser 		= strip_tags( JFactory::getApplication()->input->getVar( 'creativecontactform_browser', '', 'post' ));
		$creativecontactform_operating_system 		= strip_tags( JFactory::getApplication()->input->getVar( 'creativecontactform_operating_system', '', 'post' ));
		$creativecontactform_sc_res 		= strip_tags( JFactory::getApplication()->input->getVar( 'creativecontactform_sc_res', '', 'post' ));

		if($form_data['email_info_show_referrer'] == 1) {
			$body .= 'Page Title: '.$creativecontactform_page_title."\r\n";
			$body .= 'Page Url: '.$creativecontactform_referrer."\r\n";
			
		}

		if($form_data['email_info_show_ip'] == 1)
			$body .= 'IP Address: '.$creativecontactform_ip."\r\n";	

		if($form_data['email_info_show_browser'] == 1)
			$body .= 'Browser: '.$creativecontactform_browser."\r\n";	

		if($form_data['email_info_show_os'] == 1)
			$body .= 'Operating System: '.$creativecontactform_operating_system."\r\n";	

		if($form_data['email_info_show_sc_res'] == 1)
			$body .= 'Screen Resolution: '.$creativecontactform_sc_res."\r\n";
		
		//Set the body
		$mail->setBody( $body );
		$info[] = 'Body set successfully!';
		
		//Set subject
		$mail->setSubject( $creativecontactform_subject );
		$info[] = 'Subject set successfully!';
		
		//send me a copy check
		if(isset($_POST['creativecontactform_send_copy_enable'])) {
			if((int) $_POST['creativecontactform_send_copy_enable'] == 1 && $user_email != '') {
				if(is_array($email_to)) {
					$email_to[] = $user_email;
				}
				else {
					$email_to_final = array($email_to, $user_email);
					$email_to = $email_to_final;
				}
			}
		}
		
		//Set Recipient
		$mail->addRecipient( $email_to );
		//$info[] = 'Recipient set: '.$email_to;
		
		//Set Sender
		$sender_email = $form_data['email_from'] == '' ? ($mailfrom == '' ? $user_email : $mailfrom) : $form_data['email_from'];
		$sender_name = $form_data['email_from_name'] == '' ? ($fromname == '' ? $user_name : $fromname) : $form_data['email_from_name'];
		$mail->setSender( array( $sender_email, $sender_name ) );
		$info[] = 'Sender set successfully!';
		
		// set reply to
		$replyto_email = $form_data['email_replyto'] == '' ? ($user_email == '' ?  $mailfrom : $user_email) : $form_data['email_replyto'];
		$mail->ClearReplyTos();
		$email_replyto_name = $form_data['email_replyto_name'] == '' ? ($user_name == '' ? $fromname : $user_name) : $form_data['email_replyto_name'];
		$mail->addReplyTo($replyto_email,$email_replyto_name);
		$info[] = 'Reply to set successfully!';
		
		// add blind carbon recipient
		if ($form_data['email_bcc'] != '') {
			$email_bcc = explode(',', $form_data['email_bcc']);
			$mail->addBCC($email_bcc);
			$info[] = 'BCC recipients added successfully!';
		}
		
		// send email///////////////////////////////////////////////////////////////////////////
		$mailer_res = $mail->Send();
		if ($mailer_res === true ) {
			JSession::getFormToken(true);
			$info[] = 'Email sent successful';
		}
		else $info[] = 'There are problems sending email';

		//generates json output///////////////////////////////////////////////////////////////
		if ($mailer_res !== true) {
			echo '[{"invalid":"problem_sending_email"}]';
		}
		else {
			echo '[{';
			if(sizeof($info) > 0) {
				echo '"info": ';
				echo '[';
				foreach ($info as $k => $data) {
					echo '"'.$data.'"';
					if ($k != sizeof($info) - 1)
						echo ',';
				}
				echo ']';
			}
			echo '}]';
		}

	}
}
else {
	echo JSession::getFormToken();
}
jexit();