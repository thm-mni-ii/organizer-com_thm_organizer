<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Provides helper methods for preparatory course information
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperCourse
{
	/**
	 * Creates a button for user interaction with the course. (De-/registration, Administration)
	 *
	 * @param string $view     the view to be redirected to after registration action
	 * @param int    $lessonID the id of the course
	 * @param bool   $admin    whether or not the user has administrative access to the course
	 * @param bool   $expired  whether or not the course has expired
	 *
	 * @return string the HTML for the action button as appropriate for the user
	 *
	 * @since version
	 * @throws Exception
	 */
	public static function getActionButton($view, $lessonID, $admin, $expired)
	{
		$lang            = THM_OrganizerHelperLanguage::getLanguage();
		$shortTag        = THM_OrganizerHelperLanguage::getShortTag();
		$pathPrefix      = "index.php?option=com_thm_organizer";
		$registrationURL = "$pathPrefix&task=$view.register&languageTag=$shortTag";
		$registrationURL .= empty($menuID) ? '' : "&Itemid=$menuID";
		$registrationURL .= $view == 'subject' ? '&id=' . JFactory::getApplication()->input->getInt('id', 0) : "";
		$manage          = '<span class="icon-cogs"></span>' . $lang->_("COM_THM_ORGANIZER_MANAGE");
		$managerURL      = "{$pathPrefix}&view=course_manager&languageTag=$shortTag";
		$managerURL      .= empty($menuID) ? '' : "&Itemid=$menuID";

		if (!empty(JFactory::getUser()->id))
		{
			$lessonURL = "&lessonID=$lessonID";

			if ($admin)
			{
				$managerRoute = JRoute::_($managerURL . $lessonURL);
				$register     = "<a class='btn' href='$managerRoute'>$manage</a>";
			}
			else
			{
				$regState = THM_OrganizerHelperCourse::getUserState($lessonID);

				if ($expired)
				{
					$register = '';
				}
				else
				{
					$registerRoute = JRoute::_($registrationURL . $lessonURL);
					$disabled      = THM_OrganizerHelperCourse::isRegistrationOpen($lessonID) ? '' : 'disabled';

					if (!empty($regState))
					{
						$registerText = '<span class="icon-out-2"></span>' . $lang->_('COM_THM_ORGANIZER_COURSE_DEREGISTER');
					}
					else
					{
						$registerText = '<span class="icon-apply"></span>' . $lang->_('COM_THM_ORGANIZER_COURSE_REGISTER');
					}

					$register = "<a class='btn' href='$registerRoute' class='$disabled' type='button'>$registerText</a>";
				}
			}
		}
		else
		{
			$register = '';
		}

		return $register;
	}

	/**
	 * Loads course information from the database
	 *
	 * @param null $lessonID int id of requested lesson
	 *
	 * @return  array  with course data on success, otherwise empty
	 */
	public static function getCourse($lessonID = 0)
	{
		$lessonID = JFactory::getApplication()->input->getInt('lessonID', $lessonID);

		if (empty($lessonID))
		{
			return [];
		}

		$shortTag = THM_OrganizerHelperLanguage::getShortTag();

		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select('pp.name as planningPeriodName')
			->select('l.id, l.max_participants as lessonP')
			->select("s.id as subjectID, s.name_$shortTag as name, s.instructionLanguage, s.max_participants as subjectP");

		$query->from('#__thm_organizer_lessons AS l');
		$query->leftJoin('#__thm_organizer_lesson_subjects AS ls ON ls.lessonID = l.id');
		$query->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ls.subjectID');
		$query->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id');
		$query->leftJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id');
		$query->leftJoin('#__thm_organizer_planning_periods AS pp ON l.planningPeriodID = pp.id');
		$query->where("l.id = '$lessonID'");

		$dbo->setQuery($query);

		try
		{
			$courseData = $dbo->loadAssoc();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

			return [];
		}

		return empty($courseData) ? [] : $courseData;
	}

	/**
	 * Creates a display of formatted dates for a course
	 *
	 * @param int $lessonID the id of the course to be loaded
	 *
	 * @return  string the dates to display
	 */
	public static function getDateDisplay($lessonID = 0)
	{
		$lessonID = JFactory::getApplication()->input->getInt('lessonID', $lessonID);

		$dates = THM_OrganizerHelperCourse::getDates($lessonID);

		if (!empty($dates))
		{
			$dateFormat = JComponentHelper::getParams('com_thm_organizer')->get('dateFormat', 'd.m.Y');
			$start      = JHtml::_('date', $dates[0]["schedule_date"], $dateFormat);
			$end        = JHtml::_('date', end($dates)["schedule_date"], $dateFormat);

			return "$start - $end";
		}

		return '';
	}

	/**
	 * Loads all calendar information for specific course  from the database
	 *
	 * @param int $lessonID id of course to be loaded
	 *
	 * @return  array  array with calendar registration data on success, otherwise empty
	 */
	public static function getDates($lessonID = 0)
	{
		$lessonID = JFactory::getApplication()->input->getInt('lessonID', $lessonID);

		if (empty($lessonID))
		{
			return [];
		}

		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select('*');
		$query->from('#__thm_organizer_lessons AS l');
		$query->leftJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id');
		$query->where("l.id = '$lessonID'");
		$query->order('c.schedule_date');

		$dbo->setQuery($query);

		try
		{
			$dates = $dbo->loadAssocList();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

			return [];
		}

		return empty($dates) ? [] : $dates;
	}

	/**
	 * Loads all all participants for specific course from database
	 *
	 * @param int $lessonID id of course to be loaded
	 *
	 * @return  array  with course registration data on success, otherwise empty
	 */
	public static function getFullParticipantData($lessonID = 0, $includeWaitList = false)
	{
		if (empty($lessonID))
		{
			return [];
		}

		$shortTag = THM_OrganizerHelperLanguage::getShortTag();

		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);

		$select = 'CONCAT(ud.surname, ", ", ud.forename) AS name , ud.address, ud.zip_code, ud.city';
		$select .= ",p.name_$shortTag as programName, d.short_name_$shortTag as departmentName";
		$select .= ',u.id, u.email';

		$query->select($select);
		$query->from('#__thm_organizer_user_lessons AS ul');
		$query->leftJoin('#__users AS u ON u.id = ul.userID');
		$query->leftJoin('#__thm_organizer_user_data AS ud ON u.id = ud.userID');
		$query->leftJoin('#__thm_organizer_programs AS p ON p.id = ud.programID');
		$query->leftJoin('#__thm_organizer_departments AS d ON p.departmentID = d.id');
		$query->where("ul.lessonID = '$lessonID'");

		if (!$includeWaitList)
		{
			$query->where("ul.status = '1'");
		}

		$query->order('u.name');

		$dbo->setQuery($query);

		try
		{
			$participantData = $dbo->loadAssocList();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

			return [];
		}

		return empty($participantData) ? [] : $participantData;
	}

	/**
	 * Loads course information from the database
	 *
	 * @param null $lessonID int id of requested lesson
	 *
	 * @return  array  with course data on success, otherwise empty
	 */
	public static function getLatestCourse($subjectID)
	{
		if (empty($subjectID))
		{
			return [];
		}

		$shortTag = THM_OrganizerHelperLanguage::getShortTag();

		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select('l.id, l.max_participants as lessonP')
			->select("s.id as subjectID, s.name_$shortTag as name, s.instructionLanguage, s.max_participants as subjectP")
			->select('pp.name as planningPeriodName');

		$query->from('#__thm_organizer_lessons AS l');
		$query->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.lessonID = l.id');
		$query->innerJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ls.subjectID');
		$query->innerJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id');
		$query->innerJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id');
		$query->innerJoin('#__thm_organizer_planning_periods AS pp ON l.planningPeriodID = pp.id');
		$query->where("s.id = '$subjectID'");
		$query->order('schedule_date DESC');

		$dbo->setQuery($query);

		try
		{
			$courseData = $dbo->loadAssoc();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

			return [];
		}

		return empty($courseData) ? [] : $courseData;
	}

	/**
	 * Get list of registered students in specific course
	 *
	 * @param int $lessonID identifier of course
	 * @param int $status   status of Students (1 registered, 0 waiting, 2 all)
	 *
	 * @return mixed list of students in course with $id, false on error
	 */
	public static function getRegisteredStudents($lessonID = 0, $status = 1)
	{
		if (empty($lessonID))
		{
			return [];
		}

		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select('*');
		$query->from('#__thm_organizer_user_lessons');
		$query->where("lessonID = '$lessonID'");

		if ($status === 0 || $status === 1)
		{
			$query->where("status = '$status'");
		}

		$dbo->setQuery($query);

		try
		{
			$regStudents = $dbo->loadAssocList();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

			return [];
		}

		return empty($regStudents) ? [] : $regStudents;
	}

	/**
	 * Creates a status display for the user's relation to the respective course.
	 *
	 * @param int  $lessonID the id of the course
	 * @param bool $admin    whether or not the user is authorized to administrate the course
	 * @param bool $expired  whether or not the course has already expired
	 *
	 * @return string the HTML for the status display
	 */
	public static function getStatusDisplay($lessonID, $admin, $expired)
	{
		$lang = THM_OrganizerHelperLanguage::getLanguage();

		// Personal Status
		$none        = $expired ? '-' : $lang->_('COM_THM_ORGANIZER_COURSE_NOT_REGISTERED');
		$notLoggedIn = '<span class="icon-warning"></span>' . $lang->_('COM_THM_ORGANIZER_NOT_LOGGED_IN');
		$waitList    = '<span class="icon-checkbox-partial"></span>' . $lang->_('COM_THM_ORGANIZER_WAIT_LIST');
		$registered  = '<span class="icon-checkbox-checked"></span>' . $lang->_('COM_THM_ORGANIZER_COURSE_REGISTERED');

		if (!empty(JFactory::getUser()->id))
		{
			if ($admin)
			{
				$userStatus = $lang->_('COM_THM_ORGANIZER_COURSE_ADMINISTRATOR');
			}
			else
			{
				$regState = THM_OrganizerHelperCourse::getUserState($lessonID);

				if (empty($regState))
				{
					$text = $none;
				}
				else
				{
					$text = empty($regState["status"]) ? $waitList : $registered;
				}

				$disabled   = '<span class="disabled">%s</span>';
				$userStatus = $expired ? sprintf($disabled, $text) : $text;
			}
		}
		else
		{
			$userStatus = $expired ? $none : '<span class="disabled">' . $notLoggedIn . '</span>';
		}

		return $userStatus;
	}

	/**
	 * Figure out if student is signed into course
	 *
	 * @param int $lessonID of lesson
	 *
	 * @return array containing the user specific information or empty on error
	 */
	public static function getUserState($lessonID = 0)
	{
		$userID   = JFactory::getUser()->id;
		$lessonID = empty($lessonID) ? JFactory::getApplication()->input->getInt('lessonID', 0) : $lessonID;

		if (empty($lessonID) || empty($userID))
		{
			return [];
		}

		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select("*");
		$query->from("#__thm_organizer_user_lessons");
		$query->where("userID = '$userID' AND lessonID = '$lessonID'");

		$dbo->setQuery($query);

		try
		{
			$regState = $dbo->loadAssoc();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

			return [];
		}

		return empty($regState) ? [] : $regState;
	}

	/**
	 * Check if course with specific id is full
	 *
	 * @param int $lessonID identifier of course
	 *
	 * @return bool true when course is full, false otherwise
	 */
	public static function isCourseFull($lessonID)
	{
		$course      = self::getCourse($lessonID);
		$regStudents = false;
		$maxPart     = 0;

		if (!empty($course))
		{
			$maxPart     = empty($course["lessonP"]) ? $course["subjectP"] : $course["lessonP"];
			$regStudents = self::getRegisteredStudents($lessonID);
		}

		return $regStudents ? ($maxPart - sizeof($regStudents) <= 0) : false;
	}

	/**
	 * Check if the course is open for registration
	 *
	 * @param int $lessonID id of lesson
	 *
	 * @return bool true if registration deadline not yet in the past, false otherwise
	 */
	public static function isRegistrationOpen($lessonID = 0)
	{
		$dates    = self::getDates($lessonID);
		$now      = new DateTime;
		$deadline = JComponentHelper::getParams('com_thm_organizer')->get('deadline', '5');
		$now->add(new DateInterval("P{$deadline}D"));

		return sizeof($dates) > 0 && new DateTime($dates[0]["schedule_date"]) > $now;
	}

	/**
	 * Get formatted array with all prep courses in format id => name
	 *
	 * @return  array  assoc array with all prep courses with id => name
	 */
	public static function prepCourseList()
	{
		$shortTag = THM_OrganizerHelperLanguage::getShortTag();

		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select("id, name_$shortTag AS name");
		$query->from("#__thm_organizer_subjects");
		$query->where("is_prep_course = '1'");

		$dbo->setQuery($query);

		try
		{
			$courses = $dbo->loadAssocList();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

			return [];
		}

		if (empty($courses))
		{
			return [];
		}

		$return = [];
		foreach ($courses as $course)
		{
			$return[$course["id"]] = $course["name"];
		}

		return $return;
	}

	/**
	 * Check if user is registered as a teacher, optionally for a specific course
	 *
	 * @param int $subjectID id of course
	 *
	 * @return  boolean if user is authorized
	 */
	public static function teachesCourse($subjectID = 0)
	{
		$userName = JFactory::getUser()->username;

		if (empty($userName))
		{
			return false;
		}

		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select("COUNT(*)");
		$query->from('#__thm_organizer_subject_teachers AS st');
		$query->leftJoin('#__thm_organizer_teachers AS t ON t.id = st.teacherID');
		$query->where("t.username = '$userName'");

		if (!empty($subjectID))
		{
			$query->where("st.subjectID = '$subjectID'");
		}

		$dbo->setQuery($query);

		try
		{
			$teachesCount = $dbo->loadResult();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

			return false;
		}

		return !empty($teachesCount);
	}
}
