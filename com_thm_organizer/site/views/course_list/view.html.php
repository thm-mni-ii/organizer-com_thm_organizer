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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/prep_course.php';
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
	public $lang;

	public $items;

	public $item = array();

	public $languageSwitches;

	public $authorized;

	public $loggedIn;

	public $authValues;

	public $oneAuth;

	public $shortTag;

	public $filters = array();

	public $state = null;

	public $model = null;
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
		$this->setFilters();

		$this->lang = THM_OrganizerHelperLanguage::getLanguage();
		$this->items = $this->get('Items');

		$params = array('view' => 'course_list');
		$this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($params);
		$this->shortTag = JFactory::getApplication()->input->get('languageTag', 'de');

		$user = JFactory::getUser();
		$this->authorized = $user->authorise('core.admin');

		$this->loggedIn = !empty($user->id);

		$this->authValues = array_map(
			function($elem)
			{
				return THM_OrganizerHelperPrep_Course::authSubjectTeacher($elem->subjectID);
			}, $this->items
		);

		$this->oneAuth = array_reduce(
			$this->authValues,
			function($acc, $value)
			{
				return ($acc OR $value);
			},
			$this->authorized
		);

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
		JHtml::_('behavior.framework', true);

		JFactory::getDocument()->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/prep_course.css');
	}

	/**
	 * Sets various filter elements
	 *
	 * @return  void  sets the filter object variables
	 */
	private function setFilters()
	{
		$helper = 'THM_OrganizerHelperComponent';
		$lang = THM_OrganizerHelperLanguage::getLanguage();

		$activeOptions = array(
			"0"  => $lang->_('COM_THM_ORGANIZER_FILTER_CURRENT') . " " . $lang->_('COM_THM_ORGANIZER_PREP_COURSES'),
			"1" => $lang->_('JALL') . " " . $lang->_('COM_THM_ORGANIZER_PREP_COURSES'),
			"2" => $lang->_('COM_THM_ORGANIZER_FILTER_EXPIRED') . " " . $lang->_('COM_THM_ORGANIZER_PREP_COURSES'));
		$this->filters['filter_active'] = $helper::selectBox($activeOptions, 'filter_active', null, $this->state->filter_active);

		$subjectOptions = THM_OrganizerHelperPrep_Course::prepCourseList();
		$default = array(0 => $lang->_("JALL"));
		$this->filters['filter_subject'] = $helper::selectBox($subjectOptions, 'filter_subject', null, $this->state->filter_subject, $default);
	}
}