<?php
/**
 * @package     THM_Organizer.GuiTests
 * @subpackage  Page
 * @author     Wolf Rost <Wolf.Rost@mni.thm.de>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Page class for the back-end category edit screen.
 */
class MonitorEditPage extends iCampusAdminEditPage
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
	protected $url = 'administrator/index.php?option=com_thm_organizer&view=monitor_edit';

	/**
	 * Array of expected id values for toolbar div elements
	 * @var array
	 */
	public $toolbar = array (
		'Save & Close' => 'toolbar-save',
		'Save & New' => 'toolbar-save-new',
		'Cancel' => 'toolbar-cancel'
	);

	/**
	 * Array of all the fields of the edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.2
	 */
	public $inputFields = array (
			array('label' => 'Room', 'id' => 'jform_roomID', 'type' => 'select', 'tab' => 'header'),
			array('label' => 'IP Address', 'id' => 'jform_ip', 'type' => 'input', 'tab' => 'header'),
            array('label' => 'Default Settings', 'id' => 'jform_useDefaults', 'type' => 'select', 'tab' => 'header'),
            array('label' => 'Display Behaviour', 'id' => 'jform_display', 'type' => 'select', 'tab' => 'header'),
            array('label' => 'Display Refresh Rate', 'id' => 'jform_schedule_refresh', 'type' => 'select', 'tab' => 'header'),
            array('label' => 'Content Refresh Rate', 'id' => 'jform_content_refresh', 'type' => 'select', 'tab' => 'header'),
            array('label' => 'Display Content', 'id' => 'jform_content', 'type' => 'select', 'tab' => 'header')
		);
}
