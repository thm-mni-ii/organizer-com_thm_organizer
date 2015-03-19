<?php
/**
 * @package    THM_Organizer.UnitTest
 * @author     Andrej Sajenko <Andrej.Sajenko@mni.thm.de>
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Include the SUT class
require_once JPATH_ADMINISTRATOR . '/components/com_thm_organizer/models/color_edit.php';

/**
 * Class THM_OrganizerModelCategoryAdminTest
 *
 * @package             com_thm_organizer
 * @coversDefaultClass  THM_OrganizerModelColor_Edit
 *
 * @requires            extension sqlite3
 */
class THM_OrganizerModelColor_EditTest extends TestCaseDatabase
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
        
      	$this->object = new THM_OrganizerModelColor_Edit;
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
        
        return $dataSet;
    }
    
    /**
     * Insert a Table in the database
     *
     * @param   string  $table    The name of the table
     * @param   string  $columns  Names of the columns (seperated with ',')
     * @param   array	$values  Array elements = number of columens
     *
     */
    protected function InsertinTable($table, $columns, $values) {
    	$query = $this->_db->getQuery(true);
    	 
    	for($i = 0; $i<count($values); $i++){
    		$value[] = $this->_db->quote($values[$i]);
    	}
    	$query->insert($table)->columns($columns)->values(implode(',', $value));
    	$this->_db->setQuery($query);
    	$this->_db->query();
    }

    /**
     * Method to test the getForm function
     *
     * @covers ::getForm
     */
    public function testgetForm()
    {
        $this->markTestSkipped(
            'Assets fails the test.'
        );

    	// Mock the JSession object and mark the get-method to be manipulated
    	$sessionMock = $this->getMock("JSession", array("get"));
    	
    	$userMock = $this->getMock("JUser", array("authorise"));
    	
    	$userMock->expects($this->exactly(0))
    	->method('authorise')
    	->with('core.admin')
    	->will($this->returnValue(true));
    	
    	$reflector = new ReflectionProperty('JUser', '_authLevels');
    	$reflector->setAccessible(true);
    	$reflector->setValue($userMock, array(1,2,3,4,5,6,7,8,9,10));

    	// Set our JSession mock object in the JFactory
    	JFactory::$session = $sessionMock;
    	
    	$expected = "com_thm_organizer.color_edit";
     	
    	$actual = $this->object->getForm()->getName();
    	$this->assertEquals($expected, $actual);
    }
    
    /**
     * Method to test the getTable function
     *
     * @covers ::getTable
     */
    public function testgetTable()
    {
    	$expected = "#__thm_organizer_colors";
    	$actual = $this->object->getTable()->getTableName();
    	$this->assertEquals($expected, $actual);
    }
}
