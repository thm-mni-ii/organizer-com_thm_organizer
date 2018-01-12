<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/campuses.php';

/**
 * Class provides methods for handling the prep course list
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelCourse_List extends JModelList
{
	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		$courses = parent::getItems();

		$maxValues = [];

		foreach ($courses AS $index => &$course)
		{
			if ($this->state->filter_status == 'current')
			{
				if (isset($maxValues[$course->subjectID]))
				{
					if ($maxValues[$course->subjectID]['start'] > $course->start)
					{
						unset($courses[$index]);
						continue;
					}
					else
					{
						$oldIndex = $maxValues[$course->subjectID]['index'];
						unset($courses[$oldIndex]);
					}
				}
			}

			$course->campus = THM_OrganizerHelperCourse::getCampus($course);

			$maxValues[$course->subjectID] = ['start' => $course->start, 'index' => $index];
		}

		return $courses;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery  A JDatabaseQuery object to retrieve the data set.
	 */
	protected function getListQuery()
	{
		$tag = THM_OrganizerHelperLanguage::getShortTag();

		$courseQuery = $this->_db->getQuery(true);

		$subQuery = $this->_db->getQuery(true);

		$subQuery->select('lessonID, MIN(schedule_date) as start, MAX(schedule_date) as end')
			->select('(MAX(schedule_date) < CURRENT_DATE()) as expired')
			->from('#__thm_organizer_calendar')
			->group('lessonID');

		$courseQuery->select("s.id as subjectID, ls.lessonID, s.name_$tag as name, sq.start, sq.end, sq.expired");
		$courseQuery->select("l.campusID AS campusID, s.campusID AS abstractCampusID");
		$courseQuery->from('#__thm_organizer_subjects as s');
		$courseQuery->innerJoin('#__thm_organizer_subject_mappings as sm on sm.subjectID = s.id');
		$courseQuery->innerJoin('#__thm_organizer_lesson_subjects as ls on ls.subjectID = sm.plan_subjectID');
		$courseQuery->innerJoin('#__thm_organizer_lessons as l on ls.lessonID = l.id');
		$courseQuery->innerJoin("($subQuery) as sq on sq.lessonID = ls.lessonID");
		$courseQuery->where("is_prep_course = '1' and ls.subjectID is not null and sq.start is not null");
		$courseQuery->order("end DESC, name ASC");

		switch ($this->state->filter_status)
		{
			case "pending":
				$courseQuery->where("sq.expired = '0'");
				break;
			case "expired":
				$courseQuery->where("sq.expired = '1'");
				break;
		}

		if (!empty($this->state->filter_subject))
		{
			$courseQuery->where("s.id = '{$this->state->filter_subject}'");
		}

		if (!empty($this->state->filter_campus))
		{
			$campusID = $this->state->filter_campus;
			$courseQuery->leftJoin('#__thm_organizer_campuses as c on s.campusID = c.id');
			$campusConditions = "(l.campusID = '$campusID' OR (l.campusID IS NULL AND ";
			$campusConditions .= "(c.id = '$campusID' OR c.parentID = '$campusID' OR s.campusID IS NULL)))";
			$courseQuery->where($campusConditions);
		}

		return $courseQuery;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param string $ordering  An optional ordering field.
	 * @param string $direction An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$formData = JFactory::getApplication()->input->get('jform', [], 'array');

		$menu = JFactory::getApplication()->getMenu()->getActive();

		if (empty($menu))
		{
			$defaultCampusID =  0;
			$campusID   = !isset($formData["filter_campus"]) ? $defaultCampusID : $formData["filter_campus"];
			$showPrepCourses = 1;
		}
		else
		{
			$defaultCampusID =  $menu->params->get('campusID', 0);
			$campusID   = !isset($formData["filter_campus"]) ? $defaultCampusID : $formData["filter_campus"];
			$showPrepCourses =  $menu->params->get('show_prep_courses', 1);
		}

		$this->state->set('filter_campus', $campusID);
		$this->state->set('filter_prep_courses', $showPrepCourses);

		$status   = empty($formData["filter_status"]) ? 'current' : $formData["filter_status"];
		$this->state->set('filter_status', $status);

		$subject = empty($formData["filter_subject"]) ? 0 : (int) $formData["filter_subject"];
		$this->state->set('filter_subject', $subject);
	}
}
