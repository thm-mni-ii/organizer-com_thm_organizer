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

		$menu       = JFactory::getApplication()->getMenu()->getActive();
		$this->lang = THM_OrganizerHelperLanguage::getLanguage($menu->params->get('initialLanguage', 'de'));

		$this->model = $this->getModel();

		$this->setResourceFields();
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
			JError::raiseWarning('COM_THM_ORGANIZER_MESSAGE_FPDF_LIBRARY_NOT_INSTALLED');

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

		$document = JFactory::getDocument();
		//$document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/schedule_export.css');
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

		$fileFormats = array();
		$fileFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_ICS_CALENDAR'), 'value' => 'ics');
		$fileFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_PDF_DOCUMENT'), 'value' => 'pdf');
		$fileFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_XLS_SPREADSHEET'), 'value' => 'xls');
		$defaultFileFormat = 'pdf';
		$fileFormatSelect = JHtml::_('select.genericlist', $fileFormats, 'fileFormat', $attribs, 'value', 'text', $defaultFileFormat);
		$fileFormatLabel = JText::_('COM_THM_ORGANIZER_FILE_FORMATS');
		$this->fields['formatSettings']['fileFormats'] = array('label' => $fileFormatLabel, 'input' => $fileFormatSelect);

		$documentFormats = array();
		$documentFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_A3_SHEET'), 'value' => 'A3');
		$documentFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_A4_SHEET'), 'value' => 'A4');
		$defaultDocumentFormat = 'A4';
		$documentFormatSelect = JHtml::_('select.genericlist', $documentFormats, 'documentFormat', $attribs, 'value', 'text', $defaultDocumentFormat);
		$documentFormatLabel = JText::_('COM_THM_ORGANIZER_DOCUMENT_FORMATS');
		$this->fields['formatSettings']['documentFormats'] = array('label' => $documentFormatLabel, 'input' => $documentFormatSelect);

		$displayFormats = array();
		$displayFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_LIST'), 'value' => 'list');
		$displayFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_TIME_TABLE'), 'value' => 'timeTable');
		$defaultDisplayFormat = 'timeTable';
		$displayFormatSelect = JHtml::_('select.genericlist', $displayFormats, 'displayFormat', $attribs, 'value', 'text', $defaultDisplayFormat);
		$displayFormatLabel = JText::_('COM_THM_ORGANIZER_DOCUMENT_FORMATS');
		$this->fields['formatSettings']['displayFormats'] = array('label' => $displayFormatLabel, 'input' => $displayFormatSelect);

		$dateValue = date('Y-m-d');
		$dateFormat = '%d.%m.%Y'; // TODO: Deal with this % bullshit somehow.
		$dateSelect = JHtml::_('calendar', $dateValue, 'date', 'date', $dateFormat, $attribs);
		$dateLabel = JText::_('JDATE');
		$this->fields['formatSettings']['date'] = array('label' => $dateLabel, 'input' => $dateSelect);

		$dateRestrictions = array();
		$dateRestrictions[] = array('text' => JText::_('COM_THM_ORGANIZER_DAY'), 'value' => 'day');
		$dateRestrictions[] = array('text' => JText::_('COM_THM_ORGANIZER_WEEK'), 'value' => 'week');
		$dateRestrictions[] = array('text' => JText::_('COM_THM_ORGANIZER_MONTH'), 'value' => 'month');
		$dateRestrictions[] = array('text' => JText::_('COM_THM_ORGANIZER_MONTH'), 'value' => 'semester');
		$dateRestrictions[] = array('text' => JText::_('COM_THM_ORGANIZER_CUSTOM'), 'value' => 'custom');
		$defaultDateRestriction = 'week';
		$dateRestrictionSelect = JHtml::_('select.genericlist', $dateRestrictions, 'dateRestriction', $attribs, 'value', 'text', $defaultDateRestriction);
		$dateRestrictionLabel = JText::_('COM_THM_ORGANIZER_DATE_RESTRICTIONS');
		$this->fields['formatSettings']['dateRestrictions'] = array('label' => $dateRestrictionLabel, 'input' => $dateRestrictionSelect);

		// TODO: Add grid selection here

		$scheduleFormats = array();
		$scheduleFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_STACK_PLAN'), 'value' => 'stack');
		$scheduleFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_SEQUENCE_PLAN'), 'value' => 'sequence');
		$defaultScheduleFormat = 'sequence';
		$scheduleFormatSelect = JHtml::_('select.genericlist', $scheduleFormats, 'scheduleFormat', $attribs, 'value', 'text', $defaultScheduleFormat);
		$scheduleFormatLabel = JText::_('COM_THM_ORGANIZER_SCHEDULE_FORMATS');
		$this->fields['formatSettings']['scheduleFormat'] = array('label' => $scheduleFormatLabel, 'input' => $scheduleFormatSelect);
	}

	/**
	 * Creates resource selection fields for the form
	 *
	 * @return void sets indexes in $this->fields['resouceSettings'] with html content
	 */
	private function setResourceFields()
	{
		$this->fields['resourceSettings'] = array();
		$attribs = array('size' => '10', 'multiple' => 'multiple');
		$defaultOptions = array(array('value' => '', 'text' => JText::_('JALL')));

		// Departments
		$defaultDepartmentOption = '';
		$planDepartmentOptions = $this->model->getDepartmentOptions();
		$viewDepartmentOptions = array_merge($defaultOptions, $planDepartmentOptions);
		$departmentSelect = JHtml::_('select.genericlist', $viewDepartmentOptions, 'departmentIDs', $attribs, 'value', 'text', $defaultDepartmentOption);
		$departmentLabel = JText::_('COM_THM_ORGANIZER_DEPARTMENTS');
		$this->fields['resourceSettings']['departmetIDs'] = array('label' => $departmentLabel, 'input' => $departmentSelect);

		// Programs
		$defaultProgramOption = '';
		$planProgramOptions = $this->model->getProgramOptions();
		$viewProgramOptions = array_merge($defaultOptions, $planProgramOptions);
		$programSelect = JHtml::_('select.genericlist', $viewProgramOptions, 'programIDs', $attribs, 'value', 'text', $defaultProgramOption);
		$programLabel = JText::_('COM_THM_ORGANIZER_PROGRAMS');
		$this->fields['resourceSettings']['programIDs'] = array('label' => $programLabel, 'input' => $programSelect);

		// Pools
		$defaultPoolOption = '';
		$planPoolOptions = $this->model->getPoolOptions();
		$viewPoolOptions = array_merge($defaultOptions, $planPoolOptions);
		$poolSelect = JHtml::_('select.genericlist', $viewPoolOptions, 'poolIDs', $attribs, 'value', 'text', $defaultPoolOption);
		$poolLabel = JText::_('COM_THM_ORGANIZER_POOLS');
		$this->fields['resourceSettings']['poolIDs'] = array('label' => $poolLabel, 'input' => $poolSelect);


		// Teachers
		$defaultTeacherOption = '';
		$planTeacherOptions = $this->model->getTeacherOptions();
		$viewTeacherOptions = array_merge($defaultOptions, $planTeacherOptions);
		$teacherSelect = JHtml::_('select.genericlist', $viewTeacherOptions, 'teacherIDs', $attribs, 'value', 'text', $defaultTeacherOption);
		$teacherLabel = JText::_('COM_THM_ORGANIZER_TEACHERS');
		$this->fields['resourceSettings']['teacherIDs'] = array('label' => $teacherLabel, 'input' => $teacherSelect);

		// Rooms
		$defaultRoomOption = '';
		$planRoomOptions = $this->model->getRoomOptions();
		$viewRoomOptions = array_merge($defaultOptions, $planRoomOptions);
		$roomSelect = JHtml::_('select.genericlist', $viewRoomOptions, 'roomIDs', $attribs, 'value', 'text', $defaultRoomOption);
		$roomLabel = JText::_('COM_THM_ORGANIZER_ROOMS');
		$this->fields['resourceSettings']['roomIDs'] = array('label' => $roomLabel, 'input' => $roomSelect);
	}
}
