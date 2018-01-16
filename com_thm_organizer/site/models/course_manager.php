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
class THM_OrganizerModelCourse_Manager extends JModelForm
{
	/**
	 * Constructor to set up the config array and call the parent constructor
	 *
	 * @param array $config Configuration  (default: array)
	 */
	public function __construct($config = [])
	{
		$config['filter_fields'] = [
			'name',
			'email',
			'status_date',
			'status'
		];
		parent::__construct($config);
	}

	/**
	 * Method to select all existent assets from the database
	 *
	 * @return  JDatabaseQuery  A query object
	 */
	public function getParticipants()
	{
		$shortTag = THM_OrganizerHelperLanguage::getShortTag();
		$lessonID = JFactory::getApplication()->input->getInt('lessonID', 0);

		$query = $this->_db->getQuery(true);

		$select = 'CONCAT(pt.surname, ", ", pt.forename) as name, ul.*, pt.*';
		$select .= ',u.email, u.username, u.id as cid';
		$select .= ",p.name_$shortTag as program";

		$query->select($select);
		$query->from('#__thm_organizer_user_lessons as ul');
		$query->leftJoin('#__users as u on u.id = ul.userID');
		$query->leftJoin('#__thm_organizer_participants as pt on pt.id = ul.userID');
		$query->leftJoin('#__thm_organizer_programs as p on p.id = pt.programID');
		$query->where("ul.lessonID = '$lessonID'");
		$query->order('name ASC');

		$this->_db->setQuery($query);

		try
		{
			return $this->_db->loadAssocList();
		}
		catch (Exception $exception)
		{
			JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

			return [];
		}
	}

	/**
	 * Method to get the form
	 *
	 * @param   array   $data     Data for the form.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm|boolean  A JForm object on success, false on failure
	 */
	public function getForm($data = [], $loadData = true)
	{
		$form = $this->loadForm(
			"com_thm_organizer.course_manager",
			"course_manager",
			['control' => 'jform', 'load_data' => true]
		);

		return !empty($form) ? $form : false;
	}
}
