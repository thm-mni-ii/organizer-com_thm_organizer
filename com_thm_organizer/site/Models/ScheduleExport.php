<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Joomla\CMS\Application\ApplicationHelper;
use Organizer\Helpers as Helpers;
use Organizer\Tables as Tables;

/**
 * Class retrieves information for the creation of a schedule export form.
 */
class ScheduleExport extends BaseModel
{
	public $defaultGrid = 1;

	public $docTitle;

	public $grid;

	public $lessons;

	public $pageTitle;

	public $parameters;

	/**
	 * Schedule_Export constructor.
	 *
	 * @param   array  $config
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);
		$format        = Helpers\Input::getCMD('format', 'html');
		$lessonFormats = ['pdf', 'ics', 'xls'];

		// Don't bother setting these variables for html and raw formats
		if (in_array($format, $lessonFormats))
		{
			$this->setParameters();

			if ($format === 'pdf')
			{
				$this->setGrid();
			}

			$this->setTitles();
			$this->lessons = Helpers\Schedules::getLessons($this->parameters);
		}
	}

	/**
	 * Retrieves department options
	 *
	 * @return array an array of department options
	 */
	public function getDepartmentOptions()
	{
		$departments = Helpers\Departments::getOptions(false);
		$options     = [];
		$options[''] = Helpers\Languages::_('THM_ORGANIZER_SELECT_DEPARTMENT');

		foreach ($departments as $departmentID => $departmentName)
		{
			$options[$departmentID] = $departmentName;
		}

		return $options;
	}

