<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        User
 * @description User file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once dirname(__FILE__) . '/auth.php';

/**
 * Class provides methods to authenticate a user
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THMUser
{
	/**
	 * Username
	 *
	 * @var    String
	 */
	private $_username = null;

	/**
	 * Token
	 *
	 * @var    String
	 */
	private $_token = null;

	/**
	 * Password
	 *
	 * @var    String
	 */
	private $_passwd = null;

	/**
	 * Config
	 *
	 * @var    Object
	 */
	private $_cfg = null;

	/**
	 * Constructor with the configuration object
	 *
	 * @param MySchedConfig $cfg A object which has configurations including
	 */
	public function __construct($cfg)
	{
		$input           = JFactory::getApplication()->input;
		$this->_username = $input->getString("username");
		$this->_token    = $input->get("token");
		$this->_passwd   = $input->get("passwd");
		$this->_cfg      = $cfg;
	}

	/**
	 * Method to authenticate a user
	 *
	 * @return array An array with information if the user is authenticated
	 */
	public function auth()
	{
		// HTTPS Required?
		$protocol = JFactory::getApplication()->input->server->getString('SERVER_PROTOCOL', '');
		if (!empty($this->_cfg->requireHTTPS) AND !strstr(strtolower($protocol), 'https'))
		{
			return array("success" => true, "data" => array('error' => JText::_('COM_THM_ORGANIZER_MESSAGE_HTTPS_REQUIRED')));
		}

		// Token required. Probably always present in the Joomla environment, but we can check anyways.
		if (empty($this->_token))
		{
			return array("success" => false, "data" => array('error' => JText::_('COM_THM_ORGANIZER_MESSAGE_TOKEN_REQUIRED')));
		}

		$auth = new THMAuth($this->_cfg);

		return array("data" => $auth->joomla($this->_token));
	}
}
