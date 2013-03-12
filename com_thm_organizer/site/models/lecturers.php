<?php
/**
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
	protected $dbo = null;

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
		$this->dbo = JFactory::getDBO();
		$this->globParams = JComponentHelper::getParams('com_thm_organizer');
		$this->groupsModel = $model = new THM_OrganizerModelGroups;
		$this->groupsCurriculum = $model = new THM_OrganizerModelCurriculum;
		$this->indexModel = $model = new THM_OrganizerModelIndex;
		$this->session = JFactory::getSession();
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
		$query = $this->dbo->getQuery(true);
		$query->select('DISTINCT text1.value as vorname, text2.value as nachname, text3.value as titel');
		$query->from('FROM #__thm_groups_text as text1 ');
		$query->innerJoin('#__thm_groups_text as text2 ON text1.userid = text2.userid');
		$query->innerJoin('#__thm_groups_text as text3 ON text2.userid = text3.userid');
		$query->where('text1.structid=1');
		$query->where('text2.structid=2');
		$query->where('text3.structid=5');
		$query->where("text1.userid='$userid'");
		$this->dbo->setQuery((string) $query);
		$rows = $this->dbo->loadObjectList();

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
		$query = $this->dbo->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_lecturers as lecturers');
		$query->where("lecturers.id = $lecturerId ");
		$this->dbo->setQuery($query);

		$rows = $this->dbo->loadObjectList();

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
	public function getCourseIDList()
	{
		// Perform a soap request, in order to get all related courses
		$client = new THM_OrganizerLSFClient(
											 $this->globParams->get('webserviceUri'),
											 $this->globParams->get('webserviceUsername'),
											 $this->globParams->get('webservicePassword')
											);
		$config = $this->groupsModel->getLsfConfiguration();
		$xml = $client->getModules(
								   $config[0]->lsf_object,
								   $config[0]->lsf_study_path,
								   $config[0]->lsf_degree,
								   $config[0]->po
								  );

		$courseIDList = '';
		if (isset($xml))
		{
			// Iterate over each couse group
			foreach ($xml->gruppe as $gruppe)
			{
				if ($gruppe->modulliste->modul[0] == null)
				{
					$courseIDList .= "'$gruppe->pordid', ";
				}
				else
				{
					foreach ($gruppe->modulliste->modul as $modul)
					{
						$courseIDList .= "'$modul->modulid', ";
					}
				}
			}
			if (!empty($courseIDList) )
			{
				return substr($courseIDList, 0, strlen($courseIDList) - 2);
			}
		}

		return $courseIDList;
	}

	/**
	 * Method to get the data
	 *
	 * @return  mixed
	 */
	public function getData()
	{
		$config = $this->groupsModel->getLsfConfiguration();
		$this->major = $this->groupsCurriculum->getMajorRecord($config[0]->id);

		$filter = self::filter();
		$courseIDList = self::getCourseIDList();

		// Build the sql statement in order to get all lecturers of the current major
		$query = $this->dbo->getQuery(true);
		$query->select('DISTINCT  *');
		$query->from('#__thm_organizer_lecturers_assets AS lecturers_assets');
		$query->innerJoin('#__thm_organizer_assets AS assets ON assets.id = lecturers_assets.modul_id');
		$query->where("lsf_course_id IN ($courseIDList)");
		$query->where("$filter");
		$query->where('lecturer_type = 2');
		$query->group('lecturer_id');
		$this->dbo->setQuery((string) $query);
		$lecturers = $this->dbo->loadObjectList();

		$dozenten = array(array());
		$index = 0;

		// Iterate over each lecturer
		foreach ($lecturers as $lecturer)
		{
			$j = 0;
			$lecturerInfo = self::buildLecturerLink($lecturer->lecturer_id, $viewName = 'lecturers');

			// Build the lecturer name
			if ($lecturer->lecturer_id != "")
			{
				$dozenten[$index]['lecturer'] = $lecturerInfo['link'];
				$dozenten[$index]['lecturer_sort'] = $lecturerInfo['name'];
				$dozenten[$index]['title'] = $lecturerInfo['title'];
			}
			else
			{
				$dozenten[$index]['lecturer'] = "nicht zugeordnet";
				$dozenten[$index]['lecturer_sort'] = "nicht zugeordnet";
			}

			$dozenten[$index][$j] = array();

			// Determine related courses of this lecturer
			$query = $this->dbo->getQuery(true);
			$query->select('DISTINCT modul_id');
			$query->from('#__thm_organizer_lecturers_assets AS lecturers_assets');
			$query->innerJoin('#__thm_organizer_assets AS assets ON assets.id = lecturers_assets.modul_id');
			$query->where("lecturer_id = '$lecturer->lecturer_id'");
			$query->where('lecturer_type = 2');
			$query->where("lsf_course_id IN ($courseIDList)");
			$query->where("$filter");
			$this->dbo->setQuery((string) $query);
			$courses = $this->dbo->loadObjectList();

			/* iterate over each related course */
			foreach ($courses as $key => $course)
			{
				$moduldb = self::getModule($course->modul_id);

				if ($lecturer->lecturer_id == "")
				{
					$query = $this->dbo->getQuery(true);
					$query->select('COUNT(*) as count');
					$query->from('#__thm_organizer_lecturers_assets');
					$query->where("modul_id='$course->modul_id'");
					$this->dbo->setQuery((string) $query);
					$count = $this->dbo->loadObjectList();

					if ($count[0]->count > 1)
					{
						continue;
					}
				}

				$dozenten[$index][$j]['course_code'] = $this->groupsModel->buildCourseDetailLink($moduldb);
				$dozenten[$index][$j]['origmodulnummer'] = $moduldb->lsf_course_id;

				// Set the correct language
				if ($this->lang == 'de')
				{
					$dozenten[$index][$j]['title'] = $moduldb->title_de;
				}
				else
				{
					$dozenten[$index][$j]['title'] = $moduldb->title_en;
				}

				$courseCode = $moduldb->lsf_course_code ? $moduldb->lsf_course_code : ($moduldb->his_course_code ?
								$moduldb->his_course_code : $moduldb->lsf_course_id);
				$moduleURL = "index.php?option=com_thm_organizer&view=details&Itemid=" . JRequest::getVar('Itemid');
				$moduleURL .= "&lang=" . $this->lang . "&id=" . $moduldb->lsf_course_idL;
				$moduleLink = JRoute::_($moduleURL);
				$dozenten[$index][$j]['title'] = "<a href='$moduleLink'>" . $dozenten[$index][$j]['title'] . "</a> ($courseCode)";

				$dozenten[$index][$j]['creditpoints'] = $moduldb->min_creditpoints . " CrP";
				$dozenten[$index][$j]['schedule'] = $this->groupsModel->getSchedulerTooltip(
						strtolower(
								$moduldb->lsf_course_code
						), $this->major[0]['organizer_major']
				);

				$j++;
			}
			$index++;
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
		$query = $this->dbo->getQuery(true);
		$query->select('*');
		$query->from('#__thm_organizer_assets');
		$query->where("id = '$modulnummer'");
		$this->dbo->setQuery((string) $query);
		return $this->dbo->loadObject();
	}

	/**
	 * Method to sort by the responsible (usort callback)
	 *
	 * @param   Object  $thingOne  An object
	 * @param   Object  $thingTwo  Another object
	 *
	 * @return  Integer Returns < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
	 */
	public function cmpDozenten($thingOne, $thingTwo)
	{
		return strcmp($thingOne['lecturer_sort'], $thingTwo['lecturer_sort']);
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
		for ($index = 0; $index < count($dozenten); $index++)
		{
			for ($j = 0; $j < count($dozenten[$index]); $j++)
			{
				$arr = array();
				if (isset($dozenten[$index][$j]))
				{
					$arr['id'] = $dozenten[$index][$j]['origmodulnummer'];
					$arr['link'] = JRoute::_("index.php?option=com_thm_organizer&view=details&id=" . $dozenten[$index][$j]['origmodulnummer']);
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
		$filter = "(";
		if ($this->globParams->get('filter') == 1)
		{
			$filterModuleList = $this->globParams->get('modulecodeFilterList');
			$filterValues = explode(',', $filterModuleList);

			$last_item = each(end($filterValues));
			reset($filterValues);

			foreach ($filterValues as $key => $value)
			{
				$val = (int) $value;
				$filter .= "(his_course_code <> $val AND lsf_course_code <> '$value' ) ";

				if (!($value == $last_item['value']) OR !($key == $last_item['key']))
				{
					$filter .= "AND ";
				}
			}
		}
		$filter .= ')';

		return $filter;
	}
}
