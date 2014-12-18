<?php
/**
 * @package     THM_Organizer.GuiTests
 * @subpackage  Page
 * @author     Wolf Rost <Wolf.Rost@mni.thm.de>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Page class for the back-end schedule edit screen.
 */
class ScheduleEditPage extends iCampusAdminEditPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath =  "//form[@id='item-form']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = 'administrator/index.php?option=com_thm_organizer&view=schedule_edit';

	/**
	 * Array of expected id values for toolbar div elements
	 * @var array
	 */
	public $toolbar = array (
		'Upload' => 'toolbar-upload',
		'Cancel' => 'toolbar-cancel'
	);

	/**
	 * Array of all the fields of the edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.2
	 */
	public $inputFields = array (
			array('label' => 'Schedule', 'id' => 'jform_file', 'type' => 'input', 'tab' => 'header'),
			array('label' => 'Description', 'id' => 'jform_description', 'type' => 'input', 'tab' => 'header'),
            array('label' => 'Room Assignment Required', 'id' => 'jform_rooms_required', 'type' => 'input', 'tab' => 'header')
		);
}
