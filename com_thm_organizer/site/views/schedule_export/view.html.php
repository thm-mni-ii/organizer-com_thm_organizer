<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        thm_organizerViewSchedule_Export
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

define('K_PATH_IMAGES', JPATH_ROOT . '/media/com_thm_organizer/images/');
jimport('tcpdf.tcpdf');

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/schedule.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * View class for the display of schedules
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewSchedule_Export extends JViewLegacy
{
	public $fields = array();

	public $date;

	public $timePeriods;

	public $planningPeriods;

	public $scheduleFormat;

	public $departments;

	public $programs;

	public $pools;

	public $teachers;

	public $rooms;


	/**
	 * Method to get extra
	 *
	 * @param string $tpl template
	 *
	 * @return  mixed  false on error, otherwise void
	 */
	public function display($tpl = null)
	{
		$libraryInstalled = $this->checkLibraries();

		if (!$libraryInstalled)
		{
			return false;
		}

		$this->modifyDocument();

		$this->lang = THM_OrganizerHelperLanguage::getLanguage();

		$this->model = $this->getModel();

		$this->setResourceFields();
		$this->setFilterFields();
		$this->setFormatFields();

		parent::display($tpl);

	}

	/**
	 * Imports libraries and sets library variables
	 *
	 * @return  void
	 */
	private function checkLibraries()
	{
		$this->compiler = jimport('tcpdf.tcpdf');

		if (!$this->compiler)
		{
			JError::raiseWarning('COM_THM_ORGANIZER_MESSAGE_TCPDF_LIBRARY_NOT_INSTALLED');

			return false;
		}

		return true;
	}

	/**
	 * Modifies document variables and adds links to external files
	 *
	 * @return  void
	 */
	private function modifyDocument()
	{
		JHtml::_('bootstrap.framework');
		JHtml::_('bootstrap.tooltip');
		JHtml::_('jquery.ui');
		JHtml::_('behavior.calendar');
		JHtml::_('formbehavior.chosen', 'select');

		$document = JFactory::getDocument();
		$document->addScript(JUri::root() . '/media/com_thm_organizer/js/schedule_export.js');
		$document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/schedule_export.css');
	}

	/**
	 * Creates resource selection fields for the form
	 *
	 * @return void sets indexes in $this->fields['resouceSettings'] with html content
	 */
	private function setFilterFields()
	{
		$this->fields['filterFields'] = array();
		$attribs = array('multiple' => 'multiple');

		// Departments
		$deptAttribs = $attribs;
		$deptAttribs['onChange'] = 'repopulatePrograms();repopulateResources();';
		$deptAttribs['data-placeholder'] = JText::_('COM_THM_ORGANIZER_DEPARTMENT_SELECT_PLACEHOLDER');
		$planDepartmentOptions = $this->model->getDepartmentOptions();
		$departmentSelect = JHtml::_('select.genericlist', $planDepartmentOptions, 'departmentIDs[]', $deptAttribs, 'value', 'text');
		$this->fields['filterFields']['departmetIDs'] = array(
			'label' => JText::_('COM_THM_ORGANIZER_DEPARTMENTS'),
			'description' => JText::_('COM_THM_ORGANIZER_DEPARTMENTS_EXPORT_DESC'),
			'input' => $departmentSelect
		);

		// Programs
		$programAttribs = $attribs;
		$programAttribs['onChange'] = 'repopulateResources();';
		$programAttribs['data-placeholder'] = JText::_('COM_THM_ORGANIZER_PROGRAM_SELECT_PLACEHOLDER');
		$planProgramOptions = $this->model->getProgramOptions();
		$programSelect = JHtml::_('select.genericlist', $planProgramOptions, 'programIDs[]', $programAttribs, 'value', 'text');
		$this->fields['filterFields']['programIDs'] = array(
			'label' => JText::_('COM_THM_ORGANIZER_PROGRAMS'),
			'description' => JText::_('COM_THM_ORGANIZER_PROGRAMS_EXPORT_DESC'),
			'input' => $programSelect
		);
	}

	/**
	 * Creates format settings fields for the form
	 *
	 * @return void sets indexes in $this->fields['formatSettings'] with html content
	 */
	private function setFormatFields()
	{
		$this->fields['formatSettings'] = array();
		$attribs = array();

		$formatAttribs = $attribs;
		$formatAttribs['onChange'] = 'setFormat();';
		$fileFormats = array();
		$fileFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_ICS_CALENDAR'), 'value' => 'ics');
		$fileFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_PDF_A3_DOCUMENT'), 'value' => 'pdf.a3');
		$fileFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_PDF_A4_DOCUMENT'), 'value' => 'pdf.a4');
		//$fileFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_XLS_SPREADSHEET'), 'value' => 'xls');
		$defaultFileFormat = 'pdf.a4';
		$fileFormatSelect = JHtml::_('select.genericlist', $fileFormats, 'format', $attribs, 'value', 'text', $defaultFileFormat);
		$this->fields['formatSettings']['fileFormat'] = array(
			'label' => JText::_('COM_THM_ORGANIZER_FILE_FORMAT'),
			'description' => JText::_('COM_THM_ORGANIZER_FILE_FORMAT_DESC'),
			'input' => $fileFormatSelect
		);

		$displayFormats = array();
		//$displayFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_LIST'), 'value' => 'list');
		$displayFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_SCHEDULE'), 'value' => 'schedule');
		$defaultDisplayFormat = 'timeTable';
		$displayFormatSelect = JHtml::_('select.genericlist', $displayFormats, 'displayFormat', $attribs, 'value', 'text', $defaultDisplayFormat);
		$this->fields['formatSettings']['displayFormats'] = array(
			'label' => JText::_('COM_THM_ORGANIZER_DISPLAY_FORMAT'),
			'description' => JText::_('COM_THM_ORGANIZER_DISPLAY_FORMAT_DESC'),
			'input' => $displayFormatSelect
		);

		$dateValue = date('Y-m-d');

		// TODO: Deal with this % bullshit somehow.
		$dateFormat = '%d.%m.%Y';
		$dateSelect = JHtml::_('calendar', $dateValue, 'date', 'date', $dateFormat, $attribs);
		$this->fields['formatSettings']['date'] = array(
			'label' => JText::_('JDATE'),
			'description' => JText::_('COM_THM_ORGANIZER_DATE_DESC'),
			'input' => $dateSelect
		);

		$dateRestrictions = array();
		//$dateRestrictions[] = array('text' => JText::_('COM_THM_ORGANIZER_DAY_PLAN'), 'value' => 'day');
		$dateRestrictions[] = array('text' => JText::_('COM_THM_ORGANIZER_WEEK_PLAN'), 'value' => 'week');
		//$dateRestrictions[] = array('text' => JText::_('COM_THM_ORGANIZER_MONTH_PLAN'), 'value' => 'month');
		//$dateRestrictions[] = array('text' => JText::_('COM_THM_ORGANIZER_SEMESTER_PLAN'), 'value' => 'semester');
		//$dateRestrictions[] = array('text' => JText::_('COM_THM_ORGANIZER_CUSTOM_PLAN'), 'value' => 'custom');
		$defaultDateRestriction = 'week';
		$dateRestrictionSelect = JHtml::_('select.genericlist', $dateRestrictions, 'dateRestriction', $attribs, 'value', 'text', $defaultDateRestriction);
		$this->fields['formatSettings']['dateRestriction'] = array(
			'label' => JText::_('COM_THM_ORGANIZER_DATE_RESTRICTION'),
			'description' => JText::_('COM_THM_ORGANIZER_DATE_RESTRICTION_DESC'),
			'input' => $dateRestrictionSelect
		);

		// TODO: Add grid selection here

		$scheduleFormats = array();
		$scheduleFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_STACKED_PLANS'), 'value' => 'stack');
		$scheduleFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_SEQUENCED_PLANS'), 'value' => 'sequence');
		$defaultScheduleFormat = 'sequence';
		$scheduleFormatSelect = JHtml::_('select.genericlist', $scheduleFormats, 'scheduleFormat', $attribs, 'value', 'text', $defaultScheduleFormat);
		/*$this->fields['formatSettings']['scheduleFormat'] = array(
			'label' => JText::_('COM_THM_ORGANIZER_SCHEDULE_FORMAT'),
			'description' => JText::_('COM_THM_ORGANIZER_SCHEDULE_FORMAT_DESC'),
			'input' => $scheduleFormatSelect
		);*/
	}

	/**
	 * Creates resource selection fields for the form
	 *
	 * @return void sets indexes in $this->fields['resouceSettings'] with html content
	 */
	private function setResourceFields()
	{
		$this->fields['resourceFields'] = array();
		$attribs = array('multiple' => 'multiple');

		// Pools
		$poolAttribs = $attribs;
		$poolAttribs['data-placeholder'] = JText::_('COM_THM_ORGANIZER_POOL_SELECT_PLACEHOLDER');
		$planPoolOptions = $this->model->getPoolOptions();
		$poolSelect = JHtml::_('select.genericlist', $planPoolOptions, 'poolIDs[]', $poolAttribs, 'value', 'text');
		$this->fields['resourceFields']['poolIDs'] = array(
			'label' => JText::_('COM_THM_ORGANIZER_POOLS'),
			'description' => JText::_('COM_THM_ORGANIZER_POOLS_EXPORT_DESC'),
			'input' => $poolSelect
		);

		// Teachers
		$teacherAttribs = $attribs;
		$teacherAttribs['data-placeholder'] = JText::_('COM_THM_ORGANIZER_TEACHER_SELECT_PLACEHOLDER');
		$planTeacherOptions = $this->model->getTeacherOptions();
		$teacherSelect = JHtml::_('select.genericlist', $planTeacherOptions, 'teacherIDs[]', $teacherAttribs, 'value', 'text');
		$this->fields['resourceFields']['teacherIDs'] = array(
			'label' => JText::_('COM_THM_ORGANIZER_TEACHERS'),
			'description' => JText::_('COM_THM_ORGANIZER_TEACHERS_EXPORT_DESC'),
			'input' => $teacherSelect
		);

		// Rooms
		$roomAttribs = $attribs;
		$roomAttribs['data-placeholder'] = JText::_('COM_THM_ORGANIZER_ROOM_SELECT_PLACEHOLDER');
		$planRoomOptions = $this->model->getRoomOptions();
		$roomSelect = JHtml::_('select.genericlist', $planRoomOptions, 'roomIDs[]', $roomAttribs, 'value', 'text');
		$this->fields['resourceFields']['roomIDs'] = array(
			'label' => JText::_('COM_THM_ORGANIZER_ROOMS'),
			'description' => JText::_('COM_THM_ORGANIZER_ROOMS_EXPORT_DESC'),
			'input' => $roomSelect
		);
	}
}
