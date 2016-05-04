<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Class for the back-end control panel screen.
 *
 */
class THMOrganizerPage extends AdminManagerPage
{
	protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_thm_organizer&view=thm_organizer']";
	protected $url = 'administrator/index.php?option=com_thm_organizer&view=thm_organizer';

	public $filters = array();

	public $toolbar = array ();

	public $submenu = array (
		'option=com_thm_organizer&view=thm_organizer',
        'option=com_thm_organizer&view=degree_manager',
        'option=com_thm_organizer&view=user_manager',
        'option=com_thm_organizer&view=monitor_manager',
        'option=com_thm_organizer&view=teacher_manager',
        'option=com_thm_organizer&view=subject_manager',
        'option=com_thm_organizer&view=field_manager',
        'option=com_thm_organizer&view=color_manager',
        'option=com_thm_organizer&view=pool_manager',
        'option=com_thm_organizer&view=program_manager',
        'option=com_thm_organizer&view=schedule_manager',
        'option=com_thm_organizer&view=category_manager'
	);
}