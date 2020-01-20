<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers\Validators;

use Exception;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Models\ScheduleXML;
use stdClass;

/**
 * Provides functions for XML unit validation and persistence.
 */
class Schedules
{
	public $categories = null;

	public $creationDate;

	public $creationTime;

	public $departmentID;

	public $errors = [];

	public $events = null;

	public $fields = null;

	public $groups = null;

	public $instances = [];

	public $methods = null;

	public $periods = null;

	public $persons = null;

	public $rooms = null;

	public $roomtypes = null;

	public $schedule = null;

	public $schoolYear = null;

	public $term = null;

	public $termID = null;

	public $units = null;

	public $warnings = [];


	/**
	 * Creates a status report based upon object error and warning messages
	 *
	 * @return void  outputs errors to the application
	 */
	private function printStatusReport()
	{
		if (count($this->errors))
		{
			$errorMessage = \JText::_('ORGANIZER_ERROR_HEADER') . '<br />';
			$errorMessage .= implode('<br />', $this->errors);
			OrganizerHelper::message($errorMessage, 'error');
		}

		if (count($this->warnings))
		{
			OrganizerHelper::message(implode('<br />', $this->warnings), 'warning');
		}
	}

	/**
	 * Checks a given untis schedule xml export for data completeness and consistency. Forms the data into structures
	 * for further processing
	 *
	 * @return bool true on successful validation w/o errors, false if the schedule was invalid or an error occurred
	 * @throws Exception
	 */
	public function validate()
	{
		$this->departmentID = Input::getInt('departmentID');
		$formFiles          = Input::getInput()->files->get('jform', [], 'array');
		$this->schedule     = simplexml_load_file($formFiles['file']['tmp_name']);

		// Unused & mostly unfilled nodes
		unset($this->schedule->lesson_date_schemes, $this->schedule->lesson_tables, $this->schedule->reductions);
		unset($this->schedule->reduction_reasons, $this->schedule->studentgroups, $this->schedule->students);

		$valid = true;

		// Creation Date & Time, school year dates, term attributes
		$this->creationDate = trim((string) $this->schedule[0]['date']);
		$this->creationTime = trim((string) $this->schedule[0]['time']);

		$validCreationDate = $this->validateDate($this->creationDate, 'CREATION_DATE');
		$validCreationTime = $this->validateText($this->creationTime, 'CREATION_TIME');
		$valid             = ($valid and $validCreationDate and $validCreationTime);

		$valid = ($valid and Terms::validate($this, $this->schedule->general));
		unset($this->schedule->general);

		$this->validateResources($valid);

		$this->printStatusReport();

		return (count($this->errors)) ? false : true;
	}

	/**
	 * Validates a date attribute. Setting it to a schedule property if valid.
	 *
	 * @param   string &$value     the attribute value passed by reference because of reformatting to Y-m-d
	 * @param   string  $constant  the unique text constant fragment
	 *
	 * @return bool true on success, otherwise false
	 */
	public function validateDate(&$value, $constant)
	{
		if (empty($value))
		{
			$this->errors[] = Languages::_("ORGANIZER_{$constant}_MISSING");

			return false;
		}

		if ($value = date('Y-m-d', strtotime($value)))
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks a given schedule in gp-untis xml format for data completeness and
	 * consistency and gives it basic structure
	 *
	 * @param   bool  $validTerm  whether or not the term is valid
	 *
	 * @return void true on successful validation w/o errors, false if the schedule was invalid or an error occurred
	 * @throws Exception
	 */
	public function validateResources($validTerm)
	{
		$this->categories = new stdClass;
		foreach ($this->schedule->departments->children() as $node)
		{
			Categories::validate($this, $node);
		}
		unset($this->schedule->departments);

		$this->fields    = new stdClass;
		$this->methods   = new stdClass;
		$this->roomtypes = new stdClass;
		foreach ($this->schedule->descriptions->children() as $node)
		{
			Descriptions::validate($this, $node);
		}
		unset($this->schedule->descriptions);

		$this->periods = new stdClass;
		foreach ($this->schedule->timeperiods->children() as $node)
		{
			Grids::validate($this, $node);
		}
		Grids::setIDs($this);
		unset($this->schedule->timeperiods);

		$this->events = new stdClass;
		foreach ($this->schedule->subjects->children() as $node)
		{
			Events::validate($this, $node);
		}
		Events::setWarnings($this);
		unset($this->schedule->subjects);

		$this->groups = new stdClass;
		foreach ($this->schedule->classes->children() as $node)
		{
			Groups::validate($this, $node);
		}
		unset($this->categories, $this->periods, $this->schedule->classes);

		$this->persons = new stdClass;
		foreach ($this->schedule->teachers->children() as $node)
		{
			Persons::validate($this, $node);
		}
		Persons::setWarnings($this);
		unset($this->fields, $this->schedule->teachers);

		$this->rooms = new stdClass;
		foreach ($this->schedule->rooms->children() as $node)
		{
			Rooms::validate($this, $node);
		}
		Rooms::setWarnings($this);
		unset($this->roomtypes, $this->schedule->rooms);

		if ($validTerm)
		{
			$this->units = new stdClass;
			foreach ($this->schedule->lessons->children() as $node)
			{
				Units::validate($this, $node);
			}
			Units::setWarnings($this);
		}
		unset($this->groups, $this->events, $this->methods, $this->persons, $this->schedule, $this->term);
	}

	/**
	 * Validates a text attribute. Sets the attribute if valid.
	 *
	 * @param   string  $value     the attribute value
	 * @param   string  $constant  the unique text constant fragment
	 * @param   string  $regex     the regex to check the text against
	 *
	 * @return mixed string the text if valid, otherwise bool false
	 */
	public function validateText($value, $constant, $regex = '')
	{
		if (empty($value))
		{
			$this->errors[] = Languages::_("ORGANIZER_{$constant}_MISSING");

			return false;
		}

		if (!empty($regex) and preg_match($regex, $value))
		{
			$this->errors[] = Languages::_("ORGANIZER_{$constant}_INVALID");

			return false;
		}

		return true;
	}
}
