--
-- Table structure for table `#__creative_forms`
--
CREATE TABLE IF NOT EXISTS `#__creative_forms` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email_to` text DEFAULT NULL,
  `email_bcc` text DEFAULT NULL,
  `email_subject` text DEFAULT NULL,
  `email_from` text DEFAULT NULL,
  `email_from_name` text DEFAULT NULL,
  `email_replyto` text DEFAULT NULL,
  `email_replyto_name` text DEFAULT NULL,
  `shake_count` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `shake_distanse` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `shake_duration` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `id_template` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `name` text DEFAULT NULL,
  `top_text` text DEFAULT NULL,
  `pre_text` text DEFAULT NULL,
  `thank_you_text` text DEFAULT NULL,
  `send_text` text DEFAULT NULL,
  `send_new_text` text DEFAULT NULL,
  `close_alert_text` text DEFAULT NULL,
  `form_width` text DEFAULT NULL,
  `alias` text DEFAULT NULL,
  `created` datetime NOT NULL,
  `publish_up` datetime NOT NULL,
  `publish_down` datetime NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL,
  `access` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `featured` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  `redirect` enum('0','1') NOT NULL DEFAULT '0',
  `redirect_itemid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `redirect_url` text DEFAULT NULL,
  `redirect_delay` int(11) NOT NULL DEFAULT '0',
  `send_copy_enable` enum('0','1') NOT NULL DEFAULT '0',
  `send_copy_text` text DEFAULT NULL,
  `show_back` enum('0','1') NOT NULL DEFAULT '1',
  `email_info_show_referrer` tinyint(4) NOT NULL DEFAULT '1',
  `email_info_show_ip` tinyint(4) NOT NULL DEFAULT '1',
  `email_info_show_browser` tinyint(4) NOT NULL DEFAULT '1',
  `email_info_show_os` tinyint(4) NOT NULL DEFAULT '1',
  `email_info_show_sc_res` tinyint(4) NOT NULL DEFAULT '1',
  `custom_css` text DEFAULT NULL,
  `render_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `popup_button_text` text DEFAULT NULL,
  `static_button_position` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `static_button_offset` text DEFAULT NULL,
  `appear_animation_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `check_token` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `next_button_text` text DEFAULT NULL,
  `prev_button_text` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET = `utf8`;

--
-- Table structure for table `#__creative_fields`
--

