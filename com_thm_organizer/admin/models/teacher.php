<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelTeacher
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/models/merge.php';

/**
 * Class provides methods for teacher database abstraction
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelTeacher extends THM_OrganizerModelMerge
{
	/**
	 * Removes the resource from the schedule
	 *
	 * @param   object &$schedule  the schedule from which the resource will be removed
	 * @param   int    $resourceID the id of the resource in the db
	 * @param   string $gpuntisID  the gpuntis ID for the given resource
	 *
	 * @return  void  modifies the schedule
	 */
	protected function removeFromSchedule(&$schedule, $resourceID, $gpuntisID)
	{
		// Teacher not used in schedule
		if (empty($schedule->teachers->$gpuntisID))
		{
			return;
		}

		unset($schedule->teachers->$gpuntisID);
		foreach ($schedule->lessons as $lessonID => $lesson)
		{
			if (isset($schedule->lessons->$lessonID->teachers->$gpuntisID))
			{
				unset($schedule->lessons->$lessonID->teachers->$gpuntisID);
			}
		}
	}

	/**
	 * Updates key references to the entry being merged.
	 *
	 * @param   int   $newDBID  the id onto which the room entries merge
	 * @param   array $oldDBIDs an array containing the ids to be replaced
	 *
	 * @return  boolean  true on success, otherwise false
	 */
	protected function updateAssociations($newDBID, $oldDBIDs)
	{
		$drUpdated = $this->updateAssociation('teacher', $newDBID, $oldDBIDs, 'department_resources');
		if (!$drUpdated)
		{
			return false;
		}

		$ltUpdated = $this->updateAssociation('teacher', $newDBID, $oldDBIDs, 'lesson_teachers');
		if (!$ltUpdated)
		{
			return false;
		}

		$ptUpdated = $this->updateAssociation('teacher', $newDBID, $oldDBIDs, 'plan_teachers');
		if (!$ptUpdated)
		{
			return false;
		}

		return $this->updateAssociation('teacher', $newDBID, $oldDBIDs, 'subject_teachers');
	}

	/**
	 * Processes the data for an individual schedule
	 *
	 * @param   object &$schedule     the schedule being processed
	 * @param   array  &$data         the data for the schedule db entry
	 * @param   int    $newDBID       the new id to use for the merged resource in the database (and schedules)
	 * @param   string $newGPUntisID  the new gpuntis ID to use for the merged resource in the schedule
	 * @param   array  $allGPUntisIDs all gpuntis IDs for the resources to be merged
	 * @param   array  $allDBIDs      all db IDs for the resources to be merged
	 *
	 * @return  void
	 */
	protected function updateSchedule(&$schedule, &$data, $newDBID, $newGPUntisID, $allGPUntisIDs, $allDBIDs)
	{
		if (!empty($data['fieldID']))
		{
			$fieldDBID      = $data['fieldID'];
			$fieldGPUntisID = $this->getDescriptionGPUntisID('fields', $data['fieldID']);
		}
		else
		{
			$fieldDBID = $fieldGPUntisID = '';
		}

		foreach ($schedule->teachers as $teacherGPUntisID => $teacher)
		{
			if (in_array($teacherGPUntisID, $allGPUntisIDs))
			{
				unset($schedule->teachers->$teacherGPUntisID);

				$schedule->teachers->$newGPUntisID              = new stdClass;
				$schedule->teachers->$newGPUntisID->id          = $newDBID;
				$schedule->teachers->$newGPUntisID->gpuntisID   = $newGPUntisID;
				$schedule->teachers->$newGPUntisID->surname     = $data['surname'];
				$schedule->teachers->$newGPUntisID->forename    = empty($data['forename']) ? '' : $data['forename'];
				$schedule->teachers->$newGPUntisID->username    = empty($data['username']) ? '' : $data['username'];
				$schedule->teachers->$newGPUntisID->fieldID     = $fieldDBID;
				$schedule->teachers->$newGPUntisID->description = $fieldGPUntisID;
			}
		}

		foreach ($schedule->lessons as $lessonID => $lesson)
		{
			foreach ($lesson->teachers as $teacherGPUntisID => $delta)
			{
				if ($teacherGPUntisID == 'delta')
				{
					continue;
				}

				if (in_array($teacherGPUntisID, $allGPUntisIDs))
				{
					unset($schedule->lessons->$lessonID->teachers->$teacherGPUntisID);
					$schedule->lessons->$lessonID->teachers->$newGPUntisID = $delta;
				}
			}
		}
	}
}
