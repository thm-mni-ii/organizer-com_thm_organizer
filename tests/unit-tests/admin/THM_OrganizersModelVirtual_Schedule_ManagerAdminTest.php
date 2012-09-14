<?php
/**
 * @version     v0.0.1
 * @category 	Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.test.admin
 * @name        THM_OrganizersModelVirtual_Schedule_ManagerAdminTest
 * @description THM_OrganizersModelVirtual_Schedule_ManagerAdminTest from com_thm_organizer
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

require_once JPATH_BASE . '/administrator/components/com_thm_organizer/models/virtual_schedule_manager.php';

/**
 * THM_OrganizersModelVirtual_Schedule_ManagerAdminTest class for admin component com_thm_organizer
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.test.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class THM_OrganizersModelVirtual_Schedule_ManagerAdminTest extends PHPUnit_Framework_TestCase
{
	protected $instance;

	/**
	 * set up function before tests
	 *
	 * @return void
	 */
	public function setUp()
	{
		if (!defined('JPATH_COMPONENT')) {
			define('JPATH_COMPONENT', dirname(__FILE__));
		}
				
		$this->instance = new THM_OrganizersModelVirtual_Schedule_Manager;
	}

	/**
	 * kill function after tests
	 *
	 * @return void
	 */
	public function tearDown()
	{
		$this->instance = null;
		parent::tearDown();
	}

	/**
	 * tests getForm,
	 * inserts an value in database
	 * function should update value
	 * but picFile is null, function return false
	 *
	 * @return void
	 */
	public function testgetData()
	{
		$expected = array();
		$actual = $this->instance->getData();
		
		$this->assertEquals($expected, $actual);
	}
}
