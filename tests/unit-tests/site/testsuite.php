<?php
/**
 * @version     v0.0.1
 * @category     Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.test.site
 * @name        AllComThmOrganizerSiteTests
 * @description unit testsuite from site com_thm_organizer
 * @author        Wolf Rost,         <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR . 'framework_include.php';

/**
 * Testsuite class for site component com_thm_organizer
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.test.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class AllComThmOrganizerSiteTests
{
    /**
     * Method to initialise the test suite and bind the test files to the suite
     *
     * @return  PHPUnit_Framework_TestSuite  The test suite object
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Component THM Organizer Site Test');

        // Add Test Files here (example below
        // $suite->addTestFile(__DIR__ . '/THMOrganizerTestFile.php');
        $suite->addTestFile(__DIR__ . '/THM_OrganizerModelScheduleSiteTest.php');

        return $suite;
    }
}
