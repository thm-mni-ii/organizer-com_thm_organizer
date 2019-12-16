<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Organizer\Helpers\Can;
use Organizer\Helpers\Courses;
use Organizer\Helpers\Input;
use Organizer\Tables\CourseParticipants as CourseParticipantsTable;

/**
 * Class which manages stored course data.
 */
class CourseParticipant extends BaseModel
{
	const PENDING = 0, REGISTERED = 1;

	/**
	 * Sets the status for the course participant to registered
	 *
	 * @return bool true on success, otherwise false
	 * @throws Exception invalid / unauthorized access
	 */
	public function accept()
	{
		return $this->batch('status', self::REGISTERED);
	}

	/**
	 * Sets the status for the course participant to registered
	 *
	 * @param   string  $property  the property to update
	 * @param   int     $value     the new value for the property
	 *
	 * @return bool true on success, otherwise false
	 * @throws Exception invalid / unauthorized access
	 */
	private function batch($property, $value)
	{
		if (!$courseID = Input::getInt('courseID') or !$participantIDs = Input::getSelectedIDs())
		{
			throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
		}

		if (!Can::manage('course', $courseID))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		foreach ($participantIDs as $participantID)
		{
			if (!Can::manage('participant', $participantID))
			{
				throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
			}

			$table = $this->getTable();

			if (!$table->load(['courseID' => $courseID, 'participantID' => $participantID]))
			{
				return false;
			}

			$table->$property = $value;

			if (!$table->store())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Sends a circular mail to all course participants.
	 *
	 * @return bool true on success, false on error
	 * @throws Exception => invalid / unauthorized access
	 */
	public function circular()
	{
		if (!$courseID = Input::getInt('courseID'))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
		}
		elseif (!Can::manage('course', $courseID))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		return true;

		// Get data from input.
		//$data = Input::getFormItems()->toArray();

		// Validate data
		//if (empty($data['text']))
		//{
		//	return false;
		//}

		// Get the sender
		//$sender = Factory::getUser(Input::getParams()->get('mailSender'));
		//if (empty($sender->id))
		//{
		//	return false;
		//}

		// Get the ids of the intended recipients
		//$status = include wait list? yes, no, no input
		//$recipients = Courses::getParticipants($courseID, $status);

		//if (empty($recipients))
		//{
		//	return false;
		//}

		// Send the circular
		//$sent = true;
		//foreach ($recipients as $recipient)
		//{
		//	$mailer = JFactory::getMailer();
		//	$mailer->setSender([$sender->email, $sender->name]);
		//	$mailer->setSubject($data['subject']);
		//	$mailer->setBody($data['text']);
		//	$mailer->addRecipient($recipient['email']);
		//	$sent = ($sent and $mailer->Send());
		//}

		// Send a receipt to responsible persons.

		//return $sent;
	}

	/**
	 * Saves data for participants when administrator changes state in manager
	 *
	 * @return bool true on success, false on error
	 * @throws Exception => unauthorized access
	 */
	public function changeParticipantState()
	{
		$data     = Input::getInput()->getArray();
		$courseID = Input::getID();

		if (!Can::manage('course', $courseID))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		$participantIDs = $data['checked'];
		$state          = (int) $data['participantState'];
		$invalidState   = ($state < 0 or $state > 2);

		if (empty($participantIDs) or empty($courseID) or $invalidState)
		{
			return false;
		}

		foreach ($data['checked'] as $participantID)
		{
			if (!Participants::changeState($participantID, $courseID, $state))
			{
				return false;
			}

			if ($state === 0)
			{
				Courses::refreshWaitList($courseID);
			}
		}

		return true;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return CourseParticipantsTable A Table object
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function getTable($name = '', $prefix = '', $options = [])
	{
		return new CourseParticipantsTable;
	}

	/**
	 * Toggles binary attributes of the course participant association.
	 *
	 * @return bool true on success, otherwise false
	 * @throws Exception invalid / unauthorized access
	 */
	public function toggle()
	{
		$attribute     = Input::getCMD('attribute', '');
		$courseID      = Input::getInt('courseID', 0);
		$participantID = Input::getInt('participantID', 0);
		if (!$attribute or !$courseID or !$participantID)
		{
			throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
		}

		if (!Can::manage('course', $courseID) or !Can::manage('participant', $participantID))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		$table = $this->getTable();
		if (!property_exists($table, $attribute))
		{
			return false;
		}

		if (!$table->load(['courseID' => $courseID, 'participantID' => $participantID]))
		{
			return false;
		}

		$table->$attribute = !$table->$attribute;

		return $table->store();
	}

	/**
	 * Sets the status for the course participant to registered
	 *
	 * @return bool true on success, otherwise false
	 * @throws Exception invalid / unauthorized access
	 */
	public function wait()
	{
		return $this->batch('status', self::PENDING);
	}
}