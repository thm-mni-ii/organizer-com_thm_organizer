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
	 * default time grid, loaded first
	 *
	 * @var array
	 */
	protected $defaultGrid;

	/**
	 * the department for this schedule, chosen in menu options
	 *
	 * @var string
	 */
	protected $departmentID;

	/**
	 * time grids for displaying the schedules
	 *
	 * @var array
	 */
	protected $grids;

	/**
	 * mobile device or not
	 *
	 * @var boolean
	 */
	protected $isMobile = false;

	/**
	 * Contains the current languageTag
	 *
	 * @var    Object
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
		$this->languageTag  = JFactory::getLanguage()->getTag();
		$this->mySchedule   = $this->getModel()->getMySchedule();
		$params             = JFactory::getApplication()->getMenu()->getActive()->params;
		$this->departmentID = $params->get('departmentID');
		$this->grids        = $this->getModel()->getGrids();

		$defaultGrids = array_filter(
			$this->grids,
			function($var)
			{
				return $var->defaultGrid;
			}
		);
		$this->defaultGrid = json_decode($defaultGrids[0]->grid);

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
