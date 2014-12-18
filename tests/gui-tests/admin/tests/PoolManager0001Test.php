<?php
/**
 * @package    THM_Organizer.GuiTest
 * @author     Wolf Rost <Wolf.Rost@mni.thm.de>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use SeleniumClient\By;

/**
 * This class tests the pool manager view
 *
 * @package com_thm_organizer
 */
class PoolManager0001Test extends iCampusWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     managerPage
	 */
	protected $managerPage = null;

	/**
	 * Login and click the DegreeManager button
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->managerPage = $cpPage->clickMenuByUrl('com_thm_organizer&view=pool_manager', 'PoolManagerPage');
	}

	/**
	 * Logout and close test.
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

    /**
     * Test to open the degree edit view from the degree manager view and check whether the expected elements are present
     *
     * @test
     */
    public function poolViewSmokeTest()
    {
        $this->generalSmokeTest("PoolManagerPage", "PoolEditPage");
    }
}