CREATE TABLE IF NOT EXISTS `#__creative_fields` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(10) unsigned NOT NULL,
  `id_form` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` text DEFAULT NULL,
  `tooltip_text` text DEFAULT NULL,
  `id_type` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `alias` text DEFAULT NULL,
  `created` datetime NOT NULL,
  `publish_up` datetime NOT NULL,
  `publish_down` datetime NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL,
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  `required` enum('0','1') NOT NULL DEFAULT '0',
  `width` text DEFAULT NULL,
  `field_margin_top` text DEFAULT NULL,
  `select_show_scroll_after` int(11) NOT NULL DEFAULT '10',
  `select_show_search_after` int(11) NOT NULL DEFAULT '10',
  `message_required` text DEFAULT NULL,
  `message_invalid` text DEFAULT NULL,
  `ordering_field` enum('0','1') NOT NULL DEFAULT '0',
  `show_parent_label` enum('0','1') NOT NULL DEFAULT '1',
  `select_default_text` text DEFAULT NULL,
  `select_no_match_text` text DEFAULT NULL,
  `upload_button_text` text DEFAULT NULL,
  `upload_minfilesize` text DEFAULT NULL,
  `upload_maxfilesize` text DEFAULT NULL,
  `upload_acceptfiletypes` text DEFAULT NULL,
  `upload_minfilesize_message` text DEFAULT NULL,
  `upload_maxfilesize_message` text DEFAULT NULL,
  `upload_acceptfiletypes_message` text DEFAULT NULL,
  `captcha_wrong_message` text DEFAULT NULL,
  `datepicker_date_format` text DEFAULT NULL,
  `datepicker_animation` text DEFAULT NULL,
  `datepicker_style` smallint(5) unsigned NOT NULL DEFAULT '1',
  `datepicker_icon_style` smallint(6) NOT NULL DEFAULT '1',
  `datepicker_show_icon` smallint(5) unsigned NOT NULL DEFAULT '1',
  `datepicker_input_readonly` smallint(5) unsigned NOT NULL DEFAULT '1',
  `datepicker_number_months` smallint(5) unsigned NOT NULL DEFAULT '1',
  `datepicker_mindate` text DEFAULT NULL,
  `datepicker_maxdate` text DEFAULT NULL,
  `datepicker_changemonths` smallint(5) unsigned NOT NULL DEFAULT '0',
  `datepicker_changeyears` smallint(5) unsigned NOT NULL DEFAULT '0',
  `column_type` tinyint(4) NOT NULL DEFAULT '0',
  `custom_html` text DEFAULT NULL,
  `google_maps` text DEFAULT NULL,
  `heading` text DEFAULT NULL,
  `recaptcha_site_key` text DEFAULT NULL,
  `recaptcha_security_key` text DEFAULT NULL,
  `recaptcha_wrong_message` text DEFAULT NULL,
  `recaptcha_theme` text DEFAULT NULL,
  `recaptcha_type` text DEFAULT NULL,
  `contact_data` text DEFAULT NULL,
  `contact_data_width` smallint(6) NOT NULL DEFAULT '120',
  `creative_popup` text DEFAULT NULL,
  `creative_popup_embed` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_form` (`id_form`)
) ENGINE=MyISAM CHARACTER SET = `utf8`;

--
-- Table structure for table `#__creative_field_types`
--

CREATE TABLE IF NOT EXISTS `#__creative_field_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET = `utf8`;

--
-- Table structure for table `#__creative_form_options`
--

CREATE TABLE IF NOT EXISTS `#__creative_form_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_parent` int(11) unsigned NOT NULL  DEFAULT '0',
  `name` text DEFAULT NULL,
  `value` text DEFAULT NULL,
  `ordering` int(11) NOT NULL  DEFAULT '0',
  `showrow` enum('0','1') NOT NULL DEFAULT '1',
  `selected` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET = `utf8`;

--
-- Table structure for table `#__contact_templates`
--

CREATE TABLE IF NOT EXISTS `#__contact_templates` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` text DEFAULT NULL,
  `created` datetime NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `publish_up` datetime NOT NULL,
  `publish_down` datetime NOT NULL,
  `published` tinyint(1) NOT NULL,
  `checked_out` int(10) unsigned NOT NULL,
  `checked_out_time` datetime NOT NULL,
  `access` int(10) unsigned NOT NULL,
  `featured` tinyint(3) unsigned NOT NULL,
  `ordering` int(11) NOT NULL,
  `language` char(7) NOT NULL,
  `styles` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET = `utf8`;


--
-- Dumping data for table  `#__creative_field_types`
--

INSERT IGNORE INTO `#__creative_field_types` (`id`, `name`) VALUES
(1, 'Text Input'),
(2, 'Text Area'),
(3, 'Name'),
(4, 'E-mail'),
(5, 'Address'),
(6, 'Phone'),
(7, 'Number'),
(8, 'Url'),
(9, 'Select'),
(10, 'Multiple Select'),
(11, 'Checkbox'),
(12, 'Radio'),
(13, 'Captcha : PRO feature'),
(14, 'File upload : PRO feature'),
(16, 'Custom Html : PRO feature'),
(15, 'Datepicker : PRO feature'),
(17, 'Heading : PRO feature'),
(18, 'Google Maps : PRO feature'),
(19, 'Google reCAPTCHA : PRO feature'),
(20, 'Contact Data : PRO feature'),
(21, 'Creative Popup : PRO feature'),
(22, 'Multiple Recipients : BUSINESS feature'),
(23, 'Time : BUSINESS feature'),
(24, 'Stars : BUSINESS feature'),
(25, 'E-Signature : BUSINESS feature'),
(26, 'Page Break : BUSINESS feature'),
(27, 'If-Then : BUSINESS feature'),
(28, 'Hidden : BUSINESS feature');

