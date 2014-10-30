<?php
/**
 * @package    THM_Organizer.UnitTest
 * @author     Andrej Sajenko <Andrej.Sajenko@mni.thm.de>
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Include the SUT class
require_once JPATH_ADMINISTRATOR . '/components/com_thm_organizer/models/field.php';

/**
 * Class THM_OrganizerModelCategoryAdminTest
 *
 * @package             com_thm_organizer
 * @coversDefaultClass  THM_OrganizerModelCategory
 *
 * @requires            extension sqlite3
 */
class THM_OrganizerModelFieldAdminTest extends TestCaseDatabase
{
    /**
     * @var THM_OrganizerModelCategory
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
        JTable::addIncludePath(JPATH_COMPONENT . '/tables');
        $this->object = new THM_OrganizerModelField;
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
        //$dataSet->addTable('jos_thm_organizer_fields', JPATH_TEST_DATABASE . '/jos_thm_organizer_fields.csv');
        return $dataSet;
    }

    public function testSuccessfulSave()
    {
        $dbo = JFactory::getDbo();

        /* Test Data & References */
        $fieldName = "Informatik";
        $gpuntisID = 2;
        $colorID = 1;

        $option = 'com_thm_organizer';
        $task = 'field.save';
        $jForm = array(
            'field' => $fieldName,
            'gpuntisID' => $gpuntisID,
            'colorID' => $colorID,
        );

        JFactory::getApplication()->input->set('option', $option);
        JFactory::getApplication()->input->set('task', $task);
        JFactory::getApplication()->input->set('jform', $jForm);

        $id = $this->object->save();

        $this->assertTrue($id !== false); /*Save return false (boolean) on fail, else a id (int)*/

        $query = $dbo->getQuery(true);
        $query
            ->select('f.id, f.field, f.gpuntisID, f.colorID')
            ->from('#__thm_organizer_fields as f')
            ->where("f.id = '$id'");
        $objs = $dbo->setQuery($query)->loadObjectList();

        $this->assertTrue(count($objs) == 1); /*Found exactly one*/

        $obj = $objs[0];

        $this->assertEquals($jForm['field'], $obj->field);
        $this->assertEquals($jForm['gpuntisID'], $obj->gpuntisID);
        $this->assertEquals($jForm['colorID'], $obj->colorID);
    }
}
