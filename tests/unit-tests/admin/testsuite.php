<?php
/**
 * @version     v0.0.1
 * @category 	Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.test.admin
 * @name        AllComThmOrganizerAdminTests
 * @description unit testsuite from admin com_thm_organizer
 * @author      Dennis Priefer, <dennis.priefer@mni.thm.de>
 * @author      Niklas Simonis, <niklas.simonis@mni.thm.de>
 * @author		Wolf Rost, 		<Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'framework_include.php';

/**
 * Testsuite class for admin component com_thm_organizer
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.test.admin
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class AllComThmOrganizerAdminTests
{
	/**
	 * Method to initialise the test suite and bind the test files to the suite
	 * 
	 * @return  PHPUnit_Framework_TestSuite  The test suite object
	 */
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('Component THM Organizer Admin Test');

		// Add Test Files here (example below
		// $suite->addTestFile(__DIR__ . '/THMOrganizerTestFile.php');
		$suite->addTestFile(__DIR__ . '/THM_OrganizersControllerVirtual_ScheduleAdminTest.php');
		$suite->addTestFile(__DIR__ . '/THM_OrganizersModelVirtual_Schedule_EditAdminTest.php');
		$suite->addTestFile(__DIR__ . '/THM_OrganizersModelVirtual_Schedule_ManagerAdminTest.php');
		
		return $suite;
	}
}
