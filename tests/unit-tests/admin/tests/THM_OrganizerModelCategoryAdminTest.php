<?php
/**
 * @package    THM_Organizer.UnitTest
 * @author     Andrej Sajenko <Andrej.Sajenko@mni.thm.de>
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Include the SUT class
require_once JPATH_ADMINISTRATOR . '/components/com_thm_organizer/models/category.php';

/**
 * Class THM_OrganizerModelCategoryAdminTest
 *
 * @package             com_thm_organizer
 * @coversDefaultClass  THM_OrganizerModelCategory
 *
 * @requires            extension sqlite3
 */
class THM_OrganizerModelCategoryAdminTest extends TestCaseDatabase
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
        $this->object = new THM_OrganizerModelcategory;
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


    public function testSuccessfulSave()
    {

        $dbo = JFactory::getDbo();

        $contentCatId = 50; // Dummy

        $option = 'com_thm_organizer';
        $task = 'category_apply';
        $jForm = array(
            'title' => 'UnitTestCategory',
            'description' => '<p>UnitTestDescription</p>',
            'contentCatID' => $contentCatId,
            'global' => '0',
            'reserves' => '0',
            'id' => ''
        );


        JFactory::getApplication()->input->set('option', $option);
        JFactory::getApplication()->input->set('task', $task);
        JFactory::getApplication()->input->set('jform', $jForm);

        $id = $this->object->save();

        $this->assertTrue($id !== false); /*Save return false (boolean) on fail, else a id (int)*/

        $query = $dbo->getQuery(true);
        $query
            ->select('c.id, c.title, c.description, c.global, c.reserves, c.contentCatID')
            ->from('#__thm_organizer_categories as c')
            ->where("c.id = '$id'");
        $objs = $dbo->setQuery($query)->loadObjectList();

        $this->assertTrue(count($objs) == 1); /*Found exactly one*/

        $obj = $objs[0];

        $this->assertEquals($jForm['title'], $obj->title);
        $this->assertEquals($jForm['contentCatID'], $obj->contentCatID);
        $this->assertEquals($jForm['global'], $obj->global);
        $this->assertEquals($jForm['reserves'], $obj->reserves);
    }

    public function testSuccessfulDelete()
    {
        /* Data from TEST Database
         * 'id','title','description','global','reserves','contentCatID'
         * '1','Ausfall','<p>Makiert Termine die einen Ausfall darstellen.</p>','0','0','76'
         */

        $option = 'com_thm_organizer';
        $idToDelete = 1;
        $idsToDelete = array(0 => $idToDelete); // Delete id 1
        $task = 'category_delete';

        JFactory::getApplication()->input->set('option', $option);
        JFactory::getApplication()->input->set('task', $task);

        // JFactory::getApplication()->input->set('cid', $idsToDelete); should be used after the remove of deprecated functions

        JRequest::setVar('cid', $idsToDelete, 'post');

        $deleted = $this->object->delete();

        $this->assertTrue($deleted);

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query
            ->select('c.*')
            ->from('#__thm_organizer_categories as c')
            ->where("c.id = $idToDelete");
        $objs = $dbo->setQuery($query)->loadObjectList();

        $this->assertEmpty($objs);
    }



}