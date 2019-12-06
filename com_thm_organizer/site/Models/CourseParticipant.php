<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Joomla\CMS\Table\Table;
use Organizer\Helpers\Can;
use Organizer\Helpers\Courses;
use Organizer\Helpers\Input;
use Organizer\Tables\CourseParticipants as CourseParticipantsTable;

/**
 * Class which manages stored course data.
 */
class CourseParticipant extends BaseModel
{
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
	 * Sends a circular mail to selected course participants defaults to all.
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

		// Get the ids of the intended recipients. Filter for status?
		//$status = include wait list? yes, no, no input
		//$selectedParticipants = Input::getSelectedIDs();
		// or
		//$recipients = Courses::getFullParticipantData($courseID, (bool) $data['includeWaitList']);

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
		return new CourseParticipantsTable;
	}

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


	}
}
