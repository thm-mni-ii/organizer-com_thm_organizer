<?php
/**
 * @package    THM_Organizer.UnitTest
 * @author     Andrej Sajenko <Andrej.Sajenko@mni.thm.de>
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Include the SUT class
require_once JPATH_ADMINISTRATOR . '/components/com_thm_organizer/models/color_manager.php';

/**
 * Class THM_OrganizerModelCategoryAdminTest
 *
 * @package             com_thm_organizer
 * @coversDefaultClass  THM_OrganizerModelColor_Manager
 *
 * @requires            extension sqlite3
 */
class THM_OrganizerModelColor_ManagerTest extends TestCaseDatabase
{
    /**
     * @var THM_OrganizerModelCategoryEdit
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
        parent::setup();

        $this->saveFactoryState();

        JFactory::$application = $this->getMockCmsApp();

        $config = array();

        // View name
        $config['name'] = "color_save";

        // Set a base path for use by the controller
        //$config['base_path'] = "";

        // If the default task is set, register it as such
        // $config['default_task'] = "";

        // Set the default model search path
        //$config['model_path'] = "";

        // Set the default view search path
        $config['view_path'] = "";

        // Set the default view.
        $config['default_view'] = "";
        
        JTable::addIncludePath(JPATH_COMPONENT . '/tables');
        
        $connect = parent::getConnection();
        $this->_db = JFactory::getDbo();
       
      	$this->object = new THM_OrganizerModelColor_Manager;
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
    protected function getDataSet() {
        $dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

        $dataSet->addTable('jos_thm_organizer_colors', JPATH_TEST_DATABASE . '/jos_thm_organizer_colors.csv');
        $dataSet->addTable('jos_assets', JPATH_TEST_DATABASE . '/jos_assets.csv');
        
        return $dataSet;
    }

    /**
     * Method to test the getListQuery function
     *
     * @covers ::getListQuery
     */
    public function testgetListQuery()
    {
        // to get access to the protected function
        $reflector = new ReflectionMethod('THM_OrganizerModelColor_Manager', 'getListQuery');
        $reflector->setAccessible(true);
        $query = $reflector->invoke($this->object);

        $actual = $query->__toString();

        $query2 = $this->_db->getQuery(true);
        $query2->select("id, name, color, 'index.php?option=com_thm_organizer&view=color_edit&id=' || id AS link");
        $query2->from('#__thm_organizer_colors');
        $query2->order("name asc");
        $expected = $query2->__toString();

        $this->assertEquals($expected, $actual);
    }
    
    /**
     * Method to test the populateState function
     *
     * @covers ::populateState
     */
    public function testpopulateState()
    {
        // to get access to the protected function
        $reflector = new ReflectionMethod('THM_OrganizerModelColor_Manager', 'populateState');
        $reflector->setAccessible(true);
        $reflector->invoke($this->object);

        // to get access to the protected variable
        $reflector = new ReflectionProperty('THM_OrganizerModelColor_Manager', 'state');
        $reflector->setAccessible(true);
        $state = $reflector->getValue($this->object);

        $expected1 = "name asc";
        $expected2 = "name";
        $expected3 = "asc";
        $expected4 = "20";
        $expected5 = "0";

        $actual1 = $state->get('list.fullordering');
        $actual2 = $state->get('list.ordering');
        $actual3 = $state->get('list.direction');
        $actual4 = $state->get('list.limit');
        $actual5 = $state->get('list.start');

        $this->assertEquals($expected1, $actual1, "list.fullordering doesn't match expected 'name ASC'");
        $this->assertEquals($expected2, $actual2, "list.direction doesn't match expected 'name'");
        $this->assertEquals($expected3, $actual3, "list.direction doesn't match expected 'ASC'");
        $this->assertEquals($expected4, $actual4, "list.limit doesn't match expected '20'");
        $this->assertEquals($expected5, $actual5, "list.start doesn't match expected '0'");
    }

}