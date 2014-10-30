<?php
/**
 * @package    THM_Organizer.GuiTest
 * @author     Wolf Rost <Wolf.Rost@mni.thm.de>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the consumption view
 *
 * @package com_thm_organizer
 */
class ConsumptionSiteView0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var SiteConsumptionPage
	 */
	protected $consumptionView = null;

	/**
	 * Opens the Joomla website
	 */
	public function setUp()
	{
		$cfg = new SeleniumConfig();
		parent::setUp();
		$this->driver->get($cfg->host . $cfg->path);
	}

	/**
	 * Close the browser
	 */
	public function tearDown()
	{
		parent::tearDown();
	}

    /**
     * @test
     */
    public function checkElementsPresent()
    {
        $cfg = new SeleniumConfig();
        $archivedArticlePath = 'index.php?option=com_thm_organizer&view=consumption';
        $url = $cfg->host . $cfg->path . $archivedArticlePath;
        $this->driver->get($url);
        $this->consumptionView = $this->getPageObject('SiteConsumptionPage');

        $inputFields = $this->consumptionView->getInputFields();

        foreach($inputFields as $xPath)
        {
            $this->assertNotNull($this->driver->findElement(By::xPath($xPath)));
        }
  }
}

