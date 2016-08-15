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
	protected $defaultGrid;

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
		$this->checkMobile();

		$this->languageTag = JFactory::getLanguage()->getTag();
		$this->uri         = JUri::getInstance()->toString();
		$this->defaultGrid = $this->getModel()->getDefaultGrid();
		$this->schedules   = $this->getModel()->getSchedules();

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
		$doc->addScript(JUri::root() . "media/com_thm_organizer/js/calendar.js");
		$doc->addScript(JUri::root() . "media/com_thm_organizer/js/schedule.js");
		$doc->addScript(JUri::root() . "media/com_thm_organizer/js/schedule_ajax.js");
		
		$doc->addStyleSheet(JUri::root() . "libraries/thm_core/fonts/iconfont-frontend.css");
		$doc->addStyleSheet(JUri::root() . "media/com_thm_organizer/css/schedule.css");
		$doc->addStyleSheet(JUri::root() . "media/jui/css/icomoon.css");
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
