<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        thm_organizerViewOrganizer
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
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
	 * The time period in days in which removed lessons should get displayed.
	 *
	 * @var string
	 */
	protected $deltaDays;

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
	 * Model to this view
	 *
	 * @var THM_OrganizerModelSchedule
	 */
	protected $model;

	/**
	 * Method to display the template
	 *
	 * @param null $tpl template
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		$this->isMobile    = THM_OrganizerHelperComponent::isSmartphone();
		$this->languageTag = THM_OrganizerHelperLanguage::getShortTag();
		$this->model       = $this->getModel();
		$this->defaultGrid = $this->model->getDefaultGrid();
		$compParams        = JComponentHelper::getParams('com_thm_organizer');
		$this->dateFormat  = $compParams->get('dateFormat', 'd.m.Y');
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

		JHtml::_('formbehavior.chosen', 'select');
		$this->addScriptOptions();
		$doc->addScript(JUri::root() . "media/com_thm_organizer/js/schedule.js");

		$doc->addStyleSheet(JUri::root() . "media/com_thm_organizer/fonts/iconfont-frontend.css");
		$doc->addStyleSheet(JUri::root() . "media/com_thm_organizer/css/schedule.css");
		$doc->addStyleSheet(JUri::root() . "media/jui/css/icomoon.css");
	}

	/**
	 * Generates required params for Javascript and adds them to the document
	 *
	 * @return  void
	 */
	private function addScriptOptions()
	{
		$user = JFactory::getUser();
		$root = JUri::root();

		$variables = [
			'SEMESTER_MODE'     => 1,
			'PERIOD_MODE'       => 2,
			'INSTANCE_MODE'     => 3,
			'ajaxBase'          => $root . 'index.php?option=com_thm_organizer&view=schedule_ajax&format=raw',
			'auth'              => !empty($user->id) ?
				urlencode(password_hash($user->email . $user->registerDate, PASSWORD_BCRYPT))
				: '',
			'dateFormat'        => $this->dateFormat,
			'defaultGrid'       => $this->defaultGrid->grid,
			'deltaDays'         => $this->model->params['deltaDays'],
			'departmentID'      => $this->model->params['departmentID'],
			'exportBase'        => $root . 'index.php?option=com_thm_organizer&view=schedule_export',
			'isMobile'          => $this->isMobile,
			'menuID'            => JFactory::getApplication()->input->get('Itemid', 0),
			'registered'        => !empty($user->id),
			'showPools'         => $this->model->params['showPools'],
			'showPrograms'      => $this->model->params['showPrograms'],
			'showRooms'         => $this->model->params['showRooms'],
			'showRoomTypes'     => $this->model->params['showRoomTypes'],
			'showSubjects'      => $this->model->params['showSubjects'],
			'showTeachers'      => $this->model->params['showTeachers'],
			'subjectDetailBase' => $root . 'index.php?option=com_thm_organizer&view=subject_details&id=1',
			'username'          => !empty($user->id) ? $user->username : ''
		];

		if (!empty($this->model->params['showUnpublished']))
		{
			$variables['showUnpublished'] = $this->model->params['showUnpublished'];
		}

		$grids = [];
		foreach ($this->model->grids AS $grid)
		{
			$grids[$grid->id] = [
				"id"   => $grid->id,
				"grid" => $grid->grid
			];
		}
		$variables['grids'] = $grids;

		if (!empty($this->model->params['displayName']))
		{
			$variables['displayName'] = $this->model->params['displayName'];
		}
		if (!empty($this->model->params['poolIDs']))
		{
			$variables['poolIDs'] = $this->model->params['poolIDs'];
		}
		if (!empty($this->model->params['programIDs']))
		{
			$variables['programIDs'] = $this->model->params['programIDs'];
		}
		if (!empty($this->model->params['roomIDs']))
		{
			$variables['roomIDs'] = $this->model->params['roomIDs'];
		}
		if (!empty($this->model->params['roomTypeIDs']))
		{
			$variables['roomTypeIDs'] = $this->model->params['roomTypeIDs'];
		}
		if (!empty($this->model->params['subjectIDs']))
		{
			$variables['subjectIDs'] = $this->model->params['subjectIDs'];
		}
		if (!empty($this->model->params['teacherIDs']))
		{
			$variables['teacherIDs'] = $this->model->params['teacherIDs'];
		}

		$text = [
			'APRIL'                 => JText::_('APRIL'),
			'AUGUST'                => JText::_('AUGUST'),
			'COPY'                  => JText::_('COM_THM_ORGANIZER_ACTION_GENERATE_LINK'),
			'DECEMBER'              => JText::_('DECEMBER'),
			'FEBRUARY'              => JText::_('FEBRUARY'),
			'FRIDAY_SHORT'          => JText::_('FRI'),
			'JANUARY'               => JText::_('JANUARY'),
			'JULY'                  => JText::_('JULY'),
			'JUNE'                  => JText::_('JUNE'),
			'LUNCHTIME'             => JText::_('COM_THM_ORGANIZER_LUNCHTIME'),
			'MARCH'                 => JText::_('MARCH'),
			'MAY'                   => JText::_('MAY'),
			'MONDAY_SHORT'          => JText::_('MON'),
			'MY_SCHEDULE'           => JText::_('COM_THM_ORGANIZER_MY_SCHEDULE'),
			'NOVEMBER'              => JText::_('NOVEMBER'),
			'OCTOBER'               => JText::_('OCTOBER'),
			'POOL_PLACEHOLDER'      => JText::_('COM_THM_ORGANIZER_POOL_SELECT_PLACEHOLDER'),
			'PROGRAM_PLACEHOLDER'   => JText::_('COM_THM_ORGANIZER_PROGRAM_SELECT_PLACEHOLDER'),
			'ROOM_PLACEHOLDER'      => JText::_('COM_THM_ORGANIZER_ROOM_SELECT_PLACEHOLDER'),
			'ROOM_TYPE_PLACEHOLDER' => JText::_('COM_THM_ORGANIZER_ROOM_TYPE_SELECT_PLACEHOLDER'),
			'SATURDAY_SHORT'        => JText::_('SAT'),
			'SEPTEMBER'             => JText::_('SEPTEMBER'),
			'SUNDAY_SHORT'          => JText::_('SUN'),
			'TEACHER_PLACEHOLDER'   => JText::_('COM_THM_ORGANIZER_TEACHER_SELECT_PLACEHOLDER'),
			'THURSDAY_SHORT'        => JText::_('THU'),
			'TIME'                  => JText::_('COM_THM_ORGANIZER_TIME'),
			'TUESDAY_SHORT'         => JText::_('TUE'),
			'WEDNESDAY_SHORT'       => JText::_('WED')
		];

		$doc = JFactory::getDocument();
		$doc->addScriptOptions('variables', $variables);
		$doc->addScriptOptions('text', $text);
	}
}