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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

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
	 * mobile device or not
	 *
	 * @var boolean
	 */
	protected $isMobile = false;

	/**
	 * time grids for displaying the schedules
	 *
	 * @var array
	 */
	protected $timeGrids;

	/**
	 * time grid for displaying the exam times
	 *
	 * @var array
	 */
	protected $examsTimeGrid;

	/**
	 * URL of this site
	 *
	 * @var string
	 */
	protected $uri;

	/**
	 * Contains the current languageTag
	 *
	 * @var    Object
	 */
	protected $languageTag = "de-DE";

	/**
	 * Method to display the template
	 *
	 * @param   null $tpl template
	 *
	 * @return mixed
	 */
	public function display($tpl = null)
	{
		$this->languageTag = JFactory::getLanguage()->getTag();

		$this->uri           = JUri::getInstance()->toString();
		$this->timeGrids     = $this->get('TimeGrids');
		$this->examsTimeGrid = $this->get('ExamsTimeFallback');
		$this->startDay      = $this->timeGrids['fallback']->start_day;
		$this->endDay        = $this->timeGrids['fallback']->end_day;
		$this->schedules     = $this->getModel()->getSchedules();
		$this->checkMobile();
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

		$scripts[] = JUri::root() . "media/com_thm_organizer/js/calendar.js";
		$scripts[] = JUri::root() . "media/com_thm_organizer/js/schedule.js";
		$scripts[] = JHtml::_('jQuery.framework');

		$docScripts = array_keys($doc->_scripts);
		foreach ($scripts as $script)
		{
			if (!in_array($script, $docScripts))
			{
				$doc->addScript($script);
			}
		}

		$styleSheets[] = JUri::root() . "libraries/thm_core/fonts/iconfont-frontend.css";
		$styleSheets[] = JUri::root() . "media/com_thm_organizer/css/schedule.css";
		$styleSheets[] = JUri::root() . "media/jui/css/icomoon.css";

		$docSheets = array_keys($doc->_styleSheets);
		foreach ($styleSheets as $sheet)
		{
			if (!in_array($sheet, $docSheets))
			{
				$doc->addStyleSheet($sheet);
			}
		}
	}

	/**
	 * Searches for the Mobile Detector and sets the 'tmpl' parameter with GET.
	 *
	 * @return  void
	 */
	private function checkMobile()
	{
		$app            = JFactory::getApplication();
		$this->isMobile = THM_OrganizerHelperComponent::isSmartphone();
		$isCompTempl    = $app->input->getString('tmpl', '') == "component";

		if ($this->isMobile AND !$isCompTempl)
		{
			$base  = JUri::root() . 'index.php?';
			$query = $app->input->server->get('QUERY_STRING', '', 'raw') . '&tmpl=component';
			$app->redirect($base . $query);
		}
	}
}
