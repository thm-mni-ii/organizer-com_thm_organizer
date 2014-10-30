<?php
/**
 * @package    THM_Organizer.UnitTest
 * @author     Andrej Sajenko <Andrej.Sajenko@mni.thm.de>
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Include the SUT class
require_once JPATH_ADMINISTRATOR . '/components/com_thm_organizer/models/category_edit.php';

/**
 * Class THM_OrganizerModelCategoryAdminTest
 *
 * @package             com_thm_organizer
 * @coversDefaultClass  THM_OrganizerModelCategory
 *
 * @requires            extension sqlite3
 */
class THM_OrganizerModelCategoryEditAdminTest extends TestCaseDatabase
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
        parent::setUp();
        $this->saveFactoryState();

        JFactory::$application = $this->getMockCmsApp();
        JTable::addIncludePath(JPATH_COMPONENT . '/tables');
        $this->object = new THM_OrganizerModelCategory_Edit;
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
        $dataSet->addTable('jos_thm_organizer_categories', JPATH_TEST_DATABASE . '/jos_thm_organizer_categories.csv');
        return $dataSet;
    }


    public function testSuccessfulGetForm()
    {
        $option = 'com_thm_organizer';
        $view = 'category_edit';

        JFactory::getApplication()->input->set('option', $option);
        JFactory::getApplication()->input->set('view', $view);

        $form = $this->object->getForm();

        $this->assertFalse($form === false);
    }

    public function testGetTable()
    {
        $option = 'com_thm_organizer';
        $view = 'category_edit';

        JFactory::getApplication()->input->set('option', $option);
        JFactory::getApplication()->input->set('view', $view);

        $table = $this->object->getTable();

        $this->assertFalse($table === false);
    }


}