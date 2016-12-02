<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelSchedule_Export
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/departments.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/programs.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/pools.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/schedule.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/teachers.php';
/** @noinspection PhpIncludeInspection */
//require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class provides methods for retrieving program data
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSchedule_Export extends JModelLegacy
{
	public $docTitle;

	public $grid;

	public $headerString;

	public $lessons;

	public $pageTitle;

	public $parameters;

	public function __construct(array $config)
	{
		parent::__construct($config);
		$format        = JFactory::getApplication()->input->getString('format');
		$lessonFormats = array('pdf', 'ics', 'xls');

		// Don't bother setting these variables for html and raw formats
		if (in_array($format, $lessonFormats))
		{
			$this->setParameters();

			if ($format === 'pdf')
			{
				$this->setGrid();
			}

			$this->setTitles();
			$this->lessons = THM_OrganizerHelperSchedule::getLessons($this->parameters);
		}
	}

	/**
	 * Retrieves department options
	 *
	 * @return array an array of department options
	 */
	public function getDepartmentOptions()
	{
		$departments = THM_OrganizerHelperDepartments::getPlanDepartments(false);
		$options     = array();

		foreach ($departments as $departmentID => $departmentName)
		{
			$option['value'] = $departmentID;
			$option['text']  = $departmentName;
			$options[]       = $option;
		}

		return $options;
	}

	/**
	 * Retrieves pool options
	 *
	 * @return array an array of pool options
	 */
	public function getPoolOptions()
	{
		$pools   = THM_OrganizerHelperPools::getPlanPools(false);
		$options = array();

		foreach ($pools as $poolName => $poolID)
		{
			$option['value'] = $poolID;
			$option['text']  = $poolName;
			$options[]       = $option;
		}

		return $options;
	}

	/**
	 * Attempts to retrieve the titles for the document and page
	 *
	 * @return array the document and page names
	 */
	private function getPoolTitles()
	{
		$titles  = array('docTitle' => '', 'pageTitle' => '');
		$poolIDs = array_values($this->parameters['poolIDs']);

		if (empty($poolIDs))
		{
			return $titles;
		}

		$table       = JTable::getInstance('plan_pools', 'thm_organizerTable');
		$oneResource = count($poolIDs) === 1;

		foreach ($poolIDs as $poolID)
		{
			try
			{
				$success = $table->load($poolID);
			}
			catch (Exception $exc)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_DATABASE_ERROR'), 'error');

				return array();
			}

			if ($success)
			{
				$gpuntisID = JApplicationHelper::stringURLSafe($table->gpuntisID);

				if ($oneResource)
				{
					$titles['docTitle']  = $gpuntisID . '_';
					$titles['pageTitle'] = $table->full_name;

					return $titles;
				}

				$titles['docTitle'] .= $gpuntisID . '_';
				$titles['pageTitle'] .= empty($titles['pageTitle']) ? $table->gpuntisID : ", {$table->gpuntisID}";
			}
		}

		return $titles;
	}

	/**
	 * Retrieves program options
	 *
	 * @return array an array of program options
	 */
	public function getProgramOptions()
	{
		$programs = THM_OrganizerHelperPrograms::getPlanPrograms();
		$options  = array();

		foreach ($programs as $program)
		{
			$option['value'] = $program['id'];
			$option['text']  = empty($program['name']) ? $program['ppName'] : $program['name'];
			$options[]       = $option;
		}

		return $options;
	}

	/**
	 * Checks for ids for a given resource type and sets them in the parameters
	 *
	 * @param string $resourceName the name of the resource type
	 * @param array  &$parameters  the parameters array for the model
	 *
	 * @return array the array of values
	 */
	private function setResourceArray($resourceName, &$parameters)
	{
		$input          = JFactory::getApplication()->input;
		$rawResourceIDs = $input->get("{$resourceName}IDs", array(), 'raw');

		if (!empty($rawResourceIDs))
		{
			if (is_array($rawResourceIDs))
			{
				$filteredArray = array_filter($rawResourceIDs);
				if (!empty($filteredArray))
				{
					$parameters["{$resourceName}IDs"] = $filteredArray;
				}

				return;
			}

			if (is_int($rawResourceIDs))
			{
				$parameters["{$resourceName}IDs"] = array($rawResourceIDs);

				return;
			}

			if (is_string($rawResourceIDs))
			{
				$parameters["{$resourceName}IDs"] = explode(',', $rawResourceIDs);

				return;
			}
		}
	}

	/**
	 * Retrieves teacher options
	 *
	 * @return array an array of teacher options
	 */
	public function getRoomOptions()
	{
		$rooms = THM_OrganizerHelperRooms::getPlanRooms();
		asort($rooms);

		$options = array();

		foreach ($rooms as $roomName => $roomID)
		{
			$option['value'] = $roomID;
			$option['text']  = $roomName;
			$options[]       = $option;
		}

		return $options;
	}

	/**
	 * Attempts to retrieve the titles for the document and page
	 *
	 * @return array the document and page names
	 */
	private function getRoomTitles()
	{
		$titles  = array('docTitle' => '', 'pageTitle' => '');
		$roomIDs = array_values($this->parameters['roomIDs']);

		if (empty($roomIDs))
		{
			return $titles;
		}

		$table       = JTable::getInstance('rooms', 'thm_organizerTable');
		$oneResource = count($roomIDs) === 1;

		foreach ($roomIDs as $roomID)
		{
			try
			{
				$success = $table->load($roomID);
			}
			catch (Exception $exc)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_DATABASE_ERROR'), 'error');

				return array();
			}

			if ($success)
			{
				$gpuntisID = JApplicationHelper::stringURLSafe($table->gpuntisID);

				if ($oneResource)
				{
					$titles['docTitle']  = $gpuntisID . '_';
					$titles['pageTitle'] = $table->name;

					return $titles;
				}

				$titles['docTitle'] .= $gpuntisID . '_';
				$titles['pageTitle'] .= empty($titles['pageTitle']) ? $table->name : ", {$table->name}";
			}
		}

		return $titles;
	}

	/**
	 * Attempts to retrieve the titles for the document and page
	 *
	 * @return array the document and page names
	 */
	private function getSubjectTitles()
	{
		$subjectIDs    = array_values($this->parameters['subjectIDs']);
		$titles = array('docTitle' => '', 'pageTitle' => '');

		if (empty($subjectIDs))
		{
			return $titles;
		}

		$oneResource = count($subjectIDs) === 1;
		$tag = THM_OrganizerHelperLanguage::getShortTag();

		$query = $this->_db->getQuery(true);
		$query->select("ps.name AS psName, ps.gpuntisID as gpuntisID, s.short_name_$tag AS shortName, s.name_$tag AS name");
		$query->from('#__thm_organizer_plan_subjects AS ps');
		$query->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ps.id');
		$query->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id');

		foreach ($subjectIDs as $subjectID)
		{
			$query->clear('where');
			$query->where("ps.id = '$subjectID'");
			$this->_db->setQuery($query);

			try
			{
				$subjectNames = $this->_db->loadAssoc();
			}
			catch (Exception $exc)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_DATABASE_ERROR'), 'error');

				return $titles;
			}

			if (!empty($subjectNames))
			{
				$gpuntisID = JApplicationHelper::stringURLSafe($subjectNames['gpuntisID']);

				if (empty($subjectNames['name']))
				{
					if (empty($subjectNames['shortName']))
					{
						$name = $subjectNames['psName'];
					}
					else
					{
						$name = $subjectNames['shortName'];
					}
				}
				else
				{
					$name = $subjectNames['name'];
				}

				if ($oneResource)
				{
					$titles['docTitle']  = $gpuntisID . '_';
					$titles['pageTitle'] = $name;

					return $titles;
				}

				$titles['docTitle'] .= $gpuntisID . '_';
				$titles['pageTitle'] .= empty($titles['pageTitle']) ? $gpuntisID : ", {$gpuntisID}";
			}

		}

		return $titles;
	}

	/**
	 * Retrieves teacher options
	 *
	 * @return array an array of teacher options
	 */
	public function getTeacherOptions()
	{
		$teachers = THM_OrganizerHelperTeachers::getPlanTeachers(false);

		$options = array();

		foreach ($teachers as $teacherName => $teacherID)
		{
			$option['value'] = $teacherID;
			$option['text']  = $teacherName;
			$options[]       = $option;
		}

		return $options;
	}

	/**
	 * Attempts to retrieve the titles for the document and page
	 *
	 * @return array the document and page names
	 */
	private function getTeacherTitles()
	{
		$titles     = array('docTitle' => '', 'pageTitle' => '');
		$teacherIDs = array_values($this->parameters['teacherIDs']);

		if (empty($teacherIDs))
		{
			return $titles;
		}

		$table       = JTable::getInstance('teachers', 'thm_organizerTable');
		$oneResource = count($teacherIDs) === 1;

		foreach ($teacherIDs as $teacherID)
		{
			try
			{
				$success = $table->load($teacherID);
			}
			catch (Exception $exc)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_DATABASE_ERROR'), 'error');

				return array();
			}

			if ($success)
			{
				if ($oneResource)
				{
					$displayName = THM_OrganizerHelperTeachers::getDefaultName($teacherID);
					$titles['docTitle']  = JApplicationHelper::stringURLSafe($displayName) . '_';
					$titles['pageTitle'] = $displayName;

					return $titles;
				}

				$displayName = THM_OrganizerHelperTeachers::getLNFName($teacherID, true);
				$gpuntisID = JApplicationHelper::stringURLSafe($table->gpuntisID);
				$titles['docTitle'] .= $gpuntisID . '_';
				$titles['pageTitle'] .= empty($titles['pageTitle']) ? $displayName : ", {$displayName}";
			}
		}

		return $titles;
	}

	/**
	 * Retrieves the selected grid from the database
	 *
	 * @return void sets object variables
	 */
	private function setGrid()
	{
		$query = $this->_db->getQuery(true);
		$query->select('grid')->from('#__thm_organizer_grids');

		if (empty($this->parameters['gridID']))
		{
			$query->where("defaultGrid = '1'");
		}
		else
		{
			$query->where("id = '{$this->parameters['gridID']}'");
		}

		$this->_db->setQuery($query);

		try
		{
			$rawGrid = $this->_db->loadResult();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_DATABASE_ERROR'), 'error');

			return;
		}

		$gridSettings                 = json_decode($rawGrid, true);
		$this->grid                   = $gridSettings['periods'];
		$this->parameters['startDay'] = $gridSettings['startDay'];
		$this->parameters['endDay']   = $gridSettings['endDay'];
	}

	/**
	 * Sets the basic parameters from the request
	 *
	 * @return void sets object variables
	 */
	private function setParameters()
	{
		$input = JFactory::getApplication()->input;

		$parameters           = array();
		$parameters['format'] = $input->getString('format', 'pdf');
		$this->setResourceArray('pool', $parameters);
		$this->setResourceArray('teacher', $parameters);
		$this->setResourceArray('room', $parameters);

		$parameters['mySchedule'] = $input->getBool('mySchedule', false);

		$allowedLengths                = array('day', 'week', 'month', 'semester', 'custom');
		$rawLength                     = $input->getString('dateRestriction', 'week');
		$parameters['dateRestriction'] = in_array($rawLength, $allowedLengths) ? $rawLength : 'week';

		$rawDate = $input->getString('date');
		if (empty($rawDate))
		{
			$parameters['date'] = date('Y-m-d');
		}
		else
		{
			$parameters['date'] = THM_OrganizerHelperComponent::standardizeDate($rawDate);
		}

		switch ($parameters['format'])
		{
			case 'pdf':
				$parameters['documentFormat'] = $input->getString('documentFormat', 'A4');
				$parameters['displayFormat']  = $input->getString('displayFormat', 'schedule');
				$parameters['gridID']         = $input->getInt('gridID', 0);
				$parameters['pdfWeekFormat']  = $input->getString('pdfWeekFormat', 'sequence');
				break;
			case 'xls':
				$parameters['xlsWeekFormat'] = $input->getString('xlsWeekFormat', 'sequence');
				break;
		}

		$this->parameters = $parameters;
	}

	/**
	 * Sets the document and page titles
	 *
	 * @return void sets object variables
	 */
	private function setTitles()
	{
		$docTitle      = JText::_('COM_THM_ORGANIZER_SCHEDULE') . '_';
		$pageTitle     = '';
		$useMySchedule = !empty($this->parameters['mySchedule']);
		$useLessons    = !empty($this->parameters['lessonIDs']);
		$useInstances  = !empty($this->parameters['instanceIDs']);
		$usePools      = !empty($this->parameters['poolIDs']);
		$useTeachers   = !empty($this->parameters['teacherIDs']);
		$useRooms      = !empty($this->parameters['roomIDs']);
		$useSubjects   = !empty($this->parameters['subjectIDs']);

		if ($useMySchedule)
		{
			$docTitle  = 'mySchedule_';
			$pageTitle = JText::_('COM_THM_ORGANIZER_MY_SCHEDULE');
		}
		elseif ((!$useLessons AND !$useInstances) AND ($usePools XOR $useTeachers XOR $useRooms XOR $useSubjects))
		{
			if ($usePools)
			{
				$titles = $this->getPoolTitles();
				$docTitle .= $titles['docTitle'];
				$pageTitle .= empty($pageTitle) ? $titles['pageTitle'] : ", {$titles['pageTitle']}";
			}

			if ($useTeachers)
			{
				$titles = $this->getTeacherTitles();
				$docTitle .= $titles['docTitle'];
				$pageTitle .= empty($pageTitle) ? $titles['pageTitle'] : ", {$titles['pageTitle']}";
			}

			if ($useRooms)
			{
				$titles = $this->getRoomTitles();
				$docTitle .= $titles['docTitle'];
				$pageTitle .= empty($pageTitle) ? $titles['pageTitle'] : ", {$titles['pageTitle']}";
			}

			if ($useSubjects)
			{
				$titles = $this->getSubjectTitles();
				$docTitle .= $titles['docTitle'];
				$pageTitle .= empty($pageTitle) ? $titles['pageTitle'] : ", {$titles['pageTitle']}";
			}
		}
		else
		{
			$docTitle  = 'Schedule_';
			$pageTitle = '';
		}

		// Constructed docTitle always ends with a "_" character at this point.
		$this->parameters['docTitle']  = $docTitle . date('Ymd');
		$this->parameters['pageTitle'] = $pageTitle;
	}


}
