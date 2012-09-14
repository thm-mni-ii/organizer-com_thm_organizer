<?php
/**
 * @version     v0.0.1
 * @category 	Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.test.admin
 * @name        AllComThmOrganizerGuiTests
 * @description gui testsuite from com_thm_organizer
 * @author      Dennis Priefer, <dennis.priefer@mni.thm.de>
 * @author      Niklas Simonis, <niklas.simonis@mni.thm.de>
 * @author		Wolf Rost, 		<Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'framework_include.php';
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'JoomlaSeleniumTest.php';

/**
 * Testsuite class for admin component com_thm_organizer
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.test.admin
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class AllComThmOrganizerGuiTests
{
	/**
	 * Method to initialise the test suite and bind the test files to the suite
	 * 
	 * @return  PHPUnit_Framework_TestSuite  The test suite object
	 */
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('Component THM Organizer GUI Test');

		// Add Test Files here (example below
		// $suite->addTestFile(__DIR__.'/com_thm_organizer_example_file.php');

		return $suite;
	}
}
