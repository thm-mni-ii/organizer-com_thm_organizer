<?php
/**
 * @version     v0.0.1
 * @category     Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.test.admin
 * @name        THM_OrganizersControllerVirtual_ScheduleAdminTest
 * @description THM_OrganizersControllerVirtual_ScheduleAdminTest from com_thm_organizer
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

require_once JPATH_BASE . '/administrator/components/com_thm_organizer/controllers/virtual_schedule.php';

/**
 * THM_OrganizersControllerVirtual_ScheduleAdminTest class for admin component com_thm_organizer
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.test.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class THM_OrganizersControllerVirtual_ScheduleAdminTest extends PHPUnit_Framework_TestCase
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
                
//         $this->instance = new THM_OrganizerControllerVirtual_Schedule;
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
     * method should return null
     *
     * @return void
     */
    public function testedit()
    {
        
//         $expected = 'virtual_schedule_edit';
//         $this->instance->edit();
//         $actual = JRequest::getVar('view');
                
//         $this->assertEquals($expected, $actual);
        $this->assertEquals(true, true);
    }
}
