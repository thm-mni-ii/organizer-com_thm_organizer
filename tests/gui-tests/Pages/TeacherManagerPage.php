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
class TeacherManagerPage extends iCampusAdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_thm_organizer&view=teacher_manager']";

	/**
	 * URL of the webpage
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = 'administrator/index.php?option=com_thm_organizer&view=teacher_manager';

	/**
	 * Array of toolbar id values for this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $toolbar = array (
			'New' => 'toolbar-new',
			'Edit' => 'toolbar-edit',
            'Automatic Merge' => 'toolbar-merge-all',
            'Merge' => 'toolbar-merge',
            'Delete' => 'toolbar-delete',
			'Options' => 'toolbar-options'
			);
}
