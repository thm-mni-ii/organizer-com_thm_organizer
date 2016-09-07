<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelRoom_display
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

define('SCHEDULE', 1);
define('ALTERNATING', 2);
define('CONTENT', 3);

/**
 * Retrieves lesson and event data for a single room and day
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelRoom_Display extends JModelLegacy
{
	public $params = array();

	private $_schedules;

	public $blocks;

	private $_dbDate = "";

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$input = JFactory::getApplication()->input;

		$ipData       = array('ip' => $input->server->getString('REMOTE_ADDR', ''));
		$monitorEntry = JTable::getInstance('monitors', 'thm_organizerTable');
		$registered   = $monitorEntry->load($ipData);

		if (!$registered)
		{
			$this->params['layout'] = 'default';

			return;
		}

		$templateSet = $input->getString('tmpl', '') == 'component';
		if (!$templateSet)
		{
			$this->redirectToComponentTemplate();
		}

		$this->setParams($monitorEntry);

		$this->setRoomInformation();

		$this->setScheduleInformation();
	}

	/**
	 * Redirects to the component template
	 *
	 * @return  void
	 */
	private function redirectToComponentTemplate()
	{
		$app   = JFactory::getApplication();
		$base  = JUri::root() . 'index.php?';
		$query = $app->input->server->get('QUERY_STRING', '', 'raw') . '&tmpl=component';
		$app->redirect($base . $query);
	}

	/**
	 * Sets display parameters
	 *
	 * @param   object &$monitorEntry the JTable object for the monitors table
	 *
	 * @return  void
	 */
	private function setParams(&$monitorEntry)
	{
		if ($monitorEntry->useDefaults)
		{
			$this->params['display']          = JComponentHelper::getParams('com_thm_organizer')->get('display', 1);
			$this->params['schedule_refresh'] = JComponentHelper::getParams('com_thm_organizer')->get('schedule_refresh');
			$this->params['content_refresh']  = JComponentHelper::getParams('com_thm_organizer')->get('content_refresh');
			$this->params['content']          = JComponentHelper::getParams('com_thm_organizer')->get('content');
			$defaultLayout                    = JComponentHelper::getParams('com_thm_organizer')->get('display');
		}
		else
		{
			$this->params['display']          = $monitorEntry->display;
			$this->params['schedule_refresh'] = $monitorEntry->schedule_refresh;
			$this->params['content_refresh']  = $monitorEntry->content_refresh;
			$this->params['content']          = $monitorEntry->content;
			$defaultLayout                    = '';
		}

		$useComponentDisplay = ($monitorEntry->useDefaults AND !empty($defaultLayout));
		$monitorDisplayValue = (empty($this->params['display'])) ? SCHEDULE : $this->params['display'];
		$displayValue        = $useComponentDisplay ? $defaultLayout : $monitorDisplayValue;

		switch ($displayValue)
		{
			case ALTERNATING:
				$this->setAlternatingLayout();
				break;
			case CONTENT:
				$this->params['layout'] = 'content';
				break;
			case SCHEDULE:
			default:
				$this->params['layout'] = 'schedule';
		}

		$this->params['roomID'] = $monitorEntry->roomID;
	}

	/**
	 * Determines which display behaviour is desired based on which layout was previously used
	 *
	 * @return  void
	 */
	private function setAlternatingLayout()
	{
		$session   = JFactory::getSession();
		$displayed = $session->get('displayed', 'schedule');

		if ($displayed == 'schedule')
		{
			$this->params['layout'] = 'content';

			return;
		}

		if ($displayed == 'schedule')
		{
			$this->params['layout'] = 'content';

			return;
		}

		$session->set('displayed', $this->params['layout']);
	}

	/**
	 * Retrieves the name and id of the room
	 *
	 * @return  void
	 */
	private function setRoomInformation()
	{
		$roomEntry = JTable::getInstance('rooms', 'thm_organizerTable');
		try
		{
			$roomEntry->load($this->params['roomID']);
			$this->params['roomName']  = $roomEntry->longname;
			$this->params['gpuntisID'] = strpos($roomEntry->gpuntisID, 'RM_') === 0 ?
				substr($roomEntry->gpuntisID, 3) : $roomEntry->gpuntisID;
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
			$this->params['roomName']  = '';
			$this->params['gpuntisID'] = '';
		}
	}

	/**
	 * Sets information about the daily schedule
	 *
	 * @return  void
	 */
	private function setScheduleInformation()
	{
		$this->params['date'] = getdate(time());
		$this->_dbDate        = date('Y-m-d', $this->params['date'][0]);
		$this->setSchedules();
		if (count($this->_schedules))
		{
			$this->getBlocks();
		}
	}

	/**
	 * Retireves schedules valid for the requested date
	 *
	 * @return  void
	 */
	private function setSchedules()
	{
		$dbo   = $this->getDbo();
		$query = $dbo->getQuery(true);
		$query->select("schedule");
		$query->from("#__thm_organizer_schedules");
		$query->where("startDate <= '$this->_dbDate'");
		$query->where("endDate >= '$this->_dbDate'");
		$query->where("active = 1");
		$dbo->setQuery((string) $query);

		try
		{
			$schedules = $dbo->loadColumn();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return;
		}

		if (empty($schedules))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('COM_THM_ORGANIZER_MESSAGE_NO_SCHEDULES_FOR_DATE'), 'error');

			return;
		}

		foreach ($schedules as $key => $schedule)
		{
			$schedules[$key] = json_decode($schedule);
		}

		$this->_schedules = $schedules;
	}

	/**
	 * Creates an array of blocks and fills them with data
	 *
	 * @return void
	 */
	private function getBlocks()
	{
		$grids        = $this->_schedules[0]->periods;
		$this->blocks = array();
		foreach ($grids as $name => $grid)
		{
			if ($name == 'Haupt-Zeitraster')
			{
				foreach ($grid AS $number => $data)
				{
					$starttime                          = substr($data->starttime, 0, 2) . ":" . substr($data->starttime, 2);
					$endtime                            = substr($data->endtime, 0, 2) . ":" . substr($data->endtime, 2);
					$this->blocks[$number]              = new stdClass;
					$this->blocks[$number]->period      = $number;
					$this->blocks[$number]->starttime   = $starttime;
					$this->blocks[$number]->endtime     = $endtime;
					$this->blocks[$number]->displayTime = "$starttime - $endtime";
				}
			}
		}

		$this->setLessonData();
		$this->setDummyText();
	}

	/**
	 * Adds basic lesson information to a block (if available)
	 *
	 * @return void
	 */
	private function setLessonData()
	{
		foreach ($this->_schedules as $schedule)
		{
			$this->setBlocksDataBySchedule($schedule);
		}
	}

	/**
	 * Sets schedule specific information for a given block
	 *
	 * @param   object &$schedule the schedule being iterated
	 *
	 * @return  void  sets information in the given block
	 */
	private function setBlocksDataBySchedule(&$schedule)
	{
		foreach ($schedule->periods as $gridName => $grid)
		{
			$this->setGridLessons($schedule, $gridName, $grid);
		}
	}

	/**
	 * Iterates through the blocks of a specific grid adding the lesson data to the display blocks
	 *
	 * @param   object &$schedule the schedule being iterated
	 * @param   string $gridName  the name of the grid being iterated
	 * @param   object &$grid     the grid information
	 *
	 * @return  void  sets attributes of $this->blocks
	 *
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	private function setGridLessons(&$schedule, $gridName, &$grid)
	{
		foreach ($grid as $gridPeriod => $gridBlock)
		{
			foreach ($this->blocks as $planBlock)
			{
				$relevant = $this->hasRelevance($gridBlock, $planBlock);
				if ($relevant)
				{
					$this->setLessons($schedule, $planBlock->period, $gridName, $gridBlock);
				}
			}
		}
	}

	/**
	 * Compares the start and end times of the two blocks, determining the relevancy of one block for the other
	 *
	 * @param   object $gridBlock the data for the grid block being iterated
	 * @param   object $planBlock the data for the plan block being iterated
	 *
	 * @return  bool  true if the grid block is relevant for the plan block, otherwise false
	 */
	private function hasRelevance($gridBlock, $planBlock)
	{
		$gbStarttime     = substr($gridBlock->starttime, 0, 2) . ":" . substr($gridBlock->starttime, 2);
		$gbEndtime       = substr($gridBlock->endtime, 0, 2) . ":" . substr($gridBlock->endtime, 2);
		$pbStarttime     = substr($planBlock->starttime, 0, 2) . ":" . substr($planBlock->starttime, 2);
		$pbEndtime       = substr($planBlock->endtime, 0, 2) . ":" . substr($planBlock->endtime, 2);
		$startIsRelevant = ($gbStarttime >= $pbStarttime AND $gbStarttime < $pbEndtime);
		$endIsRelevant   = ($gbEndtime > $pbStarttime AND $gbEndtime <= $pbEndtime);

		return ($startIsRelevant OR $endIsRelevant);
	}

	/**
	 * Checks which grid blocks are relevant for the displayed block times
	 *
	 * @param   object &$schedule the schedule being iterated
	 * @param   int    $period    the period id of the block being iterated
	 * @param   array  $gridName  the block being iterated
	 * @param   object $gridBlock the data for the grid block being iterated
	 *
	 * @return  array   an array containing relevant block information
	 */
	private function setLessons(&$schedule, $period, $gridName, $gridBlock)
	{
		$lessons = $schedule->calendar->{$this->_dbDate}->$period;

		foreach ($lessons AS $lessonID => $rooms)
		{
			$lesson = $schedule->lessons->$lessonID;

			// This lesson will either be handled in another iteration or is not relevant
			if ($lesson->grid != $gridName)
			{
				continue;
			}

			// The lesson no longer exists for this block
			if (!empty($lessons->$lessonID->delta) AND $lessons->$lessonID->delta == 'removed')
			{
				break;
			}

			foreach ($rooms as $roomID => $roomDelta)
			{
				$notARoom   = $roomID == 'delta';
				$irrelevant = $roomID != $this->params['gpuntisID'];
				$removed    = (!$notARoom AND $roomDelta == 'removed');
				$skip       = ($notARoom OR $irrelevant OR $removed);
				if ($skip)
				{
					continue;
				}

				$subjects = (array) $schedule->lessons->$lessonID->subjects;
				$this->filterSubjects($subjects);

				if (!isset($this->blocks[$period]->lessons))
				{
					$this->blocks[$period]->lessons = array();
				}

				if (!isset($this->blocks[$period]->lessons[$lessonID]))
				{
					$this->blocks[$period]->lessons[$lessonID] = array();
				}

				$subjectIDs  = array_keys($subjects);
				$lessonTitle = $this->getLessonTitle($subjectIDs, $schedule);

				if (!empty($schedule->lessons->$lessonID->description))
				{
					$lessonTitle .= " - " . $schedule->lessons->$lessonID->description;
				}

				$this->blocks[$period]->lessons[$lessonID]['title'] = $lessonTitle;

				$teachersIDs = (array) $schedule->lessons->$lessonID->teachers;
				$teachers    = array();
				foreach ($teachersIDs as $teacherID => $teacherDelta)
				{
					if ($teacherDelta == 'removed')
					{
						unset($teachers[$teacherID]);
					}

					$teachers[$teacherID] = $schedule->teachers->$teacherID->surname;
				}

				$this->blocks[$period]->lessons[$lessonID]['teacher'] = implode(', ', $teachers);

				if ($gridName != 'Haupt-Zeitraster')
				{
					$this->blocks[$period]->lessons[$lessonID]['time'] = "($gridBlock->starttime - $gridBlock->endtime)";
				}
				else
				{
					$this->blocks[$period]->lessons[$lessonID]['time'] = '';
				}
			}
		}
	}

	/**
	 * Removes removed subjects from the list of subjects associated with a given lesson
	 *
	 * @param   array &$subjects the lesson's subjects
	 *
	 * @return  void  removes indexes from &$subjects
	 */
	private function filterSubjects(&$subjects)
	{
		foreach ($subjects as $subjectID => $subjectDelta)
		{
			if ($subjectDelta == 'removed')
			{
				unset($subjects[$subjectID]);
			}
		}
	}

	/**
	 * Generates the base lesson title from the ids of the associated subjects
	 *
	 * @param   array  $subjectIDs the ids of the relevant subjects
	 * @param   object &$schedule  the schedule being iterated
	 *
	 * @return  string  the lesson title
	 */
	private function getLessonTitle($subjectIDs, &$schedule)
	{
		if (count($subjectIDs) > 1)
		{
			$subjectNames = array();
			foreach ($subjectIDs as $subjectID)
			{
				$subjectNames[$subjectID] = $schedule->subjects->$subjectID->name;
			}

			$lessonTitle = implode(', ', $subjectNames);
		}
		else
		{
			$subjectID = array_shift($subjectIDs);
			$longname  = $schedule->subjects->$subjectID->longname;
			$shortname = $schedule->subjects->$subjectID->name;

			// A little arbitrary but implementing settings is a little too much effort
			$lessonTitle = (strlen($longname) <= 30) ? $longname : $shortname;
		}

		return $lessonTitle;
	}

	/**
	 * Sets a dummy text for blocks without lesson information
	 *
	 * @return  void  sets attributes of $this->blocks
	 */
	private function setDummyText()
	{
		foreach ($this->blocks as $period => $block)
		{
			if (empty($block->lessons))
			{
				$this->blocks[$period]->lessons                     = array();
				$this->blocks[$period]->lessons['DUMMY']            = array();
				$this->blocks[$period]->lessons['DUMMY']['title']   = JText::_('COM_THM_ORGANIZER_NO_INFORMATION_AVAILABLE');
				$this->blocks[$period]->lessons['DUMMY']['teacher'] = '';
				$this->blocks[$period]->lessons['DUMMY']['time']    = '';
			}
		}
	}
}
