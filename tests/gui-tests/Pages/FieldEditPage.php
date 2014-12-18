<?php
/**
 * @package     THM_Organizer.GuiTests
 * @subpackage  Page
 * @author     Wolf Rost <Wolf.Rost@mni.thm.de>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Page class for the back-end field edit screen.
 */
class FieldEditPage extends iCampusAdminEditPage
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
	protected $url = 'administrator/index.php?option=com_thm_organizer&view=field_edit';

	/**
	 * Array of expected id values for toolbar div elements
	 * @var array
	 */
	public $toolbar = array (
		'Save & Close' => 'toolbar-save',
		'Cancel' => 'toolbar-cancel'
	);

	/**
	 * Array of all the fields of the edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.2
	 */
	public $inputFields = array (
			array('label' => 'Name', 'id' => 'jform_field', 'type' => 'input', 'tab' => 'header'),
			array('label' => 'Untis ID', 'id' => 'jform_gpuntisID', 'type' => 'input', 'tab' => 'header'),
            array('label' => 'Color', 'id' => 'jform_colorID', 'type' => 'select', 'tab' => 'header')
		);
}
