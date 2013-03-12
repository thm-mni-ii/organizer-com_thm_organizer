<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelAssets
 * @description THM_OrganizerModelAssets component admin model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Class THM_OrganizerModelAssets for component com_thm_organizer
 *
 * Class provides methods to deal with assets
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerModelAssets extends JModelList
{
	/**
	 * Constructor to set up the config array and call the parent constructor
	 *
	 * @param   Array  $config  Configuration  (default: Array)
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
					'id', 'id'
			);
		}
		parent::__construct($config);
	}

	/**
	 * Method to select all existent assets from the database
	 *
	 * @return  Object  A query object
	 */
	protected function getListQuery()
	{
		$dbo = JFactory::getDBO();

		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$type = $this->state->get('filter.type');

		// Defailt ordering
		if ($orderCol == "")
		{
			$orderCol = "asset_id";
			$orderDirn = "asc";
		}

		// Create the sql query
		$query = $dbo->getQuery(true);
		$query->select("*");
		$query->select(" #__thm_organizer_asset_types.name as coursetype");
		$query->select(" #__thm_organizer_assets.name as asset_name");
		$query->select(" #__thm_organizer_assets.id as asset_id");
		$query->from('#__thm_organizer_assets');
		$query->innerJoin('#__thm_organizer_asset_types ON #__thm_organizer_assets.asset_type_id = #__thm_organizer_asset_types.id');

		$search = $dbo->Quote('%' . $dbo->getEscaped($this->state->get('filter.search'), true) . '%');
		$searchClause = "(title_de LIKE '$search' ";
		$searchClause .= "OR title_en LIKE ' . $search . ' ";
		$searchClause .= "OR short_title_de LIKE '$search' ";
		$searchClause .= "OR short_title_en LIKE '$search' ";
		$searchClause .= "OR abbreviation LIKE '$search') ";
		$query->where($searchClause);

		if (isset($type) && $type != "")
		{
			$query->where(' asset_type_id = ' . $type);
		}

		$query->order($orderCol . " " . $orderDirn);

		return $query;
	}

	/**
	 * Method to get the table
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		$layout = JRequest::getVar('layout');
		if (!empty($layout))
		{
			$this->context .= '.' . $layout;
		}

		$order = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '');
		$dir = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '');
		$filter = $app->getUserStateFromRequest($this->context . '.filter', 'filter', '');
		$limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', '');
		$search = $app->getUserStateFromRequest($this->context . '.filter_search', 'filter_search', '');
		$type = $app->getUserStateFromRequest($this->context . '.filter_type', 'filter_type', '');

		$this->setState('list.ordering', $order);
		$this->setState('filter.search', $search);
		$this->setState('filter.type', $type);
		$this->setState('list.direction', $dir);
		$this->setState('filter', $filter);
		$this->setState('limit', $limit);

		// Set the default ordering behaviour
		if ($order == '' && isset($order))
		{
			parent::populateState("id", "ASC");
		}
		else
		{
			parent::populateState($order, $dir);
		}

		parent::populateState($order, $dir);
	}

	/**
	 *
	 * Writes a given course to the database. Return the inserted ID.
	 * @param Array $data
	 *
	 * */
	/**
	 * Method to write a given course to the database
	 *
	 * @param   Array  $data  Course data
	 *
	 * @return  Integer  Return the inserted id
	 */
	public function insertCourse($data)
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

		// Prepare the local data
		$lsf_course_id = $data['lsf_course_id'];
		$lsf_course_code = $data['lsf_course_code'];
		$his_course_code = $data['his_course_code'];
		$title_de = $data['title_de'];
		$title_en = $data['title_en'];
		$creditpoints = $data['creditpoints'];
		$short_title_de = $data['short_title_de'];
		$short_title_en = $data['short_title_en'];
		$abbreviation = $data['abbreviation'];

		// Create the insert query
		$query->insert('#__thm_organizer_assets');
		$query->set("lsf_course_id = $lsf_course_id");
		$query->set("lsf_course_code = '$lsf_course_code'");
		$query->set("his_course_code = '$his_course_code'");
		$query->set("title_de = '$title_de'");
		$query->set("title_en = '$title_en'");
		$query->set("max_creditpoints = '$creditpoints'");
		$query->set("min_creditpoints = '$creditpoints'");
		$query->set("short_title_de = '$short_title_de'");
		$query->set("short_title_en = '$short_title_en'");
		$query->set("abbreviation = '$abbreviation'");
		$query->set("asset_type_id = 1");

		$dbo->setQuery($query);
		$dbo->query();

		return $dbo->insertid();
	}

	/**
	 * Method to update the course data
	 *
	 * @param   Array  $data  Course data
	 *
	 * @return  Integer  Return the inserted id
	 */
	public function updateCourse($data)
	{
		// Prepare the local data
		$lsf_course_id = $data['lsf_course_id'];
		$lsf_course_code = $data['lsf_course_code'];
		$his_course_code = $data['his_course_code'];
		$title_de = $data['title_de'];
		$title_en = $data['title_en'];
		$creditpoints = $data['creditpoints'];
		$short_title_de = $data['short_title_de'];
		$short_title_en = $data['short_title_en'];
		$abbreviation = $data['abbreviation'];

		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

		// Create the insert query
		$query->update('#__thm_organizer_assets');
		$query->set("lsf_course_code = '$lsf_course_code'");
		$query->set("his_course_code = '$his_course_code'");
		$query->set("max_creditpoints = '$creditpoints'");
		$query->set("min_creditpoints = '$creditpoints'");
		$query->set("asset_type_id = 1");

		if (!empty($title_de))
		{
			$query->set("title_de = '$title_de'");
		}

		if (!empty($title_en))
		{
			$query->set("title_en = '$title_en'");
		}

		if (!empty($short_title_de))
		{
			$query->set("short_title_de = '$short_title_de'");
		}

		if (!empty($short_title_en))
		{
			$query->set("short_title_en = '$short_title_en'");
		}

		if (!empty($abbreviation))
		{
			$query->set("abbreviation = '$abbreviation'");
		}

		$query->where("lsf_course_id = $lsf_course_id");
		$dbo->setQuery($query);
		$dbo->query();

		return $dbo->insertid();
	}

	/**
	 * Method to check the inserted courses by a user
	 *
	 * @param   Integer  $userId  User id
	 *
	 * @return  Integer  The row id or 0 if no row was found
	 */
	public function isInsertedCourse($userId)
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

		// Determine the concerned database rows
		$query->select("*");
		$query->from('#__thm_organizer_assets');
		$query->where("lsf_course_id = '$userId'");
		$dbo->setQuery($query);
		$rows = $dbo->loadObjectList();

		if (isset($rows))
		{
			return $rows[0]->id;
		}
		else
		{
			return 0;
		}
	}

	/**
	 *
	 * Inserts a given mapping of a lecturer to a specific course
	 * @param Array $data
	 */
	/**
	 * Method to get the table
	 *
	 * @param   Array  $data  Course lecturer data
	 *
	 * @return  void
	 */
	public function insertCourseLecturer($data)
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

		// Prepare the data
		$modul_id = $data['modul_id'];
		$lecturer_id = $data['lecturer_id'];
		$lecturer_type = $data['lecturer_type'];

		// Set the sql query
		$query->insert('#__thm_organizer_lecturers_assets');
		$query->set("modul_id = $modul_id");
		$query->set("lecturer_id = $lecturer_id");
		$query->set("lecturer_type = $lecturer_type");

		$dbo->setQuery($query);
		$dbo->query();
	}

	/**
	 *
	 * Inserts a given mapping of a lecturer to a specific course
	 * @param Array $data
	 */
	/**
	 * Method to insert a given mapping of a lecturer to a specific course
	 *
	 * @param   Array  $data  Lecturer data
	 *
	 * @return  void
	 */
	public function updateCourseLecturer($data)
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

		// Prepare the data
		$modul_id = $data['modul_id'];
		$lecturer_id = $data['lecturer_id'];
		$lecturer_type = $data['lecturer_type'];

		// Set the sql query
		$query->insert('#__thm_organizer_lecturers_assets');
		$query->set("lecturer_id = $lecturer_id");
		$query->set("lecturer_type = $lecturer_type");
		$query->where("modul_id = $modul_id");

		$dbo->setQuery($query);
		$dbo->query();
	}

	/**
	 * Method to clear all lecturer mappings
	 *
	 * @param   Integer  $modulID       Module id
	 * @param   String   $lecturerType  Lecturer type
	 *
	 * @return  void
	 */
	public function clearAllLecturerMappings($modulID, $lecturerType)
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

		// Determine the concerned database rows
		$query->from("#__thm_organizer_lecturers_assets");
		$query->delete();
		$query->where("modul_id=" . $modulID);
		$query->where("lecturer_type=" . $lecturerType);
		$dbo->setQuery($query);
		$dbo->query();
	}

	/**
	 *
	 * Writes a given person as a course lecturer. Returns the inserted id
	 * @param Array $data
	 */
	/**
	 * Method to write a given person as a course lecturer. Returns the inserted id
	 *
	 * @param   Array  $data  Lecturer data
	 *
	 * @return  mixed  The insert id (Integer) or an empty string ("")
	 */
	public function insertLecturer($data)
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

		// Prepare the data
		$userid = $data['userid'];
		$surname = $data['surname'];
		$forename = $data['forename'];
		$academic_title = $data['academic_title'];

		// Set the sql query
		$query->insert('#__thm_organizer_lecturers');
		$query->set("userid = '$userid'");
		$query->set("surname = '$surname'");
		$query->set("forename = '$forename'");
		$query->set("academic_title = '$academic_title'");

		$dbo->setQuery($query);
		$queryValue = $dbo->query();

		return ($queryValue)? $dbo->insertid() : "";
	}

	/**
	 * Returns the id of a already inserted lecturer
	 *
	 * @param   String  $forename  Forename
	 * @param   String  $surname   Surname
	 *
	 * @return  Integer  The inserted id
	 */
	public function isInserted($forename, $surname)
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

		// Determine the concerned database rows
		$query->select("*");
		$query->from('#__thm_organizer_lecturers');
		$query->where("forename = '$forename'");
		$query->where("surname = '$surname'");
		$dbo->setQuery($query);
		$rows = $dbo->loadObjectList();

		if (isset($rows))
		{
			return $rows[0]->id;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Method to insert a certain course to the database
	 *
	 * @param   Integer  $courseId  The course id
	 *
	 * @return  void
	 */
	public function setCourse($courseId)
	{
		// Get a instance of the details model
		$modelDetails = new THM_OrganizerModeldetails;

		// A course object is being requested by the given curriculum-course id
		$modul = $modelDetails->getModuleByID($courseId);

		// Prepare the local Attributes
		$course = array();
		$course['title_de'] = $modul->getModultitelDe();
		$course['title_en'] = $modul->getModultitelEn();
		$course['lsf_course_id'] = $modul->getModulId();
		$course['lsf_course_code'] = $modul->getNrMni();
		$course['his_course_code'] = $modul->getNrHis();
		$course['creditpoints'] = $modul->getCreditpoints();
		$course['abbreviation'] = $modul->getKuerzel();
		$course['short_title_en'] = $modul->getKurznameEn();
		$course['short_title_de'] = $modul->getKurznameDe();

		// Check if the course is already stored in the database
		$insertid = self::isInsertedCourse($course['lsf_course_id']);
		if ($insertid)
		{
			// Update the concerned course (overwrite the data)
			self::updateCourse($course);
		}
		else
		{
			// Insert the course to the database
			$insertid = self::insertCourse($course);
		}

		// Get all related lecturers
		$dozenten = $modul->getDozenten();

		// Prepeare the local data for the responsible of the course
		$lecturer = array();
		$lecturer['userid'] = $modul->getModulVerantwortlicherLdap();
		$lecturer['surname'] = $modul->getModulVerantwortlicherNachname();
		$lecturer['forename'] = $modul->getModulVerantwortlicherVorname();
		$lecturer['academic_title'] = "";

		// Prepare the data for the further mapping: responsible -> course id
		$responsible = array();
		$responsible['modul_id'] = $insertid;
		$responsible['lecturer_type'] = 1;

		// Write the person to the database and save the inserted id

		// Check whethter the current person is already saved in the database
		$lecturerIsInserted = self::isInserted($lecturer['forename'], $lecturer['surname']);

		if ($lecturerIsInserted > 0)
		{
			$responsible['lecturer_id'] = $lecturerIsInserted;
		}
		else
		{
			$responsible['lecturer_id'] = self::insertLecturer($lecturer);
		}

		if ($modul->getModulVerantwortlicherNachname())
		{
			self::clearAllLecturerMappings($insertid, "1");
		}

		// Insert the lecturer mapping
		self::insertCourseLecturer($responsible);

		if ($dozenten)
		{
			self::clearAllLecturerMappings($insertid, "2");
		}

		// Insert all related persons as course lecturers
		foreach ($dozenten as $dozent)
		{
			// Prepare the data
			$lecturer = array();
			$lecturer['userid'] = $dozent['id'];
			$lecturer['surname'] = $dozent['name'];
			$lecturer['forename'] = $dozent['vorname'];
			$lecturer['academic_title'] = "";

			$lecturerCourse = array();
			$lecturerCourse['modul_id'] = $insertid;
			$lecturerCourse['lecturer_type'] = 2;

			// Write the data to the database
			$lecturerID = self::isInserted($lecturer['forename'], $lecturer['surname']);

			if ($lecturerID > 0)
			{
				$lecturerCourse['lecturer_id'] = $lecturerID;
			}
			else
			{
				$lecturerCourse['lecturer_id'] = self::insertLecturer($lecturer);
			}

			self::insertCourseLecturer($lecturerCourse);
		}
	}

	/**
	 * Method to get the soap queries
	 *
	 * @param   Array  $majors  Majors
	 *
	 * @return  Object
	 */
	public function getSoapQueries($majors)
	{
		$dbo = JFactory::getDBO();

		$query = $dbo->getQuery(true);
				
		$query->select('*');
		$query->from("#__thm_organizer_majors");
		$query->where("id IN('" . implode("', '", $majors) . "')");	
		
		$dbo->setQuery($query);
		$rows = $dbo->loadObjectList();

		return $rows;
	}

	/**
	 * Method to import courses from curriculum based on the configured soap queries in the backend
	 *
	 * @param   Array  $majors  Majors
	 *
	 * @return  void
	 */
	public function import($majors)
	{
		$mainframe = JFactory::getApplication();
		$majors = explode(",", $majors);
		$lsf_query_parameters = self::getSoapQueries($majors);

		$globParams = JComponentHelper::getParams('com_thm_organizer');
		set_time_limit(300);

		foreach ($lsf_query_parameters as $lsf_query_parameter)
		{
			// Doing a soap request on curriculum, based on the current component configuration
			$client = new LSFClient(
					$globParams->get('webserviceUri'), $globParams->get('webserviceUsername'), $globParams->get('webservicePassword')
			);
			$modulesXML = $client->getModules(
					$lsf_query_parameter->lsf_object, $lsf_query_parameter->lsf_study_path, $lsf_query_parameter->lsf_degree,
					$lsf_query_parameter->po
			);

			// Check whether there is a soap response (xml format)
			if (isset($modulesXML))
			{
				// Iterate over the entire over each course-group of the returned xml structure
				foreach ($modulesXML->gruppe as $gruppe)
				{
					// The current group containts no courses (the current course does not belong to a group)
					if ($gruppe->modulliste->modul[0] == null)
					{
						// Check if this course is already stored in the database

						// Write the course to the database
						self::setCourse($gruppe->pordid);
					}
					else
					{
						// Iterate over each found course
						foreach ($gruppe->modulliste->modul as $modul)
						{
							// Write the course to the database
							self::setCourse($modul->modulid);
						}
					}
				}
			}
		}
		$mainframe->close();
	}
}
