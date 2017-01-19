<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        thm_organizerViewOrganizer
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * View class for the display of schedules
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewSchedule extends JViewLegacy
{
	/**
	 * format for displaying dates
	 *
	 * @var string
	 */
	protected $dateFormat;

	/**
	 * default time grid, loaded first
	 *
	 * @var object
	 */
	protected $defaultGrid;

	/**
	 * the department for this schedule, chosen in menu options
	 *
	 * @var string
	 */
	protected $departmentID;

	/**
	 * specifies the start date of calendar selection
	 *
	 * @var string
	 */
	protected $startDate;

	/**
	 * specifies the end date of calendar selection
	 *
	 * @var string
	 */
	protected $endDate;

	/**
	 * mobile device or not
	 *
	 * @var boolean
	 */
	protected $isMobile = false;

	/**
	 * Contains the current languageTag
	 *
	 * @var string
	 */
	protected $languageTag = "de-DE";

	/**
	 * Method to display the template
	 *
	 * @param null $tpl template
	 *
	 * @return mixed
	 */
	public function display($tpl = null)
	{
		$this->isMobile     = THM_OrganizerHelperComponent::isSmartphone();
		$this->languageTag  = THM_OrganizerHelperLanguage::getShortTag();
		$this->defaultGrid  = $this->getModel()->getDefaultGrid();
		$compParams         = JComponentHelper::getParams('com_thm_organizer');
		$this->dateFormat   = $compParams->get('dateFormat', 'd.m.Y');
		$params             = JFactory::getApplication()->getMenu()->getActive()->params;
		$this->departmentID = $params->get('departmentID', '0');
		$this->startDate    = $params->get('startDate', '');
		$this->endDate      = $params->get('endDate', '');
		$this->modifyDocument();
		parent::display($tpl);
	}

	/**
	 * Adds resource files to the document
	 *
	 * @return  void
	 */
	private function modifyDocument()
	{
		$doc = JFactory::getDocument();

		$doc->addScript(JHtml::_('jQuery.framework'));
		$doc->addScript(JHtml::_('formbehavior.chosen', 'select'));
		$doc->addScript(JUri::root() . "media/com_thm_organizer/js/calendar.js");
		$doc->addScript(JUri::root() . "media/com_thm_organizer/js/schedule.js");

		$doc->addStyleSheet(JUri::root() . "libraries/thm_core/fonts/iconfont-frontend.css");
		$doc->addStyleSheet(JUri::root() . "media/com_thm_organizer/css/schedule.css");
		$doc->addStyleSheet(JUri::root() . "media/jui/css/icomoon.css");
	}
}
