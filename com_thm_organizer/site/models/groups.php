<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelGroups
 * @description THM_OrganizerModelGroups component site model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

/**
 * Class THM_OrganizerModelGroups for component com_thm_organizer
 *
 * Class provides methods to work with module groups
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelGroups extends JModel
{
	/**
	 * Database
	 *
	 * @var    Object
	 */
	protected $dbo = null;

	/**
	 * Application
	 *
	 * @var    Object
	 */
	private $_app = null;

	/**
	 * Global parameters
	 *
	 * @var    Object
	 */
	private $_globParams = null;

	/**
	 * Session
	 *
	 * @var    Object
	 */
	private $_session = null;

	/**
	 * Language
	 *
	 * @var    String
	 */
	private $_lang = null;

	/**
	 * Major
	 *
	 * @var    Object
	 */
	private $_major = null;

	/**
	 * Constructor to set up the class variables and call the parent constructor
	 *
	 * @param   String  $lang  The language to use
	 */
	public function __construct($lang = null)
	{
		$this->dbo = JFactory::getDBO();
		$this->_app = JFactory::getApplication();
		$this->_globParams = JComponentHelper::getParams('com_thm_organizer');
		$this->_session = JFactory::getSession();

		if ($lang == null)
		{
			$this->_lang = JRequest::getVar('lang');
		}
		else
		{
			$this->_lang = $lang;
		}
		parent::__construct();
	}

	/**
	 * Method to build the lecturer link
	 *
	 * @param   Integer  $assetId  Course id
	 * @param   String   $lang     Language
	 *
	 * @return Array
	 */
	public function buildLecturerLink($assetId, $lang)
	{
		if (!isset($assetId) && $assetId == "")
		{
			return;
		}

		$verantwLabel = "";
		if ($lang == "de")
		{
			$verantwLabel = " (Modulverantw.)";
		}
		else
		{
			$verantwLabel = " (Respons.)";
		}

		$ret = array();

		// Build the sql statement
		$query = $this->dbo->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_lecturers as lecturers');
		$query->innerJoin('#__thm_organizer_lecturers_assets as lecturers_assets ON lecturers.id = lecturers_assets.lecturer_id ');
		$query->innerJoin('#__thm_organizer_assets as assets ON assets.id = lecturers_assets.modul_id');
		$query->where("assets.lsf_course_id = $assetId ");

		$query->group("lecturer_id");
		$query->order("lecturer_type");

		$this->db->setQuery((string) $query);
		$rows = $this->db->loadObjectList();

		foreach ($rows as $row)
		{
			// Get the user id fron the THM Groups Extension
			if (isset($row->userid) && $row->userid != "")
			{
				$userid = self::getUserIdFromGroups($row->userid);
			}

			// Get the lecturers name from THM Groups
			if ($userid == 0)
			{
				$lecturerName = false;
			}
			else
			{
				$lecturerName = self::getTeacherName($userid);
			}

			$responsilbeLabel = null;

			// If there is data from THM Groups
			if ($lecturerName)
			{
				$linkTarget = 'index.php?option=com_thm_groups&view=profile&layout=default';
				$responsilbeLabel = JRoute::_(
						$linkTarget . '&gsuid=' . $userid . '&name=' . trim($row->surname) . "&Itemid=" . JRequest::getVar('Itemid')
				);
				$responsilbeLabel = "<a href='" . $responsilbeLabel . "'>" . $lecturerName . "</a>";
			}
			else
			{
				// Take the data from the lectures database
				$responsilbeLabel = $row->academic_title . " " . $row->forename . " " . $row->surname;
			}

			array_push($ret, $responsilbeLabel . (($row->lecturer_type == 1) ? $verantwLabel : ""));
		}
		return $ret;
	}

	/**
	 * Method to create an array that contains the group structur with the related modules of a major
	 *
	 * @param   Integer  $configId   Configuration id  (default: null)
	 * @param   String   $groupName  Group name        (default: null)
	 *
	 * @return Array
	 */
	public function getGroups($configId = null, $groupName = null)
	{
		// Gets the component configiration and performs a soap request, in order to get the xml structe for a given major
		$config = self::getLsfConfiguration($configId);
		$this->_major = $model->getMajorRecord($configId);

		$client = new THM_OrganizerLSFClient(
				$this->_globParams->get('webserviceUri'), $this->_globParams->get('webserviceUsername'), $this->_globParams->get('webservicePassword')
		);
		$modulesXML = $client->getModules($config[0]->lsf_object, $config[0]->lsf_study_path, $config[0]->lsf_degree, $config[0]->po);
		$groups = array();

		// Will contain all courses without related course groups
		$additionalGroups = array();

		// Set the correct label of the group, which contains all courses without a relation to a certain group
		if ($this->_lang == 'de')
		{
			$additionalGroups[0][0] = "Sonstige Module";
		}
		else
		{
			$additionalGroups[0][0] = "Other";
		}

		$number = 0;
		$index = 0;

		if (isset($modulesXML))
		{
			// Iterate over each found course group
			foreach ($modulesXML->gruppe as $gruppe)
			{

				// Get the current component configuration for this view
				$app = JFactory::getApplication();
				$menu = $app->getMenu()->getActive();

				if ($groupName != null)
				{
					$menu->params->set('lsf_group', 1);
					$menu->params->set('lsf_group_value', $groupName);
				}

				$group_filter_enabler = $menu->params->get('lsf_group');
				$group_filter_value = $menu->params->get('lsf_group_value');

				// Skip this iteration, if there is a configured filter valuefor groups
				if ((String) $gruppe->titelde != $group_filter_value && $group_filter_enabler == 1)
				{
					continue;
				}

				// Checks if the current course group contains courses. If not, the course group itself is a course
				if ($gruppe->modulliste->modul[0] == null)
				{
					/*
					 * Get the course data from the database
					* @TODO: rename method
					*/
					$course = self::getCourseById($gruppe->pordid);

					// Skip this course in case of a configured course filter list
					if (self::filter($course->lsf_course_code) || self::filter($course->his_course_code))
					{
						continue;
					}

					// Build the array strucutre for the current course and attach it to the existent data
					$additionalGroups[0][1][$number] = self::buildCourseData($course);

					// Sort the data by the lsf course code
					usort($additionalGroups[0][1], array($this, "cmpModultitel"));
					$number++;
				}
				else
				{
					// Course groups which contains several courses
					$k = 0;

					// Set the labels for the groups
					if ($this->_lang == 'de')
					{
						$groups[$index][0] = (String) $gruppe->titelde;
					}
					else
					{
						$groups[$index][0] = (String) $gruppe->titelen;
					}

					// Iterate over each found course
					foreach ($gruppe->modulliste->modul as $modul)
					{
						// Get the course data from the database
						$course = self::getCourseById($modul->modulid);

						// Skip this course in case of a configured course filter list
						if (self::filter($course->lsf_course_code) || self::filter($course->his_course_code))
						{
							continue;
						}

						// Skip this course if there is no stored lsf course id
						if (!isset($course->lsf_course_id))
						{
							continue;
						}

						// Build the array strucutre for the current course and attach it to the local structre
						$groups[$index][1][$k] = self::buildCourseData($course);

						// Sort the data by the lsf course code
						usort($groups[$index][1], array($this, "cmpModultitel"));
						$k++;
					}
					$index++;
				}
			}
		}
		else
		{
			// The soap responste contains no xml strucutre
			echo "<div><big>Keine Gruppen verf&uuml;gbar</big></div>";
		}

		// Attach the additional groups to the end of the entire strucutre
		array_push($groups, $additionalGroups[0]);

		// Build a navigation route based on this structure
		self::setNavigationToSession($groups);

		return $groups;
	}

	/**
	 * Method to check if the given lsf course id is part of the filter list for courses
	 *
	 * @param   Integer  $modulnummer  Module number
	 *
	 * @return  Integer
	 */
	public function filter($modulnummer)
	{
		$flag = null;

		if ($this->_globParams->get('filter') == 1)
		{

			// Filtering via course id
			if ($this->_globParams->get('filter_type') == 0)
			{

				$paramfilter = $this->_globParams->get('modulecodeFilterList');
				$explodedFilterValues = explode(',', $paramfilter);
				if (in_array($modulnummer, $explodedFilterValues))
				{
					$flag = 1;
				}
			}
			elseif ($this->_globParams->get('filter_type') == 1)
			{
				// Filtering via  identifier (e.g. CS, SK, ....)
				$paramfilter = $this->_globParams->get('modulecodeFilterFachgruppen');
				$explodedFilterValues = explode(',', $paramfilter);

				// Iterate over the entire filter list
				foreach ($explodedFilterValues as $value)
				{
					if (strpos($modulnummer, $value) !== false)
					{
						$flag = 1;
					}
				}
			}
		}
		return $flag;
	}

	/**
	 * Method to build the course detail link
	 *
	 * @param   Object  $row  A row object
	 *
	 * @return  String The detail link
	 */
	public function buildCourseDetailLink($row)
	{
		$courseID = $row->lsf_course_code ? $row->lsf_course_code : ($row->his_course_code ? $row->his_course_code : $row->lsf_course_id);
		$courseLink = JRoute::_("index.php?option=com_thm_organizer&view=details&lang=" . $this->_lang . "&id=" . $row->lsf_course_id);
		$detailLink = "<a href='$courseLink'>$courseID</a>";
		return $detailLink;
	}

	/**
	 * Method to build an array structre for a course, based on the given database row
	 *
	 * @param   Object  $row  A row object
	 *
	 * @return  multitype:multitype: string NULL Ambigous <void, string>
	 */
	public function buildCourseData($row)
	{
		$model = new THM_OrganizerModelCurriculum;

		$creditpoints = explode('.', $row->min_creditpoints);
		$modul['coursecode'] = self::buildCourseDetailLink($row);

		if (isset($row->lsf_course_code) && $row->lsf_course_code != "")
		{
			$modul['course_code'] = $row->lsf_course_code;
		}
		else
		{
			$modul['course_code'] = $row->his_course_code;
		}

		// Set the default language to german
		if ($this->_lang == 'de')
		{
			$modul['title'] = $row->title_de;
		}
		else
		{
			$modul['title'] = $row->title_en;
		}

		$modul['title_sort'] = $modul['title'];

		$courseID = $row->lsf_course_code ? $row->lsf_course_code : ($row->his_course_code ? $row->his_course_code : $row->lsf_course_id);
		$detailsLink = JRoute::_("index.php?option=com_thm_organizer&view=details&lang=" . JRequest::getVar('lang') . "&id=" . $row->lsf_course_id);
		$modul['title'] = "<a href='$detailsLink'>{$modul['title']}</a>" . " ($courseID)";

		$modul['creditpoints'] = $creditpoints[0] . " CrP";

		$link = $model->buildResponsibleLink($row->lsf_course_id);

		if (empty($link))
		{
			$modul['responsible'] = $model->getLecturerName($row->lsf_course_id);
		}
		else
		{
			$modul['responsible'] = "<a href='" . $link . "'>" . $model->getLecturerName($row->lsf_course_id) . "</a";
		}

		if (isset($row->lsf_course_id))
		{
			$modul['courseid'] = $row->lsf_course_id;
		}

		if (isset($row->lsf_course_code))
		{
			$modul['schedule'] = array();
			$modul['schedule'] = self::getSchedulerTooltip(strtolower($row->lsf_course_code), $this->_major[0]['organizer_major']);
		}

		return $modul;
	}

	/**
	 * Method to build the responsible link
	 *
	 * @param   Integer  $assetId  Course id
	 *
	 * @return  String
	 */
	public function buildResponsibleLink($assetId)
	{
		if (!isset($assetId) && $assetId == "")
		{
			return;
		}

		// Build the sql statement
		$query = $this->dbo->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_lecturers as lecturers');
		$query->innerJoin('#__thm_organizer_lecturers_assets as lecturers_assets ON lecturers.id = lecturers_assets.lecturer_id ');
		$query->innerJoin('#__thm_organizer_assets as assets ON assets.id = lecturers_assets.modul_id');
		$query->where("assets.lsf_course_id = $assetId ");
		$query->where("lecturers_assets.lecturer_type = 1 ");

		$this->dbo->setQuery((string) $query);
		$rows = $this->dbo->loadObjectList();

		// Get the user id fron the THM Groups Extension
		if (isset($rows[0]->userid) && $rows[0]->userid != "")
		{
			$userid = self::getUserIdFromGroups($rows[0]->userid);
		}

		// Get the lecturers name from THM Groups
		$lecturerName = self::getTeacherName($userid);

		$responsilbeLabel = null;

		// If there is data from THM Groups
		if ($lecturerName)
		{
			$rawLink = 'index.php?option=' . JRequest::getVar('option');
			$rawLink .= '&view=' . JRequest::getVar('view') . 'catid=' . JRequest::getVar("catid");
			$rawLink .= '&id=' . JRequest::getVar("id") . '&Itemid=' . JRequest::getVar('Itemid');
			$rawLink .= '&gsuid=' . $userid;
			$responsibleLink = JRoute::_($rawLink);
			$responsilbeLabel = "<a href='$responsibleLink'>" . $lecturerName . "</a>";
		}
		else
		{
			// Take the data from the lectures database
			$responsilbeLabel = $rows[0]->academic_title . " " . $rows[0]->forename . " " . $rows[0]->surname;
		}

		return $responsilbeLabel;
	}

	/**
	 * Method to return a database row of the given lsf course id
	 *
	 * @param   Integer  $courseID  Id
	 *
	 * @return  Object  A database row for the given lsf course id
	 */
	public function getCourseById($courseID)
	{
		// Build the sql statement
		$query = $this->dbo->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_assets');
		$query->where("lsf_course_id = '$courseID'");
		$this->dbo->setQuery((string) $query);
		$rows = $this->dbo->loadObjectList();

		return $rows[0];
	}

	/**
	 * Method to build a tooltip of a given lsf course code
	 *
	 * @param   Integer  $lsfCorseId  Lsf course id
	 * @param   String   $major       Major name
	 *
	 * @return  String  The schedule tooltip
	 */
	public function getSchedulerTooltip($lsfCorseId, $major)
	{
		if ($lsfCorseId == "")
		{
			return;
		}

		$model = new THM_OrganizerModelCurriculum;
		
		if ($major == null)
		{
			$config = self::getLsfConfiguration();
			$major = $model->getMajorRecord($config[0]->id);
		}

		// Get all schedules from the THML Organizer Extension
		$schedules = self::getScheduleEvents($lsfCorseId, $major);

		if (isset($schedules))
		{
			return "<img class='hasTip' title='Stundenplan::<br>" . $schedules . "'" . "src='"
			. JURI::base() . "/components/com_thm_organizer/css/images/scheduler.png'></img>";
		}
	}


	/**
	 * Method to return all stored soap queries of the backend
	 *
	 * @return  Object  The database result
	 */
	public function getLsfConfigurations()
	{
		$this->dbo = JFactory::getDBO();
		$query = $this->dbo->getQuery(true);
		$query->select('*');
		$query->from('#__thm_organizer_soap_queries');
		$this->dbo->setQuery((string) $query);
		$rows = $this->dbo->loadObjectList();

		return $rows;
	}

	/**
	 * Method to return the record of the desired LSF configuration of a major
	 *
	 * @param   Integer  $configId  Configuration id  (default: null)
	 *
	 * @return  Object  LSF configuration
	 */
	public function getLsfConfiguration($configId = null)
	{
		if ($configId == null)
		{
			$menus = $this->_app->getMenu();
			$menu = $menus->getActive();
			$configId = $menu->params->get('lsf_query');
		}

		$this->dbo = JFactory::getDBO();
		$query = $this->dbo->getQuery(true);
		$query->select('*');
		$query->from('#__thm_organizer_majors');
		$query->where("id = '$configId'");
		$this->dbo->setQuery((string) $query);
		$rows = $this->dbo->loadObjectList();

		return $rows;
	}

	/**
	 * Method to sort by module title (usort callback)
	 *
	 * @param   Object  $thingOne  An object
	 * @param   Object  $thingTwo  Another object
	 *
	 * @return  Boolean  True if module number $thingOne is less than module number $thingTwo
	 */
	public function cmpModultitel($thingOne, $thingTwo)
	{
		return $thingOne['title_sort'] > $thingTwo['title_sort'];
	}

	/**
	 * Method to return the id from the latest semester from the THM Organizer extension
	 *
	 * @return  Integer  The current semester id
	 */
	public function getCurrentSemester()
	{
		$this->dbo = JFactory::getDbo();
		$query = $this->dbo->getQuery(true);
		$query->select('sid');
		$query->from('#__thm_organizer_schedules');
		$query->order('active DESC');
		$this->dbo->setQuery((string) $query, 0, 1);
		$sid = $this->dbo->loadResult();

		return $sid;
	}

	/**
	 * Returns a Tooltip which includes the current scheduler information of the given lsf course code
	 *
	 * @param	int		LSF course code
	 * @return	String	HTML data
	 */
	/**
	 * Method to return a tooltip which includes the current scheduler information of the given lsf course code
	 *
	 * @param   Integer  $modulnummer  Module number
	 * @param   String   $major        Major (default: null)
	 *
	 * @return  String  HTML data
	 */
	public function getScheduleEvents($modulnummer, $major = null)
	{
		$sid = self::getCurrentSemester();

		$this->dbo = JFactory::getDbo();
		$query = $this->dbo->getQuery(true);
		$query->select('*,teacher.name AS tname, l.type AS event_type, rooms.name AS room_name');
		$query->from('#__thm_organizer_subjects AS s');
		$query->innerJoin('#__thm_organizer_lessons AS l ON l.subjectID = s.id');
		$query->innerJoin('#__thm_organizer_lesson_teachers AS lt ON l.id = lt.lessonID');
		$query->innerJoin('#__thm_organizer_lesson_times AS time ON l.id = time.lessonID');
		$query->innerJoin('#__thm_organizer_teachers AS t ON lt.teacherID = t.id');
		$query->innerJoin('#__thm_organizer_periods AS p ON time.periodID = p.id');
		$query->innerJoin('#__thm_organizer_rooms AS r ON time.roomID = r.id');
		$query->innerJoin('#__thm_organizer_lesson_classes AS lc ON l.id = lc.lessonID');
		$query->from('#__thm_organizer_classes AS c ON lc.classID = c.id');
		$query->where("s.moduleID = '$modulnummer'");
		$query->where("l.semesterID = '$sid'");
		$query->where("c.major = '$major'");
		$query->order('day, starttime ASC');
		$this->dbo->setQuery((string) $query);
		$schedules = $this->dbo->loadObjectList();

		$html = null;

		if (isset($schedules))
		{
			// Iterate over each found scheduler event
			foreach ($schedules as $schedule)
			{
				// Array which maps ids to weekday
				$assignDe = array(1 => 'Montag', 2 => 'Dienstag', 3 => 'Mittwoch', 4 => 'Donnerstag', 5 => 'Freitag');
				$assignEn = array(1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday');

				$eventTyp = null;
				if ($schedule->event_type == "V")
				{
					$eventTyp = "Vorlesung";
					if (JRequest::getVar('lang') == 'de')
					{
						$eventTyp = "Vorlesung";
					}
					else
					{
						$eventTyp = "Lecture";
					}
				}
				elseif ($schedule->event_type == "P")
				{
					if (JRequest::getVar('lang') == 'de')
					{
						$eventTyp = "Praktikum";
					}
					else
					{
						$eventTyp = "Practical Course";
					}
				}
				else
				{
					if (JRequest::getVar('lang') == 'de')
					{
						$eventTyp = "&Uuml;bung";
					}
					else
					{
						$eventTyp = "Exercises";
					}
				}

				if (JRequest::getVar('lang') == 'de')
				{
					$day = $assignDe[$schedule->day];
				}
				else
				{
					$day = $assignEn[$schedule->day];
				}

				$html .= "<b>" . $eventTyp . "</b>" . "<br>" . $day . " " . substr($schedule->starttime, 0, 5)
				. " - " . substr($schedule->endtime, 0, 5) . " Uhr (" . $schedule->period . ". Block)"
				. "<br> " . $schedule->tname . " / " . $schedule->room_name . "<br><br>";
			}
		}
		return $html;
	}

	/**
	 * Method to store the necessary data for the navigation bar into the session
	 *
	 * @param   mixed  $groups  Groups
	 *
	 * @return  void
	 */
	public function setNavigationToSession($groups)
	{
		$session = & JFactory::getSession();
		$navi = array();

		// Iterate over each group
		for ($index = 0; $index < count($groups); $index++)
		{
			if (isset($groups[$index][1]))
			{
				// Iterate over each course
				for ($h = 0; $h < count($groups[$index][1]); $h++)
				{
					$arr = array();
					$arr['id'] = $groups[$index][1][$h]['courseid'];
					$arr['link'] = JRoute::_("index.php?option=com_thm_organizer&view=details&id=" . $groups[$index][1][$h]['courseid']);
					array_push($navi, $arr);
				}
			}
		}

		// Save the array in json representation to the session
		$session->set('navi_json', json_encode($navi));
		$session->set('view_state', 'groups');
	}
}
