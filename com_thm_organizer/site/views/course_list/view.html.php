<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/course.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Class which loads data into the view output context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewCourse_List extends JViewLegacy
{
	public $filters = [];

	public $items;

	public $lang;

	public $languageSwitches;

	public $model = null;

	public $shortTag;

	public $showFilters;

	public $state = null;

	/**
	 * Method to get display
	 *
	 * @param Object $tpl template  (default: null)
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->modifyDocument();
		$this->model = $this->getModel();
		$this->state = $this->model->getState();

		$this->lang  = THM_OrganizerHelperLanguage::getLanguage();
		$this->items = $this->get('Items');

		$params                 = ['view' => 'course_list'];
		$this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($params);
		$this->shortTag         = THM_OrganizerHelperLanguage::getShortTag();

		$this->setFilters();

		foreach ($this->items AS &$item)
		{
			$item->admin = THM_OrganizerHelperCourse::isCourseAdmin($item->subjectID, 'subject');
		}

		parent::display($tpl);
	}

	/**
	 * Adds resource files to the document
	 *
	 * @return  void
	 */
	private function modifyDocument()
	{
		JHtml::_('bootstrap.tooltip');
		JHTML::_('behavior.modal');

		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/course_list.css');
	}

	/**
	 * Sets various filter elements
	 *
	 * @return  void  sets the filter object variables
	 */
	private function setFilters()
	{
		$lang    = THM_OrganizerHelperLanguage::getLanguage();
		$attribs = ['onchange' => 'form.submit();'];

		if (THM_OrganizerHelperCourse::isCourseAdmin())
		{
			$activeOptions = [
				"pending" => $lang->_('COM_THM_ORGANIZER_PENDING_COURSES'),
				"current" => $lang->_('COM_THM_ORGANIZER_CURRENT_COURSES'),
				"all"     => $lang->_('COM_THM_ORGANIZER_ALL_COURSES'),
				"expired" => $lang->_('COM_THM_ORGANIZER_EXPIRED_COURSES')
			];

			$this->filters['filter_status']
				= THM_OrganizerHelperComponent::selectBox($activeOptions, 'filter_status', $attribs, $this->state->filter_status);
		}

		$subjectOptions = THM_OrganizerHelperCourse::prepCourseList();
		$default        = [0 => $lang->_("COM_THM_ORGANIZER_ALL_COURSES")];

		$this->filters['filter_subject']
			= THM_OrganizerHelperComponent::selectBox($subjectOptions, 'filter_subject', $attribs, $this->state->filter_subject, $default);

	}
}