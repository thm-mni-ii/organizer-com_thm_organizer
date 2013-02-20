<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		THM_OrganizerModelCurriculum
 * @description THM_OrganizerModelCurriculum component site model
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.model');
jimport('joomla.filesystem.path');
jimport('joomla.application.component.modellist');

require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper/module.php';
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper/lsfapi.php';
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper/ModuleList.php';
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'models/groups.php';

/**
 * Class THM_OrganizerModelCurriculum for component com_thm_organizer
 *
 * Class provides methods to work with the database and other cool stuff
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerModelCurriculum extends JModelList
{
	/**
	 * Database
	 *
	 * @var    Object
	 * @since  1.0
	 */
	protected $db = null;

	/**
	 * Constructor to set up the class variables and call the parent constructor
	 */
	public function __construct()
	{
		$this->db = JFactory::getDBO();

		parent::__construct();
	}

	/**
	 * Method to get the major records by major id
	 *
	 * @param   Integer  $majorId  Major id
	 *
	 * @return  Array
	 */
	public function getMajorRecord($majorId)
	{
		// Build the sql statement
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->select("#__thm_organizer_degrees.name as degree");
		$query->from('#__thm_organizer_majors');
		$query->join('cross', '#__thm_organizer_degrees ON #__thm_organizer_degrees.id = #__thm_organizer_majors.degree_id');
		$query->where('#__thm_organizer_majors.id = ' . $majorId);
		$db->setQuery($query);

		return $db->loadAssocList();
	}

	/**
	 * Method to get the responsible records
	 *
	 * @param   Integer  $assetId  ID of a module
	 *
	 * @return Object
	 */
	public function getResponsibleRecord($assetId)
	{
		// Build the sql statement
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_lecturers as lecturers');
		$query->join('inner', '#__thm_organizer_lecturers_assets as lecturers_assets ON lecturers.id = lecturers_assets.lecturer_id ');
		$query->join('inner', '#__thm_organizer_assets as assets ON assets.id = lecturers_assets.modul_id');
		$query->where("assets.lsf_course_id = $assetId ");
		$query->where("lecturers_assets.lecturer_type = 1 ");
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to get the groups picture
	 *
	 * @param   Integer  $assetId  ID of a module
	 *
	 * @return  Ambiguous JHTML, empty String
	 */
	public function getGroupsPicture($assetId)
	{
		// Gets an instance of the Groups-Controller
		$groupsModel = new THM_OrganizerModelGroups;
		$rows = self::getResponsibleRecord($assetId);

		if (isset($rows[0]))
		{
			$userid = $groupsModel->getUserIdFromGroups($rows[0]->userid);

			// Build the sql statement
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			$query->select("*");
			$query->from('#__thm_groups_picture');
			$query->where("userid = $userid ORDER BY structid DESC");
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if (isset($rows[0]))
			{
				if ($rows[0]->value != "")
				{
					$path = JURI::base() . "components/com_thm_groups/img/portraits/" . $rows[0]->value;
					return JHTML::image("$path", "test", array());
				}
			}
		}
		else
		{
			return "";
		}
	}

	/**
	 * Method to determine the name of a given module
	 *
	 * @param   Integer  $assetId  ID of a module
	 *
	 * @return  String
	 */
	public function getLecturerName($assetId)
	{
		// Gets an instance of the Groups-Controller
		$groupsModel = new THM_OrganizerModelGroups;
		$rows = self::getResponsibleRecord($assetId);

		if (isset($rows[0]))
		{
			$userid = $groupsModel->getUserIdFromGroups($rows[0]->userid);

			if (!$userid)
			{
				return $rows[0]->academic_title . " " . $rows[0]->forename . " " . $rows[0]->surname;
			}
			else
			{
				$lecturerName = $groupsModel->getLecturerNameFromThmGroups($userid);
				return $lecturerName . "<br>" . self::getGroupsPicture($userid);
			}
		}
	}

	/**
	 * Method to build the link to a user profile of THM Groups
	 *
	 * @param   Integer  $assetId   ID of a module
	 * @param   String   $viewName  The name of the current view (default 'curriculum')
	 *
	 * @return  String
	 */
	public function buildResponsibleLink($assetId, $viewName = 'curriculum')
	{
		$db = JFactory::getDBO();
		$groupsModel = new THM_OrganizerModelGroups;

		if (!isset($assetId) && $assetId == "")
		{
			return;
		}

		// Build the sql statement
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_lecturers as lecturers');
		$query->join('inner', '#__thm_organizer_lecturers_assets as lecturers_assets ON lecturers.id = lecturers_assets.lecturer_id ');
		$query->join('inner', '#__thm_organizer_assets as assets ON assets.id = lecturers_assets.modul_id');
		$query->where("assets.lsf_course_id = $assetId ");
		$query->where("lecturers_assets.lecturer_type = 1 ");
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Get the user id fron the THM Groups Extension
		if (isset($rows[0]))
		{
			$userid = $groupsModel->getUserIdFromGroups($rows[0]->userid);
			if ($userid)
			{
				$linkTarget = 'index.php?option=com_thm_groups&view=profile&layout=default';
				return JRoute::_($linkTarget . '&gsuid=' . $userid . '&name=' . trim($rows[0]->surname) . "&Itemid=" . JRequest::getVar('Itemid'));
			}
		}
	}

	/**
	 * Method to traverse the tree of a given asset and semester
	 *
	 * @param   Integer  $id  		ID of a module
	 * @param   Integer  $semester  ID of a semester
	 *
	 * @return Array
	 */
	public function categoryChild($id, $semester)
	{
		$db = JFactory::getDBO();
		$groupsModel = new THM_OrganizerModelGroups;

		$query = $db->getQuery(true);
		$query->select("*, #__thm_organizer_assets_tree.ecollaboration_link as ecollaboration_link_instance,
				#__thm_organizer_assets_tree.color_id as color_id_instance,
				#__thm_organizer_assets_tree.menu_link as menu_link_instance,
				#__thm_organizer_assets_tree.note as note_instance,
				#__thm_organizer_assets.ecollaboration_link as ecollaboration_link_object,
				#__thm_organizer_assets.color_id as color_id_object,
				#__thm_organizer_assets.menu_link as menu_link_object,
				#__thm_organizer_assets.note as note_object");

		// Determine the correct labels, depening on the desired language
		if (JRequest::getVar('lang') == 'de')
		{
			$query->select("title_de as title");
			$query->select("short_title_de as short_title");
		}
		else
		{
			$query->select("title_en as title");
			$query->select("short_title_en as short_title");
		}

		// Build the SQL query
		$query->select("#__thm_organizer_colors.color as color_hex");
		$query->select("#__thm_organizer_assets_semesters.id as semesters_majors_id");
		$query->from('#__thm_organizer_assets_tree');
		$query->join('inner', '#__thm_organizer_assets ON #__thm_organizer_assets_tree.asset = #__thm_organizer_assets.id');
		$query->join('inner', '#__thm_organizer_assets_semesters ' .
				'ON #__thm_organizer_assets_tree.id = #__thm_organizer_assets_semesters.assets_tree_id'
		);
		$query->join('inner', '#__thm_organizer_colors ON #__thm_organizer_assets_tree.color_id = #__thm_organizer_colors.id');
		$query->where("parent_id = $id");
		$query->where("published = 1");
		$query->where("#__thm_organizer_assets_semesters.semesters_majors_id= $semester");

		$query->order("ordering ASC");

		$db->setQuery($query);
		$subtree = $db->loadAssocList();

		$children = array();
		if (count($subtree) > 0)
		{
			foreach ($subtree as $row)
			{
				$arr = self::categoryChild($row['asset'], $semester);

				// If an asset has children, then recursively traverse its sub tree
				if (count($arr) > 0)
				{
					$row['childs'] = array();
					array_push($row['childs'], self::categoryChild($row['asset'], $semester));
				}

				if ($row['color_id_flag'] == 1)
				{
					$row['color_hex'] = self::getColorHex($row['color_id_object']);
				}
				else
				{
					$row['color_hex'] = self::getColorHex($row['color_id_instance']);
				}

				if ($row['menu_link_flag'] == 1)
				{
					$row['menu_link'] = $row['menu_link_object'];
				}
				else
				{
					$row['menu_link'] = $row['menu_link_instance'];
				}

				if ($row['ecollaboration_link_flag'] == 1)
				{
					$row['ecollaboration_link'] = $row['ecollaboration_link_object'];
				}
				else
				{
					$row['ecollaboration_link'] = $row['ecollaboration_link_instance'];
				}

				if ($row['note_flag'] == 1)
				{
					$row['note'] = self::convertSEF($row['note_object']);
				}
				else
				{
					$row['note'] = self::convertSEF($row['note_instance']);
				}

				$row['responsible_name'] = self::getLecturerName($row['lsf_course_id']);
				$row['responsible_link'] = self::buildResponsibleLink($row['lsf_course_id']);
				$row['responsible_picture'] = self::getGroupsPicture($row['lsf_course_id']);

				if ($row['lsf_course_code'])
				{
					$row['schedule'] = $groupsModel->getScheduleEvents($row['lsf_course_code'], $this->organizer_major);
				}

				if (!empty($row['menu_link']))
				{
					$row['menu_link'] = JRoute::_("index.php?Itemid=" . $row['menu_link']);
				}

				// Attach the row to the final array
				array_push($children, $row);
			}
		}
		return $children;
	}

	/**
	 * Method to store the ID of the Curriculum to the Session
	 *
	 * @return void
	 */
	public function saveToSession()
	{
		$_SESSION['stud_id'] = JRequest::getVar('id');
	}

	/**
	 * Method to convert the given text to an SEF text
	 *
	 * @param   String  $text  Text
	 *
	 * @return <String>  SEF text
	 */
	public function convertSEF($text)
	{
		// Replace src links
		$base = JURI::base(true) . '/';
		return preg_replace("/(src)=\"(?!http|ftp|https)([^\"]*)\"/", "$1=\"$base\$2\"", $text);
	}

	/**
	 * Method to get the color hex
	 *
	 * @param   Integer  $colorID  The color id
	 *
	 * @return <String>  Color hey
	 */
	public function getColorHex($colorID)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_colors');
		$query->where("id = $colorID");
		$db->setQuery($query);
		$color = $db->loadObjectList();

		if (isset($color[0]) && isset($color[0]->color))
		{
			return $color[0]->color;
		}
		else
		{
			return "ffffff";
		}
	}

	/**
	 * Method to select the Tree of the current major
	 *
	 * @return void
	 */
	public function getJSONCurriculum()
	{
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$groupsModel = new THM_OrganizerModelGroups;

		$id = JRequest::getVar('id');
		$this->majorID = $id;

		// Splits the semester values
		$selectedSemesters = explode(',', JRequest::getVar('semesters'));

		// Get the major in order to build the complete label of a given major/curriculum
		$major = $this->getMajorRecord($id);

		// Set the curriculum label
		$major[0]['full_name'] = $major[0]['degree'] . " " . $major[0]['subject'] . " (" . $major[0]['po'] . ")";

		$this->organizer_major = $major[0]['organizer_major'];

		// Get all related semesters of the current major/curriculum
		$query = $db->getQuery(true);
		$query->select("*");
		$query->select("#__thm_organizer_semesters_majors.id as id");
		$query->select("#__thm_organizer_semesters_majors.major_id as major_id");
		$query->from('#__thm_organizer_semesters_majors');
		$query->join('inner', '#__thm_organizer_semesters ON #__thm_organizer_semesters.id = #__thm_organizer_semesters_majors.semester_id');
		$query->where("major_id = $id");

		$db->setQuery($query);
		$semesters = $db->loadAssocList();

		// Iterate the found semesters
		foreach ($semesters as $key => $semester)
		{
			$semester['note'] = self::convertSEF($semester['note']);
			$sem_color = $semester["color_id"];
			$sem_id = $semester['id'];

			if (!in_array($semester['semester_id'], $selectedSemesters))
			{
				continue;
			}

			$semesterColor = self::getColorHex($sem_color);

			if (isset($semesterColor))
			{
				$semester['color'] = $semesterColor;
			}

			$semester['childs'] = array();
			$query = $db->getQuery(true);

			$query->select("*, #__thm_organizer_assets_tree.ecollaboration_link as ecollaboration_link_instance,
					#__thm_organizer_assets_tree.color_id as color_id_instance,
					#__thm_organizer_assets_tree.menu_link as menu_link_instance,
					#__thm_organizer_assets_tree.note as note_instance,
					#__thm_organizer_assets.ecollaboration_link as ecollaboration_link_object,
					#__thm_organizer_assets.color_id as color_id_object,
					#__thm_organizer_assets.menu_link as menu_link_object,
					#__thm_organizer_assets.note as note_object");

			// Determine the correct labels, depening on the desired language
			if (JRequest::getVar('lang') == 'de')
			{
				$query->select("title_de as title");
				$query->select("short_title_de as short_title");
			}
			else
			{
				$query->select("title_en as title");
				$query->select("short_title_en as short_title");
			}

			$query->select("#__thm_organizer_colors.color as color_hex_instance");
			$query->select("#__thm_organizer_assets_semesters.id as semesters_majors_id");
			$query->from('#__thm_organizer_assets_tree');
			$query->join('inner', '#__thm_organizer_assets ON #__thm_organizer_assets_tree.asset = #__thm_organizer_assets.id');
			$query->join('inner', '#__thm_organizer_assets_semesters ' .
					'ON #__thm_organizer_assets_tree.id = #__thm_organizer_assets_semesters.assets_tree_id'
			);
			$query->join('inner', '#__thm_organizer_colors ON #__thm_organizer_assets_tree.color_id = #__thm_organizer_colors.id');
			$query->where("#__thm_organizer_assets_semesters.semesters_majors_id= $sem_id");
			$query->where("published = 1");

			// @TODO: quick-fix, linage calc anpassen
			$query->where("parent_id = 0");
			$query->order("ordering ASC");
			$db->setQuery($query);
			$assets = $db->loadAssocList();

			foreach ($assets as $asset)
			{
				if ($asset['color_id_flag'] == 1)
				{
					$asset['color_hex'] = self::getColorHex($asset['color_id_object']);
				}
				else
				{
					$asset['color_hex'] = self::getColorHex($asset['color_id_instance']);
				}

				if ($asset['menu_link_flag'] == 1)
				{
					$asset['menu_link'] = $asset['menu_link_object'];
				}
				else
				{
					$asset['menu_link'] = $asset['menu_link_instance'];
				}

				if ($asset['ecollaboration_link_flag'] == 1)
				{
					$asset['ecollaboration_link'] = $asset['ecollaboration_link_object'];
				}
				else
				{
					$asset['ecollaboration_link'] = $asset['ecollaboration_link_instance'];
				}

				if ($asset['note_flag'] == 1)
				{
					$asset['note'] = self::convertSEF($asset['note_object']);
				}
				else
				{
					$asset['note'] = self::convertSEF($asset['note_instance']);
				}

				$asset['responsible_name'] = self::getLecturerName($asset['lsf_course_id']);
				$asset['responsible_link'] = self::buildResponsibleLink($asset['lsf_course_id']);
				$asset['responsible_picture'] = self::getGroupsPicture($asset['lsf_course_id']);

				if ($asset['lsf_course_code'])
				{
					$asset['schedule'] = $groupsModel->getScheduleEvents($asset['lsf_course_code'], $major[0]['organizer_major']);
				}

				if (!empty($asset['menu_link']))
				{
					$asset['menu_link'] = JRoute::_("index.php?Itemid=" . $asset['menu_link']);
				}

				$asset['childs'] = self::categoryChild($asset['asset'], $sem_id);
				array_push($semester['childs'], $asset);
			}

			// Filter a semester, if the semester is not contained in the request
			$orderpos = array_search($semester['semester_id'], $selectedSemesters);
			if ($orderpos !== false)
			{
				$semesters[$orderpos] = $semester;
			}
		}

		$major[0]['childs'] = array();
		array_push($major[0]['childs'], $semesters);

		// Outputs the json encoded curriculum
		echo json_encode($major);

		$mainframe->close();
	}
}
