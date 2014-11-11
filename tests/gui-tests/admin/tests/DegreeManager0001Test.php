<?php
/**
 * @package    THM_Organizer.GuiTest
 * @author     Wolf Rost <Wolf.Rost@mni.thm.de>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use SeleniumClient\By;

/**
 * This class tests the degreemanager view
 *
 * @package com_thm_organizer
 */
class DegreeManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     degreeManagerPage
	 */
	protected $degreeManagerPage = null;

	/**
	 * Login and click the DegreeManager button
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->degreeManagerPage = $cpPage->clickMenuByUrl('com_thm_organizer&view=degree_manager', 'DegreeManagerPage');
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
    public function openEditScreen_DegreeEditOpened()
    {
        // Check if all expected toolbar items are present
        $toolbarItems = $this->degreeManagerPage->toolbar;
        foreach($toolbarItems as $value)
        {
            $this->assertNotNull($this->driver->findElement(By::xPath('//*[@id="' . $value . '"]')));
        }

        // Click the new button
        $this->degreeManagerPage->clickButton('toolbar-new');


        // Click the close button in the degree edit view
        $degreeEditPage = $this->getPageObject('DegreeEditPage');
        $degreeEditPage->clickButton('toolbar-cancel');
        $this->degreeManagerPage = $this->getPageObject('DegreeManagerPage');
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
        $this->assertFalse($this->degreeManagerPage->getRowNumber($degreeName), 'Test degree should not be present');
        $this->degreeManagerPage->addDegree($degreeName, $abbreviation, $LSF);
        $message = $this->degreeManagerPage->getAlertMessage();
        $this->assertContains('The resources have been saved successfully.', $message, 'Degree save should return success', true);

        $this->assertTrue($this->degreeManagerPage->getRowText($degreeName) == $degreeName . " " . $abbreviation . " " . $LSF, 'Test degree should be on the page');

        $this->driver->refresh();

        $this->degreeManagerPage->deleteDegree($degreeName);

        $this->driver->waitForElementUntilIsPresent(By::xPath("//div[@class='alert alert-success']"));

        $this->assertFalse($this->degreeManagerPage->getRowNumber($degreeName), 'Test degree should not be present');
    }
}