-- table submissions

CREATE TABLE IF NOT EXISTS `#__creative_submissions` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_form` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `email` text DEFAULT NULL,
  `message` text DEFAULT NULL,
  `ip` text DEFAULT NULL,
  `browser` text DEFAULT NULL,
  `op_s` text DEFAULT NULL,
  `sc_res` text DEFAULT NULL,
  `name` text DEFAULT NULL,
  `viewed` enum('0','1') NOT NULL DEFAULT '0',
  `country` text DEFAULT NULL,
  `city` text DEFAULT NULL,
  `page_title` text DEFAULT NULL,
  `page_url` text DEFAULT NULL,
  `star_index` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `imp_index` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `uploads` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT IGNORE INTO `#__creative_submissions` (`id`, `id_form`, `date`, `email`, `message`, `ip`, `browser`, `op_s`, `sc_res`, `name`, `viewed`, `country`, `city`, `page_title`, `page_url`, `star_index`, `imp_index`, `uploads`) VALUES
(NULL, 1, NOW(), 'demo1@creative-solutions.net', 'Name: Demo User\r\nEmail: demo@creative-solutions.net\r\nCountry: Armenia\r\nHow did you find us?: Web search\r\nMessage:\nHello,\r\n\r\nThis is test message\r\n\n', '::1', 'Google Chrome 49.0.2623.112', 'Windows 7', '1920X1080', 'Demo User 1', '0', '', '', 'CCF', 'http://localhost/Joomla_3.5.0/index.php/ccf', 1, 1, ''),
(NULL, 1, NOW(), 'demo2@creative-solutions.net', 'Name: Demo User\r\nEmail: demo@creative-solutions.net\r\nCountry: Armenia\r\nHow did you find us?: Web search\r\nMessage:\nHello,\r\n\r\nThis is test message\r\n\n', '::1', 'Google Chrome 49.0.2623.112', 'Windows 7', '1920X1080', 'Demo User 2', '0', '', '', 'CCF', 'http://localhost/Joomla_3.5.0/index.php/ccf', 2, 2, ''),
(NULL, 1, NOW(), 'demo3@creative-solutions.net', 'Name: Demo User\r\nEmail: demo@creative-solutions.net\r\nCountry: Armenia\r\nHow did you find us?: Web search\r\nMessage:\nHello,\r\n\r\nThis is test message\r\n\n', '::1', 'Google Chrome 49.0.2623.112', 'Windows 7', '1920X1080', 'Demo User 3', '0', '', '', 'CCF', 'http://localhost/Joomla_3.5.0/index.php/ccf', 3, 0, ''),
(NULL, 1, NOW(), 'demo4@creative-solutions.net', 'Name: Demo User\r\nEmail: demo@creative-solutions.net\r\nCountry: Armenia\r\nHow did you find us?: Web search\r\nMessage:\nHello,\r\n\r\nThis is test message\r\n\n', '::1', 'Google Chrome 49.0.2623.112', 'Windows 7', '1920X1080', 'Demo User 4', '0', '', '', 'CCF', 'http://localhost/Joomla_3.5.0/index.php/ccf', 4, 1, ''),
(NULL, 1, NOW(), 'demo5@creative-solutions.net', 'Name: Demo User\r\nEmail: demo@creative-solutions.net\r\nCountry: Armenia\r\nHow did you find us?: Web search\r\nMessage:\nHello,\r\n\r\nThis is test message\r\n\n', '::1', 'Google Chrome 49.0.2623.112', 'Windows 7', '1920X1080', 'Demo User 5', '0', '', '', 'CCF', 'http://localhost/Joomla_3.5.0/index.php/ccf', 5, 2, '');

