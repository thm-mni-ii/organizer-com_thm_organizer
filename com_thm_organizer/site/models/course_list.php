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
	 * Constructor
	 *
	 * @param array $config An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function __construct($config = [])
	{
		parent::__construct();
		$this->populateState();
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

		if (empty($formData["filter_subject"]))
		{
			$formData["filter_subject"] = "0";
		}

		if (empty($formData["filter_active"]))
		{
			$formData["filter_active"] = "0";
		}

		$this->state->set('filter_active', $formData['filter_active']);
		$this->state->set('filter_subject', $formData['filter_subject']);
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery  A JDatabaseQuery object to retrieve the data set.
	 */
	protected function getListQuery()
	{
		$shortTag = THM_OrganizerHelperLanguage::getShortTag();
		$state    = self::getState();

		$query    = $this->_db->getQuery(true);
		$subQuery = $this->_db->getQuery(true);

		$subQuerySelect = "lessonID";
		$subQuerySelect .= ", MIN(schedule_date) as start, MAX(schedule_date) as end";
		$subQuerySelect .= ", (MAX(schedule_date) < CURRENT_DATE()) as expired";

		$subQuery->select($subQuerySelect);
		$subQuery->from('#__thm_organizer_calendar');
		$subQuery->group("lessonID");

		$select = 's.*, s.id as subjectID, ls.lessonID';
		$select .= ",s.name_$shortTag as name";
		$select .= ", c.start, c.end, c.expired";

		$query->select($select);
		$query->from('#__thm_organizer_subjects as s');
		$query->leftJoin('#__thm_organizer_subject_mappings as sm on sm.subjectID = s.id');
		$query->leftJoin('#__thm_organizer_lesson_subjects as ls on ls.subjectID = sm.plan_subjectID');
		$query->leftJoin("($subQuery) as c on c.lessonID = ls.lessonID");
		$query->where("is_prep_course = '1' and ls.subjectID is not null and c.start is not null");
		$query->order("end DESC, name ASC");

		switch ($state->filter_active)
		{
			case "0":
				$query->where("c.expired = '0'");
				break;
			case "2":
				$query->where("c.expired = '1'");
				break;
		}

		if (($state->filter_subject !== "0"))
		{
			$query->where("s.id = '{$state->filter_subject}'");
		}

		return $query;
	}
}
