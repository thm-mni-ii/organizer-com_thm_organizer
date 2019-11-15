<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Organizer\Helpers\Access;
use Organizer\Helpers\Courses;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Participants;
use Organizer\Tables\Participants as ParticipantsTable;

/**
 * Class which manages stored participant data.
 */
class Participant extends BaseModel
{
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
		return new ParticipantsTable;
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
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		$canAccept = (int) Courses::canAcceptParticipant($courseID);
		$state     = $state == 1 ? $canAccept : 2;

		return Participants::changeState($participantID, $courseID, $state);
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
		$data = empty($data) ? Input::getFormItems()->toArray() : $data;

		if (!isset($data['id']))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
		}

		$lessonID = empty($data['lessonID']) ? null : $data['lessonID'];
		if (!Access::allowParticipantAccess($data['id'], $lessonID))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		$numericFields  = ['id', 'programID', 'zipCode'];
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

		$forenames = explode(' ', $data['forename']);
		array_filter($forenames);
		array_walk($forenames, array($this, 'normalize'));
		$data['forename'] = implode(' ', $forenames);

		$surname  = str_replace('-', ' ', $data['surname']);
		$surnames = explode(' ', $surname);
		$surnames = array_filter($surnames);
		array_walk($surnames, array($this, 'normalize'));
		$data['surname'] = implode('-', $surnames);

		$table = $this->getTable();

		if (empty($table))
		{
			return false;
		}

		$table->load($data['id']);
		$success = $table->save($data);

		return $success ? $table->id : false;
	}
}
