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
class CategoryEditPage extends iCampusAdminEditPage
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
	protected $url = 'administrator/index.php?option=com_thm_organizer&view=category_edit';

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
			array('label' => 'Name', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'header'),
			array('label' => 'Content Category', 'id' => 'jform_contentCatID', 'type' => 'select', 'tab' => 'header'),
            array('label' => 'Global Monitor Display', 'id' => 'jform_global', 'type' => 'select', 'tab' => 'header'),
            array('label' => 'Reserves Resources', 'id' => 'jform_reserves', 'type' => 'select', 'tab' => 'header'),
            array('label' => 'Description', 'id' => 'jform_description', 'type' => 'textarea', 'tab' => 'header')
		);
}
