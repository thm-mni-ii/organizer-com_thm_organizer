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
class ProgramEditPage extends iCampusAdminEditPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath =  "//form[@id='item-form']";

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $tabs = array('details');

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = 'administrator/index.php?option=com_thm_organizer&view=program_edit';

	/**
	 * Array of expected id values for toolbar div elements
	 * @var array
	 */
	public $toolbar = array (
		'Create' => 'toolbar-apply',
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
			array('label' => 'Degree', 'id' => 'jform_degreeID', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Version', 'id' => 'jform_version', 'type' => 'input', 'tab' => 'details'),
            array('label' => 'LSF Field ID', 'id' => 'jform_lsfFieldID', 'type' => 'input', 'tab' => 'details'),
            array('label' => 'Field', 'id' => 'jform_fieldID', 'type' => 'select', 'tab' => 'details')
		);
}
