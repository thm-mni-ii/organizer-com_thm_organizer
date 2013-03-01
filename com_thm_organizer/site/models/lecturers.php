<?php
/**
 * @version     v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelLecturers
 * @description THM_OrganizerModelLecturers component site model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.model');
jimport('joomla.filesystem.path');

require_once JPATH_COMPONENT_SITE . DS . 'helper/module.php';
require_once JPATH_COMPONENT_SITE . DS . 'helper/lsfapi.php';
require_once JPATH_COMPONENT_SITE . DS . 'helper/ModuleList.php';
require_once JPATH_COMPONENT_SITE . DS . 'models/groups.php';
require_once JPATH_COMPONENT_SITE . DS . 'models/index.php';
require_once JPATH_COMPONENT_SITE . DS . 'models/curriculum.php';

/**
 * Class THM_OrganizerModelLecturers for component com_thm_organizer
 *
 * Class provides methods to deal with lecturers
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerModelLecturers extends JModel
{
	/**
	 * Data
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_data;

	/**
	 * Database
	 *
	 * @var    Object
	 * @since  1.0
	 */
	protected $db = null;

	/**
	 * Global Parameters
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
	 * Constructor to set up the class variables and call the parent constructor
	 */
	public function __construct()
	{
		$this->db = JFactory::getDBO();
		$this->globParams = JComponentHelper::getParams('com_thm_organizer');
		$this->groupsModel = $model = new THM_OrganizerModelGroups;
		$this->groupsCurriculum = $model = new THM_OrganizerModelCurriculum;
		$this->indexModel = $model = new THM_OrganizerModelIndex;
		$this->session = & JFactory::getSession();
		$this->lang = JRequest::getVar('lang');

		parent::__construct();
	}

	/**
	 * Method to return the responsible label which contains the first and last name from the THM Groups extension
	 *
	 * @param   String  $userid  User id
	 *
	 * @return  Array
	 */
	public function getLecturerNameFromThmGroups($userid)
	{
		$query = "SELECT DISTINCT text1.value as vorname, text2.value as nachname, text3.value as titel " .
				"FROM #__thm_groups_text as text1 INNER JOIN #__thm_groups_text as text2 "
				. "ON text1.userid = text2.userid INNER JOIN #__thm_groups_text as text3 ON text2.userid = text3.userid"
				. " WHERE text1.structid=1 AND text2.structid=2 AND text3.structid=5 AND text1.userid='" . $userid . "';";

		$this->db->setQuery($query);
		$rows = $this->db->loadObjectList();

		if (isset($rows[0]))
		{
			$result = array();
			$result['name'] = $rows[0]->nachname . ", " . $rows[0]->vorname;
			$result['title'] = $rows[0]->titel;

			return $result;
		}
	}

	/**
	 *
	 * Builds a lecturer link to mod_thm_groups
	 * @param Int $lecturerId
	 * @param String $viewName
	 * @return Ambigous <NULL, string>
	 */
	/**
	 * Method to build a lecturer link to mod_thm_groups
	 *
	 * @param   Integer  $lecturerId  Lecturer id
	 * @param   String   $viewName    The view name  (default: 'groups')
	 *
	 * @return  Array
	 */
	public function buildLecturerLink($lecturerId, $viewName = 'groups')
	{
		// Build the sql statement
		$query = $this->db->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_lecturers as lecturers');
		$query->where("lecturers.id = $lecturerId ");
		$this->db->setQuery($query);

		$rows = $this->db->loadObjectList();

		// Get the user id fron the THM Groups Extension
		$userid = $this->groupsModel->getUserIdFromGroups($rows[0]->userid);

		// Get the lecturers name from THM Groups
		$lecturerName = self::getLecturerNameFromThmGroups($userid);

		$responsilbeLabel = null;

		$lecturer = array();

		$lecturer['name'] = $lecturerName['name'];
		$lecturer['title'] = $lecturerName['title'];

		/* if there is data from THM Groups */
		if ($lecturerName)
		{
			$linkTarget = 'index.php?option=com_thm_groups&view=profile&layout=default';
			$responsilbeLabel = JRoute::_(
					$linkTarget . '&gsuid=' . $userid . '&name=' . trim($rows[0]->surname) . "&Itemid=" . JRequest::getVar('Itemid')
			);

			$lecturer['name'] = $lecturerName['name'];
			$lecturer['title'] = $lecturerName['title'];
			$lecturer['link'] = "<a href='" . $responsilbeLabel . "'>" . $lecturer['name'] . "</a>";
		}
		else
		{
			// Take the data from the lectures database
			$lecturer['name'] = $rows[0]->surname . ", " . $rows[0]->forename;
			$lecturer['link'] = $rows[0]->surname . ", " . $rows[0]->forename;
			$lecturer['title'] = "";
		}

		return $lecturer;
	}

	/**
	 * Method to build a SQL where clause which contains courses which are related to the current lsf major
	 *
	 * @return  String
	 */
	public function getSqlOr()
	{
		// Perform a soap request, in order to get all related courses
		$client = new LsfClient(
				$this->globParams->get('webserviceUri'), $this->globParams->get('webserviceUsername'), $this->globParams->get('webservicePassword')
		);
		$config = $this->groupsModel->getLsfConfiguration();
		$xml = $client->getModules($config[0]->lsf_object, $config[0]->lsf_study_path, "", $config[0]->lsf_degree, $config[0]->po);

		// Build the where clause
		$or = " WHERE lsf_course_id IN (";
		if (isset($xml))
		{
			// Iterate over each couse group
			foreach ($xml->gruppe as $gruppe)
			{
				if ($gruppe->modulliste->modul[0] == null)
				{
					$or .= "'$gruppe->pordid', ";
				}
				else
				{
					foreach ($gruppe->modulliste->modul as $modul)
					{
						$or .= "'$modul->modulid', ";
					}
				}
			}
			$or .= ")";
			$ret = substr($or, 0, strrpos($or, ','));
		}

		return $ret;
	}

	/**
	 * Method to get the data
	 *
	 * @return  mixed
	 */
	public function getData()
	{
		// Do a soap request, in order to get all courses of this lsf major
		$config = $this->groupsModel->getLsfConfiguration();
		$client = new LsfClient(
				$this->globParams->get('webserviceUri'), $this->globParams->get('webserviceUsername'), $this->globParams->get('webservicePassword')
		);
		$modulesXML = $client->getModules($config[0]->lsf_object, $config[0]->lsf_study_path, "", $config[0]->lsf_degree, $config[0]->po);

		$this->major = $this->groupsCurriculum->getMajorRecord($config[0]->id);

		// Get the course filter list
		$filter = self::filter();

		// Builds a where clause which selects all courses of this major
		$ret = self::getSqlOr();

		// Build the sql statement in order to get all lecturers of the current major
		$query = "SELECT DISTINCT  * FROM #__thm_organizer_lecturers_assets as lecturers_assets, " .
				"#__thm_organizer_assets as assets" . $ret . $filter . " AND lecturer_type = 2 " .
				"AND assets.id = lecturers_assets.modul_id GROUP BY lecturer_id";
		$this->db->setQuery($query);
		$lecturers = $this->db->loadObjectList();

		$dozenten = array(array());
		$i = 0;

		// Iterate over each lecturer
		foreach ($lecturers as $lecturer)
		{
			$j = 0;
			$lecturerInfo = self::buildLecturerLink($lecturer->lecturer_id, $viewName = 'lecturers');

			// Build the lecturer name
			if ($lecturer->lecturer_id != "")
			{
				$dozenten[$i]['lecturer'] = $lecturerInfo['link'];
				$dozenten[$i]['lecturer_sort'] = $lecturerInfo['name'];
				$dozenten[$i]['title'] = $lecturerInfo['title'];
			}
			else
			{
				$dozenten[$i]['lecturer'] = "nicht zugeordnet";
				$dozenten[$i]['lecturer_sort'] = "nicht zugeordnet";
			}

			$dozenten[$i][$j] = array();

			// Determine related courses of this lecturer
			$query = "SELECT DISTINCT modul_id FROM #__thm_organizer_lecturers_assets as lecturers_assets, " .
					"#__thm_organizer_assets as assets" . $ret . $filter
					. "  AND assets.id = lecturers_assets.modul_id AND lecturer_type = 2 AND lecturer_id=" . $lecturer->lecturer_id . "; ";

			$this->db->setQuery($query);
			$courses = $this->db->loadObjectList();

			/* iterate over each related course */
			foreach ($courses as $key => $course)
			{
				$moduldb = self::getModule($course->modul_id);

				if ($lecturer->lecturer_id == "")
				{
					$query = "SELECT  COUNT(*) as count FROM #__thm_organizer_lecturers_assets WHERE modul_id='" . $course->modul_id . "'; ";
					$this->db->setQuery($query);
					$count = $this->db->loadObjectList();

					if ($count[0]->count > 1)
					{
						continue;
					}
				}

				$dozenten[$i][$j]['course_code'] = $this->groupsModel->buildCourseDetailLink($moduldb);
				$dozenten[$i][$j]['origmodulnummer'] = $moduldb->lsf_course_id;

				// Set the correct language
				if ($this->lang == 'de')
				{
					$dozenten[$i][$j]['title'] = $moduldb->title_de;
				}
				else
				{
					$dozenten[$i][$j]['title'] = $moduldb->title_en;
				}

				$dozenten[$i][$j]['title'] = "<a href='" .
						JRoute::_("index.php?option=com_thm_organizer&view=details&Itemid=" . JRequest::getVar('Itemid') .
								"&lang=" . $this->lang . "&id="
								. $moduldb->lsf_course_id
						) . "'>" . $dozenten[$i][$j]['title'] . "</a>" . " ("
						. ($moduldb->lsf_course_code ? $moduldb->lsf_course_code : ($moduldb->his_course_code ?
								$moduldb->his_course_code : $moduldb->lsf_course_id)) . ")";

				$dozenten[$i][$j]['creditpoints'] = $moduldb->min_creditpoints . " CrP";
				$dozenten[$i][$j]['schedule'] = $this->groupsModel->getSchedulerTooltip(
						strtolower(
								$moduldb->lsf_course_code
						), $this->major[0]['organizer_major']
				);

				$j++;
			}
			$i++;
		}

		usort($dozenten, array($this, "cmpDozenten"));
		self::setNavigationToSession($dozenten);

		return $dozenten;
	}

	/**
	 * Method to get a module
	 *
	 * @param   Integer  $modulnummer  Module number
	 *
	 * @return  Object
	 */
	public function getModule($modulnummer)
	{
		$query = "SELECT * FROM #__thm_organizer_assets WHERE id=" . $modulnummer . "; ";
		$this->db->setQuery($query);

		return $this->db->loadObject();
	}

	/**
	 * Method to sort by the responsible (usort callback)
	 *
	 * @param   Object  $a  An object
	 * @param   Object  $b  Another object
	 *
	 * @return  Integer Returns < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
	 */
	public function cmpDozenten($a, $b)
	{
		return strcmp($a['lecturer_sort'], $b['lecturer_sort']);
	}

	/**
	 * Method to create the navigationbar in the module description and store it into the session
	 *
	 * @param   mixed  $dozenten  Teachers
	 *
	 * @return  Array
	 */
	public function setNavigationToSession($dozenten)
	{
		$session = & JFactory::getSession();
		$navi = array();
		for ($i = 0; $i < count($dozenten); $i++)
		{
			for ($j = 0; $j < count($dozenten[$i]); $j++)
			{
				$arr = array();
				if (isset($dozenten[$i][$j]))
				{
					$arr['id'] = $dozenten[$i][$j]['origmodulnummer'];
					$arr['link'] = JRoute::_("index.php?option=com_thm_organizer&view=details&id=" . $dozenten[$i][$j]['origmodulnummer']);
					array_push($navi, $arr);
				}
			}
		}
		$session->set('navi_json', json_encode($navi));
		$session->set('view_state', 'dozent');
		return $navi;
	}

	/**
	 *  builds a sql where clause in order to filter selected courses
	 */
	/**
	 * Method to build a sql where clause in order to filter selected courses
	 *
	 * @return  String
	 */
	public function filter()
	{
		if ($this->globParams->get('filter') == 1)
		{
			$paramfilter = $this->globParams->get('modulecodeFilterList');
			$explodedFilterValues = explode(',', $paramfilter);

			$sql = " AND (";

			$last_item = end($explodedFilterValues);
			$last_item = each($explodedFilterValues);
			reset($explodedFilterValues);

			foreach ($explodedFilterValues as $key => $value)
			{
				$val = (int) $value;
				$sql .= "(his_course_code <> $val AND lsf_course_code <> '$value' ) ";

				if ($value == $last_item['value'] && $key == $last_item['key'])
				{

				}
				else
				{
					$sql .= " AND";
				}
			}
		}

		return $sql . ")";
	}
}
