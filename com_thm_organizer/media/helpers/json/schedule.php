<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelXML_Schedule
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class enapsulating data abstraction and business logic for json schedules.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelJSONSchedule extends JModelLegacy
{
	/**
	 * Object containing information from the actual schedule
	 *
	 * @var object
	 */
	private $schedule = null;

	/**
	 * Object containing information from a comparison schedule
	 *
	 * @var object
	 */
	private $compSchedule = null;

	public function migrate($scheduleID)
	{
		$scheduleRow = JTable::getInstance('schedules', 'thm_organizerTable');
		$loaded      = $scheduleRow->load($scheduleID);

		if (!$loaded)
		{
			return false;
		}

		$this->compSchedule = json_decode($scheduleRow->schedule);
		$this->schedule     = new stdClass;

		// Common information
		$this->schedule->departmentID     = $scheduleRow->departmentID;
		$this->schedule->planningPeriodID = $scheduleRow->planningPeriodID;
		$this->schedule->creationDate     = $this->compSchedule->creationdate;
		$this->schedule->creationTime     = $this->compSchedule->creationtime;

		$this->schedule->programs = array();
		$this->migratePrograms();

		$this->schedule->pools = array();
		$this->migratePools();

		$this->schedule->rooms = array();
		$this->migrateRooms();

		$this->schedule->subjects = array();
		$this->migrateSubjects();

		$this->schedule->teachers = array();
		$this->migrateTeachers();


		// Make this attribute accessible during the lesson/calendar migration process
		$this->schedule->active = $scheduleRow->active;

		$this->schedule->calendar       = new stdClass;
		$this->schedule->configurations = array();
		$this->schedule->lessons        = array();
		$this->migrateLessons();
		$this->migrateCalendar();

		// Don't save this in the schedule
		unset($this->schedule->active);

		$scheduleRow->newSchedule = json_encode($this->schedule);

		$scheduleRow->store();

		if ($scheduleRow->active)
		{

		}
	}

	/**
	 * Migrates the deprecated format calendar/lessons nodes to the new format calendar/configurations/lessons nodes
	 *
	 * @return void alters the calendar/configurations/lessons nodes of the object's schedule
	 */
	private function migrateCalendar()
	{
		$configurationIndex = 1;
		foreach ($this->compSchedule->calendar as $date => $blocks)
		{
			if (empty($this->schedule->calendar->$date))
			{
				$this->schedule->calendar->$date = new stdClass;
			}

			foreach ($blocks as $blockNo => $blockLessons)
			{
				foreach ($blockLessons as $lessonCode => $instanceRooms)
				{
					$gridName = $this->compSchedule->lessons->$lessonCode->grid;
					$times = $this->compSchedule->periods->$gridName->$blockNo;
					$time =  $times->starttime . '-' . $times->endtime;

					if (empty($this->schedule->calendar->$date->$time))
					{
						$this->schedule->calendar->$date->$time = new stdClass;
					}

					$lessonID = $this->compSchedule->lessons->$lessonCode->gpuntisID;
					$this->schedule->calendar->$date->$time->$lessonID = new stdClass;


					$this->schedule->calendar->$date->$time->$lessonID->delta
						= empty($instanceRooms->delta)? '' : $this->resolveDelta($instanceRooms->delta);
					$configurations = $this->migrateConfigurations($lessonCode, $instanceRooms);
					$this->schedule->calendar->$date->$time->$lessonID->configurations = $configurations;
				}
			}
		}
	}

	/**
	 * Creates complete instance configurations with lessonID, subjectID, teacher and room IDs => deltas
	 *
	 * @param string $lessonCode the reference string used in the deprecated schedules
	 * @param object $instanceRooms the room gpuntis ids with their corresponding deltas
	 *
	 * @return array the ids of the configurations
	 */
	private function migrateConfigurations($lessonCode, $instanceRooms)
	{
		$rooms = array();
		foreach ($instanceRooms as $gpuntisID => $delta)
		{
			if ($gpuntisID == 'delta')
			{
				continue;
			}
			$rooms[$this->compSchedule->rooms->$gpuntisID->id] = $this->resolveDelta($delta);
		}

		$configurations = array();
		$rawBaseConfigs = $this->compSchedule->lessons->$lessonCode->configurations;
		foreach ($rawBaseConfigs as $rawBaseConfig)
		{
			// lesson, subject & teachers
			$config = json_decode($rawBaseConfig);
			$config->rooms = $rooms;
			$jsonConfig = json_encode($config);

			$configExists = in_array($jsonConfig, $this->schedule->configurations);
			if (!$configExists)
			{
				$this->schedule->configurations[] = $jsonConfig;
			}

			$configIndex = array_search($jsonConfig, $this->schedule->configurations);

			$referenceExists = in_array($configIndex, $configurations);
			if (!$referenceExists)
			{
				$configurations[] = $configIndex;
			}
		}
		return $configurations;
	}

	/**
	 * Migrates the deprecated format calendar/lessons nodes to the new format calendar/configurations/lessons nodes
	 *
	 * @return void alters the calendar/configurations/lessons nodes of the object's schedule
	 */
	private function migrateLessons()
	{
		foreach ($this->compSchedule->lessons as $lessonCode => $lesson)
		{
			$lessonID                                     = $lesson->gpuntisID;
			$this->schedule->lessons[$lessonID]           = new stdClass;
			$this->schedule->lessons[$lessonID]->delta    = $this->resolveDelta($lesson);

			if (!empty($lesson->methodID))
			{
				$this->schedule->lessons[$lessonID]->methodID = $lesson->methodID;
			}

			$this->schedule->lessons[$lessonID]->comment  = $lesson->comment;

			$pools    = $this->resolveCollection($lesson->pools, 'pools');
			$subjects = $this->resolveCollection($lesson->subjects, 'subjects');
			$this->schedule->lessons[$lessonID]->subjects = array();
			foreach ($subjects as $subjectID => $delta)
			{
				$this->schedule->lessons[$lessonID]->subjects[$subjectID] = new stdClass;
				$this->schedule->lessons[$lessonID]->subjects[$subjectID]->delta = $delta;
				$this->schedule->lessons[$lessonID]->subjects[$subjectID]->pools = $pools;

				$teachers = $this->resolveCollection($lesson->teachers, 'teachers');
				$this->schedule->lessons[$lessonID]->subjects[$subjectID]->teachers = $teachers;

				// Save this to the comp schedule for easier cross referencing in migrateCalendar
				if (empty($this->compSchedule->lessons->$lessonCode->configurations))
				{
					$this->compSchedule->lessons->$lessonCode->configurations = array();
				}

				$baseConfig = new stdClass;
				$baseConfig->lessonID = $lessonID;
				$baseConfig->subjectID = $subjectID;
				$baseConfig->teachers = $teachers;
				$jsonConfig = json_encode($baseConfig);

				if (!in_array($jsonConfig, $this->compSchedule->lessons->$lessonCode->configurations))
				{
					$this->compSchedule->lessons->$lessonCode->configurations[] = $jsonConfig;
				}
			}
		}
	}

	/**
	 * Migrates the deprecated format pools node to the new format pools node
	 *
	 * @return void alters the pools node of the object's schedule
	 */
	private function migratePools()
	{
		require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/pools.php';

		foreach ($this->compSchedule->pools as $gpuntisID => $pool)
		{
			// Newer format schedule has associated plan programs
			if (!empty($pool->id) AND !in_array($pool->id, $this->schedule->pools))
			{
				$this->schedule->pools[] = $pool->id;
				continue;
			}

			$poolID = THM_OrganizerHelperPools::getPlanResourceID($gpuntisID, $pool);
			if (!empty($poolID))
			{
				$this->compSchedule->pools->$gpuntisID->id = $poolID;
				$this->schedule->pools[]                   = $poolID;
				THM_OrganizerHelperDepartment_Resources::setDepartmentResource($poolID, 'poolID', $this->schedule->departmentID);
			}
		}
	}

	/**
	 * Migrates the deprecated format degrees node to the new format programs node
	 *
	 * @return void alters the programs node of the object's schedule
	 */
	private function migratePrograms()
	{
		require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/programs.php';

		foreach ($this->compSchedule->degrees as $gpuntisID => $program)
		{
			// Newer format schedule has associated plan programs
			if (!empty($program->id) AND !in_array($program->id, $this->schedule->programs))
			{
				$this->schedule->programs[] = $program->id;
				continue;
			}

			$programID = THM_OrganizerHelperPrograms::getPlanResourceID($program);
			if (!empty($programID))
			{
				$this->compSchedule->degrees->$gpuntisID->id = $programID;
				$this->schedule->programs[]                  = $programID;
				THM_OrganizerHelperDepartment_Resources::setDepartmentResource($programID, 'programID', $this->schedule->departmentID);
			}
		}
	}

	/**
	 * Migrates the deprecated format rooms node to the new format rooms node
	 *
	 * @return void alters the rooms node of the object's schedule
	 */
	private function migrateRooms()
	{
		require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/rooms.php';

		foreach ($this->compSchedule->rooms as $gpuntisID => $room)
		{
			// Newer format schedule has the room id available in the object
			if (!empty($room->id) AND !in_array($room->id, $this->schedule->rooms))
			{
				$this->schedule->rooms[] = $room->id;
				continue;
			}

			$roomID = THM_OrganizerHelperRooms::getID($gpuntisID, $room);
			if (!empty($roomID))
			{
				$this->compSchedule->rooms->$gpuntisID->id = $roomID;
				$this->schedule->rooms[]                   = $roomID;
				THM_OrganizerHelperDepartment_Resources::setDepartmentResource($roomID, 'roomID', $this->schedule->departmentID);
			}
		}
	}

	/**
	 * Migrates the deprecated format subjects node to the new format subjects node
	 *
	 * @return void alters the subjects node of the object's schedule
	 */
	private function migrateSubjects()
	{
		require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/subjects.php';

		foreach ($this->compSchedule->subjects as $gpuntisID => $subject)
		{
			// Newer format schedule has associated plan programs
			if (!empty($subject->id) AND !in_array($subject->id, $this->schedule->subjects))
			{
				$this->schedule->subjects[] = $subject->id;
				continue;
			}

			$subjectIndex = $this->compSchedule->departmentname . '_' . $gpuntisID;
			$subjectID    = THM_OrganizerHelperSubjects::getPlanResourceID($subjectIndex, $subject);
			if (!empty($subjectID))
			{
				$this->compSchedule->subjects->$gpuntisID->id = $subjectID;
				$this->schedule->subjects[]                   = $subjectID;
				THM_OrganizerHelperDepartment_Resources::setDepartmentResource($subjectID, 'subjectID', $this->schedule->departmentID);
			}
		}
	}

	/**
	 * Migrates the deprecated format teachers node to the new format teachers node
	 *
	 * @return void alters the teachers node of the object's schedule
	 */
	private function migrateTeachers()
	{
		require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/teachers.php';

		foreach ($this->compSchedule->teachers as $gpuntisID => $teacher)
		{
			// Newer format schedule has the room id available in the object
			if (!empty($teacher->id) AND !in_array($teacher->id, $this->schedule->teachers))
			{
				$this->schedule->teachers[] = $teacher->id;
				continue;
			}

			$teacherID = THM_OrganizerHelperTeachers::getID($gpuntisID, $teacher);
			if (!empty($teacherID))
			{
				$this->compSchedule->teachers->$gpuntisID->id = $teacherID;
				$this->schedule->teachers[]                   = $teacherID;
				THM_OrganizerHelperDepartment_Resources::setDepartmentResource($teacherID, 'teacherID', $this->schedule->departmentID);
			}
		}
	}

	/**
	 * Resolves the resource delta
	 *
	 * @param mixed $resource the resource being checked (object) or the value of a dynamic field typically string
	 *
	 * @return string empty if the schedule is inactive or the resource had no changes, otherwise new/removed
	 */
	private function resolveDelta($resource)
	{
		if (is_object($resource))
		{
			$value = empty($resource->delta) ? '' : $resource->delta;
		}
		else
		{
			$value = $resource;
		}

		return (empty($this->schedule->active) OR empty($value))? '' : $value;
	}

	/**
	 * Resolves the collection id strings to the numerical values from the database
	 *
	 * @param object $collection     the collection being processed
	 * @param string $collection the name of the collection being resolved
	 *
	 * @return array the id => delta mapping of the deprecated format lesson, empty if resolution failed
	 */
	private function resolveCollection($collection, $collectionName)
	{
		$return = array();
		if (empty($collection))
		{
			return $return;
		}

		foreach ($collection as $gpuntisID => $value)
		{
			$resourceID = $this->compSchedule->$collectionName->$gpuntisID->id;
			if (empty($resourceID))
			{
				continue;
			}

			$return[$resourceID] = $this->resolveDelta($value);
		}

		return $return;
	}
}
