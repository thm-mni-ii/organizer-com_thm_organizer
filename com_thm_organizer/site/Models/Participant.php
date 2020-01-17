<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Organizer\Helpers;
use Organizer\Tables;

/**
 * Class which manages stored participant data.
 */
class Participant extends MergeModel
{
	protected $fkColumn = 'participantID';

	/**
	 * Filters names (city, forename, surname) for actual letters and accepted special characters.
	 *
	 * @param   string  $name  the raw value
	 *
	 * @return string the cleaned value
	 */
	private function cleanAlpha($name)
	{
		$name = preg_replace('/[^A-ZÀ-ÖØ-Þa-zß-ÿ\p{N}_.\-\']/', ' ', $name);

		return self::cleanSpaces($name);
	}

	/**
	 * Filters names (city, forename, surname) for actual letters and accepted special characters.
	 *
	 * @param   string  $name  the raw value
	 *
	 * @return string the cleaned value
	 */
	private function cleanAlphaNum($name)
	{
		$name = preg_replace('/[^A-ZÀ-ÖØ-Þa-zß-ÿ\d\p{N}_.\-\']/', ' ', $name);

		return self::cleanSpaces($name);
	}

	/**
	 * Filters out extra spaces.
	 *
	 * @param   string  $string  the raw value
	 *
	 * @return string the cleaned value
	 */
	private function cleanSpaces($string)
	{
		return preg_replace('/ +/', ' ', $string);
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return Table A Table object
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function getTable($name = '', $prefix = '', $options = [])
	{
		return new Tables\Participants;
	}

	/**
	 * Normalized strings used for participant name pieces.
	 *
	 * @param   string  $item  the attribute item being normalized.
	 *
	 * @return void modifies the string
	 */
	private function normalize(&$item)
	{
		if (strpos($item, '-') !== false)
		{
			$compoundParts = explode('-', $item);
			array_walk($compoundParts, 'normalize');
			$item = implode('-', $compoundParts);

			return;
		}

		$item = ucfirst(strtolower($item));
	}

	/**
	 * (De-) Registers course participants
	 *
	 * @param   int     $participantID  the participantID
	 * @param   int     $courseID       id of lesson
	 * @param   string  $state          the state requested by the user
	 *
	 * @return boolean true on success, false on error
	 * @throws Exception => unauthorized access
	 */
	public function register($participantID, $courseID, $state)
	{
		if (!Factory::getUser()->id === $participantID)
		{
			throw new Exception(Helpers\Languages::_('THM_ORGANIZER_403'), 403);
		}

		$canAccept = (int) Helpers\Courses::canAcceptParticipant($courseID);
		$state     = $state == 1 ? $canAccept : 2;

		return Helpers\Participants::changeState($participantID, $courseID, $state);
	}

	/**
	 * Attempts to save the resource.
	 *
	 * @param   array  $data  form data which has been preprocessed by inheriting classes.
	 *
	 * @return mixed int id of the resource on success, otherwise boolean false
	 * @throws Exception => unauthorized access
	 */
	public function save($data = [])
	{
		$data = empty($data) ? Helpers\Input::getFormItems()->toArray() : $data;

		if (!isset($data['id']))
		{
			throw new Exception(Helpers\Languages::_('THM_ORGANIZER_400'), 400);
		}

		if (!Helpers\Can::edit('participant', $data['id']))
		{
			throw new Exception(Helpers\Languages::_('THM_ORGANIZER_403'), 403);
		}

		$numericFields  = ['id', 'programID'];
		$requiredFields = ['address', 'city', 'forename', 'id', 'programID', 'surname', 'zipCode'];

		foreach ($data as $index => $value)
		{
			if (in_array($index, $requiredFields))
			{
				$data[$index] = trim($value);
				if (empty($data[$index]))
				{
					return false;
				}
				if (in_array($index, $numericFields) and !is_numeric($value))
				{
					return false;
				}
			}
		}

		$data['address']  = self::cleanAlphaNum($data['address']);
		$data['city']     = self::cleanAlpha($data['city']);
		$data['forename'] = self::cleanAlpha($data['forename']);
		$data['surname']  = self::cleanAlpha($data['surname']);
		$data['zipCode']  = self::cleanAlphaNum($data['zipCode']);

		$success = true;
		$table   = new Tables\Participants;
		if ($table->load($data['id']))
		{
			$altered = false;

			foreach ($data as $property => $value)
			{
				if (property_exists($table, $property))
				{
					$table->set($property, $value);
					$altered = true;
				}
			}

			if ($altered)
			{
				$success = $table->store();
			}
		}
		// Manual insertion because the table's primary key is also a foreign key.
		else
		{
			$relevantData = (object) $data;

			foreach ($relevantData as $property => $value)
			{
				if (!property_exists($table, $property))
				{
					unset($relevantData->$property);
				}
			}

			$success = Helpers\OrganizerHelper::insertObject('#__thm_organizer_participants', $relevantData, 'id');

		}

		return $success ? $data['id'] : false;
	}

	/**
	 * Updates the resource dependent associations
	 *
	 * @return boolean  true on success, otherwise false
	 */
	protected function updateAssociations()
	{
		if (!$this->updateCourseParticipants())
		{
			return false;
		}

		if (!$this->updateInstanceParticipants())
		{
			return false;
		}

		return $this->updateUsers();
	}

	/**
	 * Updates the course participants table to reflect the merge of the participants.
	 *
	 * @return bool true on success, otherwise false;
	 */
	private function updateCourseParticipants()
	{
		if (!$relevantCourses = $this->getAssociatedResourceIDs('courseID', 'course_participants'))
		{
			return true;
		}

		$mergeID = reset($this->selected);

		foreach ($relevantCourses as $courseID)
		{
			$attended        = false;
			$paid            = false;
			$participantDate = '';
			$status          = null;
			$statusDate      = '';

			$existing = new Tables\CourseParticipants;
			$exists   = $existing->load(['courseID' => $courseID, 'participantID' => $mergeID]);

			foreach ($this->selected as $participantID)
			{
				$cpTable        = new Tables\CourseParticipants;
				$loadConditions = ['courseID' => $courseID, 'participantID' => $participantID];
				if (!$cpTable->load($loadConditions))
				{
					continue;
				}

				$attended = ($attended or $cpTable->attended);
				$paid     = ($paid or $cpTable->paid);

				if ($cpTable->statusDate > $statusDate)
				{
					$participantDate = $cpTable->participantDate;
					$status          = $cpTable->status;
					$statusDate      = $cpTable->statusDate;
				}

				if ($exists)
				{
					if ($existing->id !== $cpTable->id)
					{
						$cpTable->delete();
					}

					continue;
				}

				$existing = $cpTable;
				$exists   = true;
			}

			$existing->attended        = $attended;
			$existing->paid            = $paid;
			$existing->participantID   = $mergeID;
			$existing->participantDate = $participantDate;
			$existing->status          = $status;
			$existing->statusDate      = $statusDate;
			if (!$existing->store())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Updates the instance participants table to reflect the merge of the participants.
	 *
	 * @return bool true on success, otherwise false;
	 */
	private function updateInstanceParticipants()
	{
		if (!$relevantInstances = $this->getAssociatedResourceIDs('instanceID', 'instance_participants'))
		{
			return true;
		}

		$mergeID = reset($this->selected);

		foreach ($relevantInstances as $instanceID)
		{
			$existing = new Tables\InstanceParticipants;
			$exists   = $existing->load(['instanceID' => $instanceID, 'participantID' => $mergeID]);

			foreach ($this->selected as $participantID)
			{
				$ipTable        = new Tables\InstanceParticipants;
				$loadConditions = ['instanceID' => $instanceID, 'participantID' => $participantID];
				if (!$ipTable->load($loadConditions))
				{
					continue;
				}

				if ($exists)
				{
					if ($existing->id !== $ipTable->id)
					{
						$ipTable->delete();
					}

					continue;
				}

				$existing = $ipTable;
				$exists   = true;
			}

			$existing->participantID = $mergeID;
			if (!$existing->store())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Updates the users table to reflect the merge of the participants.
	 *
	 * @return bool true on success, otherwise false;
	 */
	private function updateUsers()
	{
		$mergeID = reset($this->selected);
		$user    = Helpers\Users::getUser($mergeID);

		if (empty($user->id))
		{
			return false;
		}

		$email    = '';
		$name     = '';
		$pattern  = '/thm.de$/';
		$username = '';

		foreach ($this->selected as $participantID)
		{
			$thisUser = Helpers\Users::getUser($participantID);

			if (preg_match($pattern, $thisUser->email))
			{
				$email    = $thisUser->email;
				$name     = $thisUser->name;
				$username = $thisUser->username;
			}

			if ($thisUser->id !== $user->id)
			{
				$thisUser->delete();
			}
		}

		$user->email    = $email;
		$user->name     = $name;
		$user->username = $username;

		return $user->save();
	}
}
