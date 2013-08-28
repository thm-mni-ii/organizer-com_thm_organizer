<?php
/**
 * @version     v0.0.1
 * @category 	Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.test.site
 * @name        THM_OrganizerModelScheduleSiteTest
 * @description THM_OrganizerModelScheduleSiteTest from com_thm_organizer
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

require_once JPATH_BASE . '/components/com_thm_organizer/models/scheduler.php';

/**
 * THM_OrganizerModelScheduleSiteTest class for site component com_thm_organizer
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.test.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class THM_OrganizerModelScheduleSiteTest extends PHPUnit_Framework_TestCase
{
	protected $instance;

	/**
	 * Set up function before tests
	 *
	 * @return void
	 */
	public function setUp()
	{
		if (!defined('JPATH_COMPONENT'))
		{
			define('JPATH_COMPONENT', dirname(__FILE__));
		}
				
		$this->instance = new THM_OrganizerModelScheduler;
	}

	/**
	 * Kill function after tests
	 *
	 * @return void
	 */
	public function tearDown()
	{
		$this->instance = null;
		parent::tearDown();
	}

	/**
	 * Test for isComAvailable
	 * method should return true
	 *
	 * @return void
	 */
	public function testisComAvailableTrue()
	{
		$actual = $this->instance->isComAvailable("com_users");
				
		$this->assertTrue($actual);
	}

	/**
	 * Test for isComAvailable
	 * method should return false
	 *
	 * @return void
	 */
	public function testisComAvailableFalse()
	{
		$actual = $this->instance->isComAvailable("$$$$");
	
		$this->assertFalse($actual);
	}

	/**
	 * Test for getSessionID
	 * method should a sessionid
	 *
	 * @return void
	 *
	 */
	public function testgetSessionIDAsUser()
	{
		$excepted = "1234567890hiigq4gjkkth478h01234567890";
		$username = "MNIDummyUserForTests";
		
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		
		$columns = array('username', 'session_id', 'guest');
		$values = array($db->quote($username), $db->quote($excepted), 0);
		
		$query
		->insert($db->quoteName('#__session'))
		->columns($db->quoteName($columns))
		->values(implode(',', $values));
		
		$db->setQuery($query);
		try
		{
			// Execute the query in Joomla 2.5.
			$result = $db->query();
		}
		catch (Exception $e)
		{
			// Catch any database errors.
		}
		
		$user = JFactory::getUser();
		$user->username = $username;
				
		$actual = $this->instance->getSessionID($user);
		
		$this->assertEquals($excepted, $actual);
		
		$query = $db->getQuery(true);
		
		$query->delete($db->quoteName('#__session'));
		$query->where("WHERE username = '" . $username . "' ");
		
		$db->setQuery($query);
		
		try
		{
			$result = $db->query();
		}
		catch (Exception $e)
		{
			// Catch the error.
		}
	}

	/**
	 * Test for getSessionID
	 * method should return an empty string
	 *
	 * @return void
	 *
	 */
	public function testgetSessionIDAsGuest()
	{
		$user = JFactory::getUser();
		
		$actual = $this->instance->getSessionID($user);
		
		$this->assertEmpty($actual);
	}
}
