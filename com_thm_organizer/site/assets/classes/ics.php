<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THMICSBuilder
 * @description Creates ICS files for com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once dirname(__FILE__) . "/AbstractBuilder.php";
/** @noinspection PhpIncludeInspection */
require_once JPATH_BASE . "/libraries/thm_core/icalcreator/iCalcreator.php";

/**
 * Class provides methods to create a schedule in ics format
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THMICSBuilder extends THMAbstractBuilder
{
	/**
	 * Config
	 *
	 * @var  Object
	 */
	private $_cfg = null;

	/**
	 * List with all subjects
	 *
	 * @var  Object
	 */
	private $_subjects = null;

	/**
	 * List with all teachers
	 *
	 * @var  Object
	 */
	private $_teachers = null;

	/**
	 * List with all modules
	 *
	 * @var  Object
	 */
	private $_pools = null;

	/**
	 * List with all schedule grids
	 *
	 * @var  Object
	 */
	private $_grids = null;

	/**
	 * Constructor with the configuration object
	 *
	 * @param   Object $cfg A object which has configurations including
	 */
	public function __construct($cfg)
	{
		$this->_cfg = $cfg;
	}

	/**
	 * Method to create a ics schedule
	 *
	 * @param   Object $scheduleGrid The event object
	 * @param   string $username     The current logged in username
	 * @param   string $title        The schedule title
	 *
	 * @return array An array with information about the status of the creation
	 */
	public function createSchedule($scheduleGrid, $username, $title)
	{
		$planningPeriod = JFactory::getApplication()->input->getString('departmentAndSemester');
		$activeSchedule = $this->getActiveSchedule($planningPeriod);
		$scheduleData   = json_decode($activeSchedule->schedule);

		// To save memory unset schedule
		unset($activeSchedule->schedule);
		$this->_subjects = $scheduleData->subjects;
		unset($scheduleData->subjects);
		$this->_teachers = $scheduleData->teachers;
		unset($scheduleData->teachers);
		$this->_pools = $scheduleData->pools;
		unset($scheduleData->pools);
		$this->_grids = $scheduleData->periods;
		unset($scheduleData->periods);

		if ($title == JText::_("COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE") && $username != "")
		{
			$title = $username . " - " . $title;
		}

		$vCalendar = new vcalendar;
		$vCalendar->setConfig('unique_id', "MySched");
		$vCalendar->setConfig("lang", "de");
		$vCalendar->setProperty("x-wr-calname", $title);
		$vCalendar->setProperty("X-WR-CALDESC", "Calendar Description");
		$vCalendar->setProperty("X-WR-TIMEZONE", "Europe/Berlin");
		$vCalendar->setProperty("PRODID", "-//212.201.14.161//NONSGML iCalcreator 2.6//");
		$vCalendar->setProperty("VERSION", "2.0");
		$vCalendar->setProperty("METHOD", "PUBLISH");

		$vTimeZone1 = new vtimezone;
		$vTimeZone1->setProperty("TZID", "Europe/Berlin");

		$vTimeZone2 = new vtimezone('standard');
		$vTimeZone2->setProperty("DTSTART", 1601, 1, 1, 0, 0, 0);
		$vTimeZone2->setProperty("TZNAME", "Standard Time");

		$vTimeZone1->setComponent($vTimeZone2);
		$vCalendar->setComponent($vTimeZone1);

		foreach ($scheduleGrid as $lesson)
		{
			$this->setEvent($vCalendar, $lesson);
		}

		$vCalendar->saveCalendar($this->_cfg->pdf_downloadFolder, $title . '.ics');
		$resparr['url'] = "false";

		return array("success" => true, "data" => $resparr);
	}

	/**
	 * Method to set an event
	 *
	 * @param   object &$vCalendar The event array
	 * @param   object $lesson     The semester end date
	 *
	 * @return  array  an array with event information
	 */
	private function setEvent(&$vCalendar, $lesson)
	{
		$lessonSubject = key($lesson->subjects);
		$lessonName    = $this->_subjects->{$lessonSubject}->longname;

		$lessonTeachers = implode(
			", ",
			$this->getTeacherNames(
				array_merge(
					array_keys((array) $lesson->teachers, ""),
					array_keys((array) $lesson->teachers, "new")
				)
			)
		);

		$lessonComment = $lesson->comment;
		foreach ($lesson->calendar as $calendarKey => $calendarValue)
		{
			foreach ($calendarValue as $blockKey => $blockValue)
			{
				foreach ($blockValue->lessonData as $roomKey => $roomValue)
				{
					if ($roomValue != "removed")
					{
						$lessonBlock = $blockKey;
						$lessonRoom  = $roomKey;

						$lessonSummary = $lessonName . " bei " . $lessonTeachers . " im " . $lessonRoom;

						$lessonDate      = explode("-", $calendarKey);
						$lessonTime      = $this->blocktotime($lessonBlock);
						$lessonBeginTime = explode(":", $lessonTime[0]);
						$lessonEndTime   = explode(":", $lessonTime[1]);

						$lessonStartDate = array(
							"year"  => $lessonDate[0],
							"month" => $lessonDate[1],
							"day"   => $lessonDate[2],
							"hour"  => $lessonBeginTime[0],
							"min"   => $lessonBeginTime[1],
							"sec"   => 0
						);
						$lessonEndDate   = array(
							"year"  => $lessonDate[0],
							"month" => $lessonDate[1],
							"day"   => $lessonDate[2],
							"hour"  => $lessonEndTime[0],
							"min"   => $lessonEndTime[1],
							"sec"   => 0
						);

						$e = new vevent;
						$e->setProperty("ORGANIZER", $lessonTeachers);
						$e->setProperty("DTSTART", $lessonStartDate);
						$e->setProperty("DTEND", $lessonEndDate);
						$e->setProperty("LOCATION", $lessonRoom);
						$e->setProperty("TRANSP", "OPAQUE");
						$e->setProperty("SEQUENCE", "0");
						$e->setProperty("SUMMARY", $lessonSummary);
						$e->setProperty("PRIORITY", "5");
						$e->setProperty("DESCRIPTION", $lessonComment);
						$vCalendar->setComponent($e);
					}
				}
			}
		}
	}

	/**
	 * Method to check the username and password
	 *
	 * @param   int $block The block
	 *
	 * @return array An array which includes the block time
	 */
	private function blocktotime($block)
	{
		// Immer eine Stunden weniger wegen tz=Europe/Berlin (+0100)
		$times = array(
			1 => array(
				0 => "8:00",
				1 => "9:30"
			),
			2 => array(
				0 => "9:50",
				1 => "11:20"
			),
			3 => array(
				0 => "11:30",
				1 => "13:00"
			),
			4 => array(
				0 => "14:00",
				1 => "15:30"
			),
			5 => array(
				0 => "15:45",
				1 => "17:15"
			),
			6 => array(
				0 => "17:30",
				1 => "19:00"
			)
		);

		return $times[$block];
	}

	/**
	 * Method to get the active schedule
	 *
	 * @param   string $planningPeriod The department semester selection (Default: null)
	 *
	 * @return   mixed  The active schedule or false
	 */
	private function getActiveSchedule($planningPeriod = null)
	{
		$schedulerModel = JModelLegacy::getInstance('scheduler', 'thm_organizerModel', array('ignore_request' => false));
		$activeSchedule = $schedulerModel->getActiveSchedule($planningPeriod);

		return $activeSchedule;
	}

	/**
	 * Method to transform teacher ids to teacher names
	 *
	 * @param   array $teachers An array with teacher ids
	 *
	 * @return  array  An array with teacher names
	 */
	private function getTeacherNames($teachers)
	{
		for ($index = 0; $index < count($teachers); $index++)
		{
			$teacher = $teachers[$index];
			if (!empty($this->_teachers->{$teacher}->surname))
			{
				$teachers[$index] = $this->_teachers->{$teacher}->surname;
			}

			if (!empty($this->_teachers->{$teacher}->firstname))
			{
				$teachers[$index] .= ", " . $this->_teachers->{$teacher}->firstname{0} . ".";
			}
		}

		return $teachers;
	}
}
