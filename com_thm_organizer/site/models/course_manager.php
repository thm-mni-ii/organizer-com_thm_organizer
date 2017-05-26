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
 * Class provides methods to get the neccessary data to display options for a course
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelCourse_Manager extends JModelList
{
	/**
	 * Constructor to set up the config array and call the parent constructor
	 *
	 * @param array $config Configuration  (default: array)
	 */
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
			'name',
			'email',
			'status_date',
			'status'
		);
		parent::__construct($config);
	}

	/**
	 * Method to select all existent assets from the database
	 *
	 * @return  JDatabaseQuery  A query object
	 */
	protected function getListQuery()
	{
		$shortTag = THM_OrganizerHelperLanguage::getShortTag();
		$lessonID = JFactory::getApplication()->input->getInt('lessonID', 0);

		$query = $this->_db->getQuery(true);

		$select = 'CONCAT(ud.surname, ", ", ud.forename) as name, ul.*, ud.*';
		$select .= ',u.email, u.username, u.id as cid';
		$select .= ",p.name_$shortTag as program";

		$query->select($select);
		$query->from('#__thm_organizer_user_lessons as ul');
		$query->leftJoin('#__users as u on u.id = ul.userID');
		$query->leftJoin('#__thm_organizer_user_data as ud on ud.userID = ul.userID');
		$query->leftJoin('#__thm_organizer_programs as p on p.id = ud.programID');
		$query->where("ul.lessonID = '$lessonID'");
		$query->order(
			$this->getState('list.ordering', 'user_date') . ' ' .
			$this->getState('list.direction', 'ASC')
		);

		return $query;
	}

	/**
	 * Method to get the form
	 *
	 * @return  mixed  JForm object on success, False on error.
	 */
	public function getForm()
	{
		$form = $this->loadForm(
			"com_thm_organizer.course_manager",
			"course_manager",
			array('control' => 'jform', 'load_data' => true)
		);

		return !empty($form) ? $form : false;
	}
}
