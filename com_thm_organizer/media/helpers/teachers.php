<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperTeachers
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once 'departments.php';

/**
 * Provides validation methods for xml teacher objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperTeachers
{
	/**
	 * Checks for multiple teacher entries (responsibilities) for a subject and removes the lesser
	 *
	 * @param array &$list the list of teachers responsilbe for a subject
	 *
	 * @return  void  removes duplicate list entries dependent on responsibility
	 */
	private static function ensureUnique(&$list)
	{
		$keysToIds = [];
		foreach ($list as $key => $item)
		{
			$keysToIds[$key] = $item['id'];
		}

		$valueCount = array_count_values($keysToIds);
		foreach ($list as $key => $item)
		{
			$unset = ($valueCount[$item['id']] > 1 AND $item['teacherResp'] > 1);
			if ($unset)
			{
				unset($list[$key]);
			}
		}
	}

	/**
	 * Retrieves the teacher responsible for the subject's development
	 *
	 * @param int  $subjectID      the subject's id
	 * @param int  $responsibility represents the teacher's level of
	 *                             responsibility for the subject
	 * @param bool $multiple       whether or not multiple results are desired
	 * @param bool $unique         whether or not unique results are desired
	 *
	 * @return  array  an array of teacher data
	 */
	public static function getDataBySubject($subjectID, $responsibility = null, $multiple = false, $unique = true)
	{
		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select("t.id, t.surname, t.forename, t.title, t.username, u.id AS userID, teacherResp, gpuntisID");
		$query->from('#__thm_organizer_teachers AS t');
		$query->innerJoin('#__thm_organizer_subject_teachers AS st ON t.id = st.teacherID ');
		$query->leftJoin('#__users AS u ON t.username = u.username');
		$query->where("st.subjectID = '$subjectID' ");

		if (!empty($responsibility))
		{
			$query->where("st.teacherResp = '$responsibility'");
		}

		$query->order('surname ASC');
		$dbo->setQuery($query);

		if ($multiple)
		{
			try
			{
				$teacherList = $dbo->loadAssocList();
			}
			catch (Exception $exc)
			{
				JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

				return [];
			}

			if ($unique)
			{
				self::ensureUnique($teacherList);
			}

			return $teacherList;
		}

		try
		{
			return $dbo->loadAssoc();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return [];
		}
	}

	/**
	 * Generates a default teacher text based upon organizer's internal data
	 *
	 * @param int $teacherID the teacher's id
	 *
	 * @return  string  the default name of the teacher
	 */
	public static function getDefaultName($teacherID)
	{
		$teacher = JTable::getInstance('teachers', 'thm_organizerTable');
		$teacher->load($teacherID);

		$return = '';
		if (!empty($teacher->id))
		{
			$title    = empty($teacher->title) ? '' : "{$teacher->title} ";
			$forename = empty($teacher->forename) ? '' : "{$teacher->forename} ";
			$surname  = $teacher->surname;
			$return   .= $title . $forename . $surname;
		}

		return $return;
	}

	/**
	 * Generates a preformatted teacher text based upon organizer's internal data
	 *
	 * @param int   $teacherID the teacher's id
	 * @param  bool $short     Whether or not the teacher's forename should be abbrevieated
	 *
	 * @return  string  the default name of the teacher
	 */
	public static function getLNFName($teacherID, $short = false)
	{
		$teacher = JTable::getInstance('teachers', 'thm_organizerTable');
		$teacher->load($teacherID);

		$return = '';
		if (!empty($teacher->id))
		{
			if (!empty($teacher->forename))
			{
				// Getting the first letter by other means can cause encoding problems with 'interesting' first names.
				$forename = $short ? mb_substr($teacher->forename, 0, 1) . '.' : $teacher->forename;
			}
			$return = $teacher->surname;
			$return .= empty($forename) ? '' : ", $forename";
		}

		return $return;
	}

	/**
	 * Checks for the teacher entry in the database, creating it as necessary. Adds the id to the teacher entry in the
	 * schedule.
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param string $gpuntisID      the teacher's gpuntis ID
	 *
	 * @return  int the id of the teacher on success, otherwise 0
	 */
	public static function getID($gpuntisID, $data)
	{
		$teacherTable   = JTable::getInstance('teachers', 'thm_organizerTable');
		$loadCriteria   = [];
		$loadCriteria[] = ['gpuntisID' => $gpuntisID];

		if (!empty($data->username))
		{
			$loadCriteria[] = ['username' => $data->username];
		}
		if (!empty($data->forename))
		{
			$loadCriteria[] = ['surname' => $data->surname, 'forename' => $data->forename];
		}

		foreach ($loadCriteria as $criteria)
		{
			try
			{
				$success = $teacherTable->load($criteria);
			}
			catch (Exception $exc)
			{
				JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

				return 0;
			}

			if ($success)
			{
				return $teacherTable->id;
			}
		}

		// Entry not found
		$success = $teacherTable->save($data);

		return $success ? $teacherTable->id : 0;
	}

	/**
	 * Getter method for teachers in database. Only retrieving the IDs here allows for formatting the names according to
	 * the needs of the calling views.
	 *
	 * @param bool $short whether or not abbreviated names should be returned
	 *
	 * @return string  all pools in JSON format
	 *
	 * @throws RuntimeException
	 */
	public static function getPlanTeachers($short = true)
	{
		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select("DISTINCT lt.teacherID");
		$query->from('#__thm_organizer_lesson_teachers AS lt');

		$input               = JFactory::getApplication()->input;
		$selectedDepartments = $input->getString('departmentIDs');
		$selectedPrograms    = $input->getString('programIDs');

		if (!empty($selectedDepartments))
		{
			$query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.teacherID = lt.teacherID');
			$departmentIDs = "'" . str_replace(',', "', '", $selectedDepartments) . "'";
			$query->where("dr.departmentID IN ($departmentIDs)");
		}

		if (!empty($selectedPrograms))
		{
			$programIDs = "'" . str_replace(',', "', '", $selectedPrograms) . "'";
			$query->innerJoin('#__thm_organizer_lesson_subjects AS ls ON lt.subjectID = ls.id');
			$query->innerJoin('#__thm_organizer_lesson_pools AS lp ON lp.subjectID = ls.id');
			$query->innerJoin('#__thm_organizer_plan_pools AS ppo ON lp.poolID = ppo.id');
			$query->where("ppo.programID in ($programIDs)");
		}

		$dbo->setQuery($query);

		$default = [];
		try
		{
			$teacherIDs = $dbo->loadColumn();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return $default;
		}

		if (empty($teacherIDs))
		{
			return $default;
		}

		$teachers = [];
		foreach ($teacherIDs as $teacherID)
		{
			$name            = THM_OrganizerHelperTeachers::getLNFName($teacherID, $short);
			$teachers[$name] = $teacherID;
		}

		ksort($teachers);

		return $teachers;
	}

	/**
	 * Checks whether the teacher is associated with lessons
	 *
	 * @param string $table     the dynamic part of the table name
	 * @param int    $teacherID the id of the teacher being checked
	 *
	 * @return bool true if the teacher is assigned to a lesson
	 */
	public static function teaches($table, $teacherID)
	{
		if (empty($table))
		{
			return false;
		}

		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select("COUNT(*)")->from("#__thm_organizer_{$table}_teachers")->where("teacherID = '$teacherID'");
		$dbo->setQuery($query);

		try
		{
			$number = $dbo->loadResult();
		}
		catch (Exception $exception)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return false;
		}

		return empty($number) ? false : true;
	}
}
