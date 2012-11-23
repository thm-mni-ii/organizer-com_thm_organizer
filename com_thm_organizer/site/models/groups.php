<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		THM_OrganizerModelGroups
 * @description THM_OrganizerModelGroups component site model
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.model');
jimport('joomla.filesystem.path');
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper/module.php';
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper/lsfapi.php';
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper/ModuleList.php';
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'models/details.php';
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'models/curriculum.php';

/**
 * Class THM_OrganizerModelGroups for component com_thm_organizer
 *
 * Class provides methods to work with module groups
 *
 * @category	Joomla.Component.Site
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerModelGroups extends JModel
{
	/**
	 * Database
	 *
	 * @var    Object
	 * @since  1.0
	 */
	protected $db = null;

	/**
	 * Application
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_app = null;

	/**
	 * Global parameters
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_globParams = null;

	/**
	 * Session
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_session = null;

	/**
	 * Language
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_lang = null;

	/**
	 * Major
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_major = null;

	/**
	 * Constructor to set up the class variables and call the parent constructor
	 *
	 * @param   String  $lang  The language to use
	 */
	public function __construct($lang = null)
	{
		$this->db = &JFactory::getDBO();
		$this->app = &JFactory::getApplication();
		$this->globParams = JComponentHelper::getParams('com_thm_organizer');
		$this->session = & JFactory::getSession();

		if ($lang == null)
		{
			$this->lang = JRequest::getVar('lang');
		}
		else
		{
			$this->lang = $lang;
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
		$query = $this->db->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_lecturers as lecturers');
		$query->join('inner', '#__thm_organizer_lecturers_assets as lecturers_assets ON lecturers.id = lecturers_assets.lecturer_id ');
		$query->join('inner', '#__thm_organizer_assets as assets ON assets.id = lecturers_assets.modul_id');
		$query->where("assets.lsf_course_id = $assetId ");

		$query->group("lecturer_id");
		$query->order("lecturer_type");

		$this->db->setQuery($query);
		$rows = $this->db->loadObjectList();

		foreach ($rows as $row)
		{
			// Get the user id fron the THM Groups Extension
			if (isset($row->userid) && $row->userid != "")
			{
				$userid = self::getUserIdFromGroups($row->userid);
			}

			// Get the lecturers name from THM Groups
			$lecturerName = self::getLecturerNameFromThmGroups($userid);

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
		$model = new THM_OrganizerModelCurriculum;
		$this->major = $model->getMajorRecord($configId);

		$client = new LsfClient(
				$this->globParams->get('webserviceUri'), $this->globParams->get('webserviceUsername'), $this->globParams->get('webservicePassword')
		);
		$modulesXML = $client->getModules($config[0]->lsf_object, $config[0]->lsf_study_path, "", $config[0]->lsf_degree, $config[0]->po);
		$groups = array();

		// Will contain all courses without related course groups
		$additionalGroups = array();

		// Set the correct label of the group, which contains all courses without a relation to a certain group
		if ($this->lang == 'de')
		{
			$additionalGroups[0][0] = "Sonstige Module";
		}
		else
		{
			$additionalGroups[0][0] = "Other";
		}

		$n = 0;
		$i = 0;

		if (isset($modulesXML))
		{
			// Iterate over each found course group
			foreach ($modulesXML->gruppe as $gruppe)
			{
				$app = JFactory::getApplication();
				$menus = $app->getMenu();
				$menu = $menus->getActive();

				if ($groupName != null)
				{
					$menu->params->set('lsf_group', 1);
					$menu->params->set('lsf_group_value', $groupName);
				}

				// Get the current component configuration for this view
				$app = JFactory::getApplication();
				$menus = $app->getMenu();
				$menu = $menus->getActive();
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
					$additionalGroups[0][1][$n] = self::buildCourseData($course);

					// Sort the data by the lsf course code
					usort($additionalGroups[0][1], array($this, "cmpModultitel"));
					$n++;
				}
				else
				{
					// Course groups which contains several courses
					$k = 0;

					// Set the labels for the groups
					if ($this->lang == 'de')
					{
						$groups[$i][0] = (String) $gruppe->titelde;
					}
					else
					{
						$groups[$i][0] = (String) $gruppe->titelen;
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
						$groups[$i][1][$k] = self::buildCourseData($course);

						// Sort the data by the lsf course code
						usort($groups[$i][1], array($this, "cmpModultitel"));
						$k++;
					}
					$i++;
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

		if ($this->globParams->get('filter') == 1)
		{

			// Filtering via course id
			if ($this->globParams->get('filter_type') == 0)
			{

				$paramfilter = $this->globParams->get('modulecodeFilterList');
				$explodedFilterValues = explode(',', $paramfilter);
				if (in_array($modulnummer, $explodedFilterValues))
				{
					$flag = 1;
				}
			}
			elseif ($this->globParams->get('filter_type') == 1)
			{
				// Filtering via  identifier (e.g. CS, SK, ....)
				$paramfilter = $this->globParams->get('modulecodeFilterFachgruppen');
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
		$detailLink = "<a href='" . JRoute::_("index.php?option=com_thm_organizer&view=details&lang=" . $this->lang . "&id="
				. $row->lsf_course_id
		) .
		"'>" . ($row->lsf_course_code ? $row->lsf_course_code : ($row->his_course_code ? $row->his_course_code : $row->lsf_course_id))
		. "</a>";

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
		$data = array();
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
		if ($this->lang == 'de')
		{
			$modul['title'] = $row->title_de;
		}
		else
		{
			$modul['title'] = $row->title_en;
		}

		$modul['title_sort'] = $modul['title'];

		$modul['title'] = "<a href='" . JRoute::_("index.php?option=com_thm_organizer&view=details&lang=" . JRequest::getVar('lang') . "&id="
				. $row->lsf_course_id
		) .
		"'>" . $modul['title'] . "</a>" . " ("
		. ($row->lsf_course_code ? $row->lsf_course_code : ($row->his_course_code ? $row->his_course_code : $row->lsf_course_id)) . ")";

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

		if (isset($row->lsf_course_code))
		{
			$modul['schedule'] = array();
			$modul['schedule'] = self::getSchedulerTooltip(strtolower($row->lsf_course_code), $this->major[0]['organizer_major']);
		}

		return $modul;
	}

	/**
	 * Method to build the responsible link
	 *
	 * @param   Integer  $assetId   Course id
	 * @param   String   $viewName  The view name  (default: 'groups')
	 *
	 * @return  String
	 */
	public function buildResponsibleLink($assetId, $viewName = 'groups')
	{
		$model = new THM_OrganizerModelCurriculum;

		if (!isset($assetId) && $assetId == "")
		{
			return;
		}

		// Build the sql statement
		$query = $this->db->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_lecturers as lecturers');
		$query->join('inner', '#__thm_organizer_lecturers_assets as lecturers_assets ON lecturers.id = lecturers_assets.lecturer_id ');
		$query->join('inner', '#__thm_organizer_assets as assets ON assets.id = lecturers_assets.modul_id');
		$query->where("assets.lsf_course_id = $assetId ");
		$query->where("lecturers_assets.lecturer_type = 1 ");

		$this->db->setQuery($query);
		$rows = $this->db->loadObjectList();

		// Get the user id fron the THM Groups Extension
		if (isset($rows[0]->userid) && $rows[0]->userid != "")
		{
			$userid = self::getUserIdFromGroups($rows[0]->userid);
		}

		// Get the lecturers name from THM Groups
		$lecturerName = self::getLecturerNameFromThmGroups($userid);

		$responsilbeLabel = null;

		// If there is data from THM Groups
		if ($lecturerName)
		{
			$responsilbeLabel = "<a href='" . JRoute::_("index.php?option=" . JRequest::getVar('option') . "&view=" . JRequest::getVar('view')
					. "&catid=" . JRequest::getVar("catid") . "&id=" . JRequest::getVar("id") . "&Itemid="
					. JRequest::getVar('Itemid') . "&gsuid=" . $userid
			) .
			"'>" . $lecturerName . "</a>";
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
	 * @param   Integer  $id  Id
	 *
	 * @return  Object  A database row for the given lsf course id
	 */
	public function getCourseById($id)
	{
		// Build the sql statement
		$query = $this->db->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_assets');
		$query->where("lsf_course_id = $id");
		$this->db->setQuery($query);
		$rows = $this->db->loadObjectList();

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
	 * Method to return the UserId from the THM Groups Extension
	 *
	 * @param   String  $hgNr  LDAP-ID
	 *
	 * @return	String	GET-Parameter
	 */
	public function getUserIdFromGroups($hgNr)
	{
		// Build the sql query
		$query = "SELECT * FROM #__thm_groups_text WHERE value ='" . $hgNr . "' AND structid = 3;";
		$this->db->setQuery($query);
		$rows = $this->db->loadObjectList();

		if (isset($rows[0]))
		{
			return $rows[0]->userid;
		}
	}

	/**
	 * Method to return the responsible label which contains the first and last name from the THM Groups Extension
	 *
	 * @param   String  $userid  User id
	 *
	 * @return  String
	 */
	public function getLecturerNameFromThmGroups($userid)
	{
		$query = "SELECT DISTINCT text1.value as vorname, text2.value as nachname, " .
				"text3.value as titel FROM #__thm_groups_text as text1 INNER JOIN #__thm_groups_text as text2 "
				. "ON text1.userid = text2.userid INNER JOIN #__thm_groups_text as text3 ON text2.userid = text3.userid"
				. " WHERE text1.structid=1 AND text2.structid=2 AND text3.structid=5 AND text1.userid='" . $userid . "';";

		$this->db->setQuery($query);
		$rows = $this->db->loadObjectList();

		if (isset($rows[0]))
		{
			return $rows[0]->titel . " " . $rows[0]->vorname . " " . $rows[0]->nachname;
		}
	}

	/**
	 * Method to return all stored soap queries of the backend
	 *
	 * @return  Object  The database result
	 */
	public function getLsfConfigurations()
	{
		$this->db = &JFactory::getDBO();

		// Build the sql statement
		$query = "SELECT * FROM #__thm_organizer_soap_queries;";
		$this->db->setQuery($query);
		$rows = $this->db->loadObjectList();

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
			$menus = $this->app->getMenu();
			$menu = $menus->getActive();
			$configId = $menu->params->get('lsf_query');
		}

		$this->db = &JFactory::getDBO();
		$query = "SELECT * FROM #__thm_organizer_majors WHERE id = $configId;";
		$this->db->setQuery($query);
		$rows = $this->db->loadObjectList();

		return $rows;
	}

	/**
	 * Method to sort by module number (usort callback)
	 *
	 * @param   Object  $a  An object
	 * @param   Object  $b  Another object
	 *
	 * @return  Boolean  True if module number $a is less than module number $b
	 */
	public function cmpModulnummer($a, $b)
	{
		if (!is_numeric($a['course_code']))
		{
			return strcmp($a['course_code'], $b['course_code']);
		}
		else
		{
			return $a['course_code'] > $b['course_code'];
		}
	}

	/**
	 * Method to sort by module title (usort callback)
	 *
	 * @param   Object  $a  An object
	 * @param   Object  $b  Another object
	 *
	 * @return  Boolean  True if module number $a is less than module number $b
	 */
	public function cmpModultitel($a, $b)
	{
		return $a['title_sort'] > $b['title_sort'];
	}

	/**
	 * Method to return the id from the latest semester from the THM Organizer extension
	 *
	 * @return  Integer  The current semester id
	 */
	public function getCurrentSemester()
	{
		$query = "SELECT sid FROM #__thm_organizer_schedules ORDER BY active DESC LIMIT 1";
		$this->db->setQuery($query);
		$sid = $this->db->loadResult();

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
	 * @param   String   $major  	   Major          (default: null)
	 *
	 * @return  String  HTML data
	 */
	public function getScheduleEvents($modulnummer, $major = null)
	{
		$sid = self::getCurrentSemester();

		$query = "SELECT *,teacher.name as tname, lessons.type as event_type, rooms.name as room_name "
		. "FROM #__thm_organizer_subjects as subjects,"
		. "#__thm_organizer_lessons as lessons,"
		. "#__thm_organizer_lesson_teachers as teachers,"
		. "#__thm_organizer_lesson_times as time,"
		. "#__thm_organizer_teachers as teacher,"
		. "#__thm_organizer_periods as periods,"
		. "#__thm_organizer_rooms as rooms,"
		. "#__thm_organizer_classes as classes,"
		. "#__thm_organizer_lesson_classes as lesson_classes"
		. " WHERE subjects.moduleID = '$modulnummer'"
		. " AND subjects.id = lessons.subjectID"
		. " AND lessons.id = teachers.lessonID"
		. " AND teachers.teacherID = teacher.id"
		. " AND time.lessonID = lessons.id"
		. " AND time.periodID = periods.id"
		. " AND rooms.id = time.roomID"
		. " AND lessons.semesterID = $sid"
		. " AND lesson_classes.classID = classes.id"
		. " AND lesson_classes.lessonID = lessons.id"
		. " AND classes.major = '" . $major . "'"
		. " ORDER BY day , starttime asc";

		$this->db->setQuery($query);

		$schedules = $this->db->loadObjectList();
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
		$k = 0;
		$session = & JFactory::getSession();
		$navi = array();

		// Iterate over each group
		for ($i = 0; $i < count($groups); $i++)
		{
			if (isset($groups[$i][1]))
			{
				// Iterate over each course
				for ($h = 0; $h < count($groups[$i][1]); $h++)
				{
					$arr = array();
					$arr['id'] = $groups[$i][1][$h]['courseid'];
					$arr['link'] = JRoute::_("index.php?option=com_thm_organizer&view=details&id=" . $groups[$i][1][$h]['courseid']);
					array_push($navi, $arr);
				}
			}
		}

		// Save the array in json representation to the session
		$session->set('navi_json', json_encode($navi));
		$session->set('view_state', 'groups');
	}
}