	/**
	 * Retrieves grid options
	 *
	 * @return array an array of grid options
	 */
	public function getGridOptions()
	{
		$tag   = Helpers\Languages::getTag();
		$query = $this->_db->getQuery(true);
		$query->select("id, name_$tag AS name, defaultGrid")->from('#__thm_organizer_grids');
		$this->_db->setQuery($query);

		$options = [];

		$grids = Helpers\OrganizerHelper::executeQuery('loadAssocList', []);

		foreach ($grids as $grid)
		{
			if ($grid['defaultGrid'])
			{
				$this->defaultGrid = $grid['id'];
			}

			$options[$grid['id']] = $grid['name'];
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
		$titles  = ['docTitle' => '', 'pageTitle' => ''];
		$poolIDs = array_values($this->parameters['poolIDs']);

		if (empty($poolIDs))
		{
			return $titles;
		}

		$table       = new Tables\Groups;
		$oneResource = count($poolIDs) === 1;

		foreach ($poolIDs as $poolID)
		{
			if ($table->load($poolID))
			{
				$untisID = ApplicationHelper::stringURLSafe($table->untisID);

				if ($oneResource)
				{
					$titles['docTitle']  = $untisID . '_';
					$titles['pageTitle'] = $table->fullName;

					return $titles;
				}

				$titles['docTitle']  .= $untisID . '_';
				$titles['pageTitle'] .= empty($titles['pageTitle']) ? $table->untisID : ", {$table->untisID}";
			}
		}

		return $titles;
	}

	/**
	 * Attempts to retrieve the titles for the document and page
	 *
	 * @return array the document and page names
	 */
	private function getRoomTitles()
	{
		$titles  = ['docTitle' => '', 'pageTitle' => ''];
		$roomIDs = array_values($this->parameters['roomIDs']);

		if (empty($roomIDs))
		{
			return $titles;
		}

		$table       = new Tables\Rooms;
		$oneResource = count($roomIDs) === 1;

		foreach ($roomIDs as $roomID)
		{
			if ($table->load($roomID))
			{
				$untisID = ApplicationHelper::stringURLSafe($table->untisID);

				if ($oneResource)
				{
					$titles['docTitle']  = $untisID . '_';
					$titles['pageTitle'] = $table->name;

					return $titles;
				}

				$titles['docTitle']  .= $untisID . '_';
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
		$courseIDs = array_values($this->parameters['courseIDs']);
		$titles    = ['docTitle' => '', 'pageTitle' => ''];

		if (empty($courseIDs))
		{
			return $titles;
		}

		$oneResource = count($courseIDs) === 1;
		$tag         = Helpers\Languages::getTag();

		$query = $this->_db->getQuery(true);
		$query->select('co.name AS courseName, co.untisID AS untisID')
			->select("s.shortName_$tag AS shortName, s.name_$tag AS name")
			->from('#__thm_organizer_courses AS co')
			->leftJoin('#__thm_organizer_subject_mappings AS sm ON co.id = sm.courseID')
			->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id');

		foreach ($courseIDs as $courseID)
		{
			$query->clear('where');
			$query->where("co.id = '$courseID'");
			$this->_db->setQuery($query);
			$courseNames = Helpers\OrganizerHelper::executeQuery('loadAssoc', []);

			if (!empty($courseNames))
			{
				$untisID = ApplicationHelper::stringURLSafe($courseNames['untisID']);

				if (empty($courseNames['name']))
				{
					if (empty($courseNames['shortName']))
					{
						$name = $courseNames['courseName'];
					}
					else
					{
						$name = $courseNames['shortName'];
					}
				}
				else
				{
					$name = $courseNames['name'];
				}

				if ($oneResource)
				{
					$titles['docTitle']  = $untisID . '_';
					$titles['pageTitle'] = $name;

					return $titles;
				}

				$titles['docTitle']  .= $untisID . '_';
				$titles['pageTitle'] .= empty($titles['pageTitle']) ? $untisID : ", {$untisID}";
			}
		}

		return $titles;
	}

	/**
	 * Attempts to retrieve the titles for the document and page
	 *
	 * @return array the document and page names
	 */
	private function getPersonTitles()
	{
		$titles    = ['docTitle' => '', 'pageTitle' => ''];
		$personIDs = array_values($this->parameters['personIDs']);

		if (empty($personIDs))
		{
			return $titles;
		}

		$table       = new Tables\Persons;
		$oneResource = count($personIDs) === 1;

		foreach ($personIDs as $personID)
		{
			if ($table->load($personID))
			{
				if ($oneResource)
				{
					$displayName         = Helpers\Persons::getDefaultName($personID);
					$titles['docTitle']  = ApplicationHelper::stringURLSafe($displayName) . '_';
					$titles['pageTitle'] = $displayName;

					return $titles;
				}

				$displayName         = Helpers\Persons::getLNFName($personID, true);
				$untisID             = ApplicationHelper::stringURLSafe($table->untisID);
				$titles['docTitle']  .= $untisID . '_';
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

		$rawGrid = Helpers\OrganizerHelper::executeQuery('loadResult');
		if (empty($rawGrid))
		{
			return;
		}

		$gridSettings = json_decode($rawGrid, true);

		if (!empty($gridSettings['periods']))
		{
			$this->grid = $gridSettings['periods'];
		}

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
		$parameters                  = [];
		$parameters['departmentIDs'] = Helpers\Input::getFilterIDs('department');
		$parameters['format']        = Helpers\Input::getCMD('format', 'pdf');
		$parameters['mySchedule']    = Helpers\Input::getBool('myschedule', false);

		if (empty($parameters['mySchedule']))
		{
			if (count($poolIDs = Helpers\Input::getFilterIDs('pool')))
			{
				$parameters["poolIDs"] = [$poolIDs];
			}
			if (count($personIDs = Helpers\Input::getFilterIDs('person')))
			{
				$parameters["personIDs"] = [$personIDs];
			}
			if (count($roomIDs = Helpers\Input::getFilterIDs('room')))
			{
				$parameters["roomIDs"] = [$roomIDs];
			}
		}

		$parameters['userID'] = Helpers\Users::getUser()->id;

		$allowedIntervals       = ['day', 'week', 'month', 'semester', 'custom'];
		$reqInterval            = Helpers\Input::getCMD('interval');
		$parameters['interval'] = in_array($reqInterval, $allowedIntervals) ? $reqInterval : 'week';

		$parameters['date'] = Helpers\Dates::standardizeDate(Helpers\Input::getCMD('date'));

		switch ($parameters['format'])
		{
			case 'pdf':
				$parameters['documentFormat'] = Helpers\Input::getCMD('documentFormat', 'a4');
				$parameters['displayFormat']  = Helpers\Input::getCMD('displayFormat', 'schedule');
				$parameters['gridID']         = Helpers\Input::getInt('gridID');
				$parameters['grouping']       = Helpers\Input::getInt('grouping', 1);
				$parameters['pdfWeekFormat']  = Helpers\Input::getCMD('pdfWeekFormat', 'sequence');
				$parameters['titles']         = Helpers\Input::getInt('titles', 1);
				break;
			case 'xls':
				$parameters['documentFormat'] = Helpers\Input::getCMD('documentFormat', 'si');
				$parameters['xlsWeekFormat']  = Helpers\Input::getCMD('xlsWeekFormat', 'sequence');
				break;
		}

		$parameters['delta'] = false;

		$this->parameters = $parameters;
	}

	/**
	 * Sets the document and page titles
	 *
	 * @return void sets object variables
	 */
	private function setTitles()
	{
		$docTitle      = Helpers\Languages::_('THM_ORGANIZER_SCHEDULE') . '_';
		$pageTitle     = '';
		$useMySchedule = !empty($this->parameters['mySchedule']);
		$useLessons    = !empty($this->parameters['lessonIDs']);
		$useInstances  = !empty($this->parameters['instanceIDs']);
		$usePools      = !empty($this->parameters['poolIDs']);
		$usePersons    = !empty($this->parameters['personIDs']);
		$useRooms      = !empty($this->parameters['roomIDs']);
		$useSubjects   = !empty($this->parameters['subjectIDs']);

		if ($useMySchedule)
		{
			$docTitle  = 'mySchedule_';
			$pageTitle = Helpers\Languages::_('THM_ORGANIZER_MY_SCHEDULE');
		}
		elseif ((!$useLessons and !$useInstances) and ($usePools xor $usePersons xor $useRooms xor $useSubjects))
		{
			if ($usePools)
			{
				$titles    = $this->getPoolTitles();
				$docTitle  .= $titles['docTitle'];
				$pageTitle .= empty($pageTitle) ? $titles['pageTitle'] : ", {$titles['pageTitle']}";
			}

			if ($usePersons)
			{
				$titles    = $this->getPersonTitles();
				$docTitle  .= $titles['docTitle'];
				$pageTitle .= empty($pageTitle) ? $titles['pageTitle'] : ", {$titles['pageTitle']}";
			}

			if ($useRooms)
			{
				$titles    = $this->getRoomTitles();
				$docTitle  .= $titles['docTitle'];
				$pageTitle .= empty($pageTitle) ? $titles['pageTitle'] : ", {$titles['pageTitle']}";
			}

			if ($useSubjects)
			{
				$titles    = $this->getSubjectTitles();
				$docTitle  .= $titles['docTitle'];
				$pageTitle .= empty($pageTitle) ? $titles['pageTitle'] : ", {$titles['pageTitle']}";
			}
		}
		else
		{
			$docTitle  = 'Schedule_';
			$pageTitle = '';
		}

		// Constructed docTitle always ends with a '_' character at this point.
		$this->parameters['docTitle']  = $docTitle . date('Ymd');
		$this->parameters['pageTitle'] = $pageTitle;
	}
}
