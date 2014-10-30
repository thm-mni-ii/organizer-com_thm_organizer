<?php

/**
 * @package    THM_Organizer.UnitTest
 * @author     Wolf Rost <Wolf.Rost@mni.thm.de>
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
// Include the SUT class
require_once JPATH_BASE . '/components/com_thm_organizer/assets/classes/UserSchedule.php';

/**
 * Class THM_OrganizerModelConsumptionSiteTest
 *
 * @package             com_thm_organizer
 * @coversDefaultClass  THMUserSchedule
 *
 * @requires            extension sqlite3
 */
class THM_OrganizerHelperUserScheduleSiteTest extends TestCaseDatabase
{
    /**
     * @var THMUserSchedule
     * @access protected
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     * 
     * @return  null
     */
    protected function setUp()
    {
        parent::setUp();

        $this->saveFactoryState();

        JFactory::$application = $this->getMockCmsApp();

        $_JDA = new THM_OrganizerDataAbstraction;
        $_CFG = new mySchedConfig($_JDA);

        $this->object = new THMUserSchedule($_JDA, $_CFG);
    }

    /**
     * Overrides the parent tearDown method.
     *
     * @return  void
     *
     * @see     PHPUnit_Framework_TestCase::tearDown()
     * @since   3.2
     */
    protected function tearDown()
    {
        $this->restoreFactoryState();

        $this->object = null;

        parent::tearDown();
    }

    /**
     * Gets the data set to be loaded into the database during setup
     *
     * @return xml dataset
     */
    protected function getDataSet()
    {
        $dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

        $dataSet->addTable('jos_thm_organizer_user_schedules', JPATH_TEST_DATABASE . '/jos_thm_organizer_user_schedules.csv');

        return $dataSet;
    }

    /**
     * Method to test the load function
     *
     * @covers ::load
     *
     * @return null
     */
    public function testload()
    {
        $expected = array("success" => true);
        $expected["data"] = file_get_contents(JPATH_TEST_STUBS . "/wngr74_UserScheduleData.txt");

        $reflector = new ReflectionProperty('THMUserSchedule', '_username');
        $reflector->setAccessible(true);
        $reflector->setValue($this->object, "wngr74");

        $actual = $this->object->load();

        $this->assertEquals($expected, $actual);
    }
}
