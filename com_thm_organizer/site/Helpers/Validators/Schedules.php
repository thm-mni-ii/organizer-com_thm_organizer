<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
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
			$errorMessage = \JText::_('THM_ORGANIZER_ERROR_HEADER') . '<br />';
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

		$valid = true;

		// Creation Date & Time, school year dates, term attributes
		$this->creationDate = trim((string) $this->schedule[0]['date']);
		$this->creationTime = trim((string) $this->schedule[0]['time']);

		$validCreationDate = $this->validateDate($this->creationDate, 'CREATION_DATE');
		$validCreationTime = $this->validateText($this->creationTime, 'CREATION_TIME');
		$valid             = ($valid and $validCreationDate and $validCreationTime);

		$this->schoolYear            = new stdClass;
		$this->schoolYear->endDate   = trim((string) $this->schedule->general->schoolyearenddate);
		$this->schoolYear->startDate = trim((string) $this->schedule->general->schoolyearbegindate);

		$validSYED = $this->validateDate($this->schoolYear->endDate, 'SCHOOL_YEAR_END_DATE');
		$validSYSD = $this->validateDate($this->schoolYear->startDate, 'SCHOOL_YEAR_START_DATE');
		$valid     = ($valid and $validSYED and $validSYSD);

		$this->term            = new stdClass;
		$this->term->endDate   = trim((string) $this->schedule->general->termenddate);
		$this->term->name      = trim((string) $this->schedule->general->footer);
		$this->term->startDate = trim((string) $this->schedule->general->termbegindate);
		$valid                 = ($valid and $this->validateTerm());

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
	private function validateDate(&$value, $constant)
	{
		if (empty($value))
		{
			$this->errors[] = Languages::_("THM_ORGANIZER_{$constant}_MISSING");

			return false;
		}

		$value = date('Y-m-d', strtotime($value));

		return true;
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
	private function validateText($value, $constant, $regex = '')
	{
		if (empty($value))
		{
			$this->errors[] = Languages::_("THM_ORGANIZER_{$constant}_MISSING");

			return false;
		}

		if (!empty($regex) and preg_match($regex, $value))
		{
			$this->errors[] = Languages::_("THM_ORGANIZER_{$constant}_INVALID");

			return false;
		}

		return true;
	}

	/**
	 * Validates the dates given to ensure term consistency. Sets the term ID if valid.
	 *
	 * @return bool true if the term dates are existent and consistent, otherwise false
	 */
	private function validateTerm()
	{
		$validTED = $this->validateDate($this->term->endDate, 'TERM_END_DATE');
		$validTN  = $this->validateText($this->term->name, 'TERM_NAME', '/[\#\;]/');
		$validTSD = $this->validateDate($this->term->startDate, 'TERM_START_DATE');
		$valid    = ($validTED and $validTN and $validTSD);

		if (!$valid)
		{
			return false;
		}

		$endTimeStamp = strtotime($this->term->endDate);
		$invalidEnd   = $endTimeStamp > strtotime($this->schoolYear->endDate);

		$startTimeStamp = strtotime($this->term->startDate);
		$invalidStart   = $startTimeStamp < strtotime($this->schoolYear->startDate);

		$invalidPeriod = $startTimeStamp >= $endTimeStamp;
		$invalid       = ($invalidStart or $invalidEnd or $invalidPeriod);

		if ($invalid)
		{
			$this->errors[] = Languages::_('THM_ORGANIZER_TERM_INVALID');

			return false;
		}

		$termData = ['endDate' => date('Y-m-d', $endTimeStamp), 'startDate' => date('Y-m-d', $startTimeStamp)];
		$table    = OrganizerHelper::getTable('Terms');
		if ($table->load($termData))
		{
			$this->termID = $table->id;

			return true;
		}

		$shortYear        = date('y', $termData['endDate']);
		$termData['name'] = $this->term->name . $shortYear;

		if ($table->save($termData))
		{
			$this->termID = $table->id;

			return true;
		}

		return false;
	}
}
