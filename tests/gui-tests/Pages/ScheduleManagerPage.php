<?php
/**
 * @package     THM_Organizer.GuiTests
 * @subpackage  Page
 * @author      Wolf Rost <Wolf.Rost@mni.thm.de>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use SeleniumClient\By;

/**
 * Page class for the back-end category manager
 */
class ScheduleManagerPage extends iCampusAdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_thm_organizer&view=schedule_manager']";

	/**
	 * URL of the webpage
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = 'administrator/index.php?option=com_thm_organizer&view=schedule_manager';

	/**
	 * Array of toolbar id values for this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $toolbar = array (
			'New' => 'toolbar-new',
			'Merge' => 'toolbar-merge',
            'Activate' => 'toolbar-default',
			'Calculate Data' => 'toolbar-diff',
            'Delete' => 'toolbar-delete',
            'Options' => 'toolbar-options'
			);
}
