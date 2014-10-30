<?php

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * @package     THM_Organizer.GuiTest
 * @subpackage  Page
 *
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Page class for Front End Consumption
 */
class SiteConsumptionPage extends SitePage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath =  "//form[@id='thm_organizer_statistic_form']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = 'index.php?option=com_thm_organizer&view=consumption';

    /**
     * Array of all the fields
     *
     * @var array
     * @since 3.2
     */
    public $inputFields = array (
        '//*[@id="thm_organizer_statistic_form"]',
        '//*[@id="activated"]'
    );

    /**
     * Returns all input fields
     *
     * @return array    Input fields
     */
    public function getInputFields()
    {
        return $this->inputFields;
    }
}
