<?php
/**
 * @package    THM_Organizer.GuiTest
 * @author     Wolf Rost <Wolf.Rost@mni.thm.de>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use SeleniumClient\By;

/**
 * This class tests the degree manager view
 *
 * @package com_thm_organizer
 */
class DegreeManager0001Test extends iCampusWebdriverTestCase
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
		$this->managerPage = $cpPage->clickMenuByUrl('com_thm_organizer&view=degree_manager', 'DegreeManagerPage');
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
    public function degreeViewSmokeTest()
    {
        $this->generalSmokeTest("DegreeManagerPage", "DegreeEditPage");
    }

    /**
    * Creates a new degree and delete it afterwards
    *
    * @test
    */
    public function addMenu_WithGivenFields_MenuAdded()
    {
        $salt = rand(111111, 999999);
        $degreeName = 'Abschluss' . $salt;
        $abbreviation = 'ab' . $salt;
        $LSF = 'AB' . $salt;
        $this->assertFalse($this->managerPage->getRowNumber($degreeName), 'Test degree should not be present');
        $this->managerPage->addDegree($degreeName, $abbreviation, $LSF);
        $message = $this->managerPage->getAlertMessage();
        $this->assertContains('The resources have been saved successfully.', $message, 'Degree save should return success', true);

        $this->assertTrue($this->managerPage->getRowText($degreeName) == $degreeName . " " . $abbreviation . " " . $LSF, 'Test degree should be on the page');

        $this->driver->refresh();

        $this->managerPage->deleteDegree($degreeName);

        $this->driver->waitForElementUntilIsPresent(By::xPath("//div[@class='alert alert-success']"));

        $this->assertFalse($this->managerPage->getRowNumber($degreeName), 'Test degree should not be present');
    }
}