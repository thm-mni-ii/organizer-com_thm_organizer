<?php
/**
 * @package     THM_Organizer.GuiTests
 * @subpackage  Page
 * @author      Wolf Rost <Wolf.Rost@mni.thm.de>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use SeleniumClient\By;

/**
 * Page class for the back-end user manager
 */
class UserManagerPage extends iCampusAdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_thm_organizer&view=user_manager']";

	/**
	 * URL of the webpage
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = 'administrator/index.php?option=com_thm_organizer&view=user_manager';

	/**
	 * Array of toolbar id values for this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $toolbar = array (
			'Add Users' => 'toolbar-popup-new',
            'Delete' => 'toolbar-delete',
			'Options' => 'toolbar-options'
			);
}
