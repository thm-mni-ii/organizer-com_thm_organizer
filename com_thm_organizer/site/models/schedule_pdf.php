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
class THM_OrganizerModelSchedule_PDF extends JModelLegacy
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
		$this->setParameters();
		$this->setGrid();
		$this->setTitles();
		$this->lessons = THM_OrganizerHelperSchedule::getLessons($this->parameters);
	}

	/**
	 * Attempts to retrieve the titles for the document and page
	 *
	 * @return array the document and page names
	 */
	private function getPoolTitles()
	{
		$poolID       = array_values($this->parameters['poolIDs'])[0];
		$defaultNames = array('docTitle' => 'Schedule_', 'pageTitle' => '');

		if (empty($poolID))
		{
			return $defaultNames;
		}

		$table = JTable::getInstance('plan_pools', 'thm_organizerTable');

		try
		{
			$success = $table->load($poolID);
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_DATABASE_ERROR'), 'error');

			return $defaultNames;
		}

		if (!$success)
		{
			return $defaultNames;
		}

		return array('docTitle' => $table->gpuntisID . '_', 'pageTitle' => $table->full_name);
	}

	/**
	 * Attempts to retrieve the titles for the document and page
	 *
	 * @return array the document and page names
	 */
	private function getRoomTitles()
	{
		$roomID       = array_values($this->parameters['roomIDs'])[0];
		$defaultNames = array('docTitle' => 'Schedule_', 'pageTitle' => '');

		if (empty($roomID))
		{
			return $defaultNames;
		}

		$table = JTable::getInstance('rooms', 'thm_organizerTable');

		try
		{
			$success = $table->load($roomID);
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_DATABASE_ERROR'), 'error');

			return $defaultNames;
		}

		if (!$success)
		{
			return $defaultNames;
		}

		$docTitle = JApplicationHelper::stringURLSafe($table->name);

		return array('docTitle' => $docTitle . '_', 'pageTitle' => $table->name);
	}

	/**
	 * Attempts to retrieve the titles for the document and page
	 *
	 * @return array the document and page names
	 */
	private function getSubjectTitles()
	{
		$subjectID    = array_values($this->parameters['subjectIDs'])[0];
		$defaultNames = array('docTitle' => 'Schedule_', 'pageTitle' => '');

		if (empty($subjectID))
		{
			return $defaultNames;
		}

		$tag = THM_OrganizerHelperLanguage::getShortTag();

		$query = $this->_db->getQuery(true);
		$query->select("ps.name AS psName, s.short_name_$tag AS shortName, s.name_$tag AS name");
		$query->from('#__thm_organizer_plan_subjects AS ps');
		$query->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ps.id');
		$query->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id');
		$query->where("ps.id = '$subjectID'");
		$this->_db->setQuery($query);

		try
		{
			$subjectNames = $this->_db->loadAssoc();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_DATABASE_ERROR'), 'error');

			return $defaultNames;
		}

		if (empty($subjectNames))
		{
			return $defaultNames;
		}

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

		$docTitle = JApplicationHelper::stringURLSafe($name);

		return array('docTitle' => $docTitle . '_', 'pageTitle' => $name);
	}

	/**
	 * Attempts to retrieve the titles for the document and page
	 *
	 * @return array the document and page names
	 */
	private function getTeacherTitles()
	{
		$teacherID    = array_values($this->parameters['teacherIDs'])[0];
		$defaultNames = array('docTitle' => 'Schedule_', 'pageTitle' => '');

		if (empty($teacherID))
		{
			return $defaultNames;
		}

		$rawTitle = THM_OrganizerHelperTeachers::getDefaultName($teacherID);

		if (empty($rawTitle))
		{
			return $defaultNames;
		}

		$docTitle = JApplicationHelper::stringURLSafe($rawTitle);

		return array('docTitle' => $docTitle . '_', 'pageTitle' => $rawTitle);
	}

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
		$parameters = array();

		$input = JFactory::getApplication()->input;

		// These 5 lines are only here for the test phase to pass parameters
		$params = JFactory::getApplication()->getMenu()->getActive()->params;

		foreach ($params as $key => $value)
		{
			$input->set($key, $value);
		}

		$this->setResourceArray('pool', $parameters);
		$this->setResourceArray('teacher', $parameters);
		$this->setResourceArray('room', $parameters);
		$this->setResourceArray('subject', $parameters);
		$this->setResourceArray('lesson', $parameters);
		$this->setResourceArray('instance', $parameters);


		$parameters['mySchedule'] = $input->getBool('mySchedule', false);

		$allowedLengths               = array('day', 'week', 'month', 'period');
		$rawLength                    = $input->getString('scheduleLength', 'week');
		$parameters['scheduleLength'] = in_array($rawLength, $allowedLengths) ? $rawLength : 'week';

		$rawDate            = $input->getString('scheduleLength', 'week');
		$parameters['date'] = (!empty($rawDate) AND preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $rawDate) === 1) ? $rawDate : date('Y-m-d');

		$formats              = array('pdf', 'ics');
		$rawFormat            = $input->getString('format', 'pdf');
		$parameters['format'] = in_array($rawFormat, $formats) ? $rawFormat : 'pdf';

		switch ($parameters['format'])
		{
			case 'ics':
				break;
			case 'pdf':
				$parameters['paperFormat'] = $input->getString('paperFormat', 'A4');
			default:
				$parameters['gridID'] = $input->getInt('gridID', 0);
				break;
		}

		$this->parameters = $parameters;
	}

	/**
	 * Checks for ids for a given resource type and sets them in the parameters
	 *
	 * @param string $resourceName the name of the resource type
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
	 * Sets the document and page titles
	 */
	private function setTitles()
	{
		$useMySchedule = !empty($this->parameters['mySchedule']);
		$useLessons    = (!empty($this->parameters['lessonIDs']));
		$useInstances  = (!empty($this->parameters['instanceIDs']));
		$usePools      = (!empty($this->parameters['poolIDs']) AND count($this->parameters['poolIDs']) === 1);
		$useTeachers   = (!empty($this->parameters['teacherIDs']) AND count($this->parameters['teacherIDs']) === 1);
		$useRooms      = (!empty($this->parameters['roomIDs']) AND count($this->parameters['roomIDs']) === 1);
		$useSubjects   = (!empty($this->parameters['subjectIDs']) AND count($this->parameters['subjectIDs']) === 1);

		if ($useMySchedule)
		{
			$docTitle  = 'mySchedule_';
			$pageTitle = JText::_('COM_THM_ORGANIZER_MY_SCHEDULE');
		}
		elseif ((!$useLessons AND !$useInstances) AND ($usePools XOR $useTeachers XOR $useRooms XOR $useSubjects))
		{
			if ($usePools)
			{
				$pool      = $this->getPoolTitles();
				$docTitle  = $pool['docTitle'];
				$pageTitle = $pool['pageTitle'];
			}

			if ($useTeachers)
			{
				$teacher   = $this->getTeacherTitles();
				$docTitle  = $teacher['docTitle'];
				$pageTitle = $teacher['pageTitle'];
			}

			if ($useRooms)
			{
				$room      = $this->getRoomTitles();
				$docTitle  = $room['docTitle'];
				$pageTitle = $room['pageTitle'];
			}

			if ($useSubjects)
			{
				$subject   = $this->getSubjectTitles();
				$docTitle  = $subject['docTitle'];
				$pageTitle = $subject['pageTitle'];
			}
		}
		else
		{
			$docTitle  = 'Schedule_';
			$pageTitle = '';
		}

		if (!empty($pageTitle))
		{
			$pageTitle .= " ";
		}


		$dates = THM_OrganizerHelperSchedule::getDates($this->parameters);

		$this->parameters['docTitle']  = $docTitle . ' ' . $dates['startDate'];
		$this->parameters['pageTitle'] = $pageTitle;

		$timeConstant       = 'COM_THM_ORGANIZER_' . strtoupper($this->parameters['scheduleLength']) . '_PLAN';
		$this->parameters['headerString'] = JText::_($timeConstant) . ": " . THM_OrganizerHelperComponent::formatDate($dates['startDate']);
	}
}
