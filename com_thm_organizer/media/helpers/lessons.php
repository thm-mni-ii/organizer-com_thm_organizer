<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperLessons
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class encapsulating data abstraction and business logic for lessons.
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperLessons
{
	/**
	 * Saves the lessons from the schedule object to the database and triggers functions for saving lesson associations.
	 *
	 * @return void saves lessons to the database
	 */
	public static function saveLessons()
	{
		foreach ($this->scheduleModel->schedule->lessons as $lesson)
		{
			// If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
			$table = JTable::getInstance('lessons', 'thm_organizerTable');

			$data              = array();
			$data['gpuntisID'] = $lesson->gpuntisID;
			$data['planName']  = $this->planName;
			$table->load($data);

			if (!empty($lesson->methodID))
			{
				$data['methodID'] = $lesson->methodID;
			}

			// Delta will be 'calculated' later but explicitly overwritten now irregardless
			$data['delta'] = '';

			$success = $table->save($data);
			if (!$success)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');
				continue;
			}

			$this->saveLessonSubjects($table->id, $lesson->plan_subjects);
		}
	}

	/**
	 * Saves the lesson pools from the schedule object to the database and triggers functions for saving lesson associations.
	 *
	 * @param string $lessonSubjectID the db id of the lesson subject association
	 * @param object $pools           the pools associated with the subject
	 * @param string $subjectNo       the subject's id in documentation
	 *
	 * @return void saves lessons to the database
	 */
	private function saveLessonPools($lessonSubjectID, $pools, $subjectID, $subjectNo)
	{
		foreach ($pools as $poolID => $delta)
		{
			// If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
			$table = JTable::getInstance('lesson_pools', 'thm_organizerTable');

			$data              = array();
			$data['subjectID'] = $lessonSubjectID;
			$data['poolID']    = $poolID;
			$table->load($data);

			// Delta will be 'calculated' later but explicitly overwritten now irregardless
			$data['delta'] = '';

			$success = $table->save($data);
			if (!$success)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');
				continue;
			}

			if (!empty($subjectNo))
			{
				$this->savePlanSubjectMapping($subjectID, $poolID, $subjectNo);
			}
		}
	}

	/**
	 * Saves the lesson subjectss from the schedule object to the database and triggers functions for saving lesson
	 * associations.
	 *
	 * @param string $lessonID the db id of the lesson subject association
	 * @param object $subjects the subjects associated with the lesson
	 *
	 * @return void saves lessons to the database
	 */
	private function saveLessonSubjects($lessonID, $subjects)
	{
		foreach ($subjects as $subjectID => $abstractConfig)
		{
			// If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
			$table = JTable::getInstance('lesson_subjects', 'thm_organizerTable');

			$data              = array();
			$data['lessonID']  = $lessonID;
			$data['subjectID'] = $subjectID;
			$table->load($data);

			// Delta will be 'calculated' later but explicitly overwritten now irregardless
			$data['delta'] = '';

			$success = $table->save($data);
			if (!$success)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');
				continue;
			}

			$subjectNo = empty($abstractConfig->subjectNo) ? null : $abstractConfig->subjectNo;
			$this->saveLessonPools($table->id, $abstractConfig->pools, $subjectID, $subjectNo);
			$this->saveLessonTeachers($table->id, $abstractConfig->teachers);
		}
	}

	/**
	 * Saves the lesson pools from the schedule object to the database and triggers functions for saving lesson associations.
	 *
	 * @param string $subjectID the db id of the lesson subject association
	 * @param object $teachers  the teacherss associated with the subject
	 *
	 * @return void saves lessons to the database
	 */
	private function saveLessonTeachers($subjectID, $teachers)
	{
		foreach ($teachers as $teacherID => $delta)
		{
			// If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
			$table = JTable::getInstance('lesson_teachers', 'thm_organizerTable');

			$data              = array();
			$data['subjectID'] = $subjectID;
			$data['teacherID'] = $teacherID;
			$table->load($data);

			// Delta will be 'calculated' later but explicitly overwritten now irregardless
			$data['delta'] = '';

			$success = $table->save($data);
			if (!$success)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');
				continue;
			}
		}
	}

	/**
	 * Attempts to associate subjects used in scheduling with their documentation
	 *
	 * @param string $planSubjectID the id of the subject in the plan_subjects table
	 * @param string $poolID        the id of the pool in the plan_pools table
	 * @param string $subjectNo     the subject id used in documentation
	 *
	 * @return void saves/updates a database entry
	 */
	private function savePlanSubjectMapping($planSubjectID, $poolID, $subjectNo)
	{
		$dbo = JFactory::getDbo();

		// Get the mapping boundaries for the program
		$boundariesQuery = $dbo->getQuery(true);
		$boundariesQuery->select('lft, rgt')
			->from('#__thm_organizer_mappings as m')
			->innerJoin('#__thm_organizer_programs as prg on m.programID = prg.id')
			->innerJoin('#__thm_organizer_plan_programs as p_prg on prg.id = p_prg.programID')
			->innerJoin('#__thm_organizer_plan_pools as p_pool on p_prg.id = p_pool.programID')
			->where("p_pool.id = '$poolID'");
		$dbo->setQuery((string) $boundariesQuery);

		try
		{
			$boundaries = $dbo->loadAssoc();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return;
		}

		if (empty($boundaries))
		{
			return;
		}

		// Get the id for the subject documentation
		$subjectQuery = $dbo->getQuery(true);
		$subjectQuery->select('subjectID')
			->from('#__thm_organizer_mappings as m')
			->innerJoin('#__thm_organizer_subjects as s on m.subjectID = s.id')
			->where("m.lft > '{$boundaries['lft']}'")
			->where("m.rgt < '{$boundaries['rgt']}'")
			->where("s.externalID = '$subjectNo'");
		$dbo->setQuery((string) $subjectQuery);

		try
		{
			$subjectID = $dbo->loadResult();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return;
		}

		if (empty($subjectID))
		{
			return;
		}

		$data  = array('subjectID' => $subjectID, 'plan_subjectID' => $planSubjectID);
		$table = JTable::getInstance('subject_mappings', 'thm_organizerTable');
		$table->load($data);
		$table->save($data);
	}
}