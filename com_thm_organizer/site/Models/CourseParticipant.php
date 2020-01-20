<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Joomla\CMS\Factory;
use Organizer\Helpers\Can;
use Organizer\Helpers\Courses;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Tables\CourseParticipants as CourseParticipantsTable;

/**
 * Class which manages stored course data.
 */
class CourseParticipant extends BaseModel
{
	const ACCEPTED = 1, ATTENDED = 1, PAID = 1;

	/**
	 * Sets the status for the course participant to accepted
	 *
	 * @return bool true on success, otherwise false
	 * @throws Exception invalid / unauthorized access
	 */
	public function accept()
	{
		return $this->batch('status', self::ACCEPTED);
	}

	/**
	 * Sets the property the given property to the given value for the selected participants.
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
			throw new Exception(Languages::_('ORGANIZER_400'), 400);
		}

		if (!Can::manage('course', $courseID))
		{
			throw new Exception(Languages::_('ORGANIZER_403'), 403);
		}

		foreach ($participantIDs as $participantID)
		{
			if (!Can::manage('participant', $participantID))
			{
				throw new Exception(Languages::_('ORGANIZER_403'), 403);
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
			throw new Exception(Languages::_('ORGANIZER_400'), 400);
		}
		elseif (!Can::manage('course', $courseID))
		{
			throw new Exception(Languages::_('ORGANIZER_403'), 403);
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
			throw new Exception(Languages::_('ORGANIZER_403'), 403);
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
	 * Sets the status for the course participant to attended
	 *
	 * @return bool true on success, otherwise false
	 * @throws Exception invalid / unauthorized access
	 */
	public function confirmAttendance()
	{
		return $this->batch('attended', self::ATTENDED);
	}

	/**
	 * Sets the payment status to paid.
	 *
	 * @return bool true on success, otherwise false
	 * @throws Exception invalid / unauthorized access
	 */
	public function confirmPayment()
	{
		return $this->batch('paid', self::PAID);
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
	/*
		private function mailRemoval($courseID, $participantID)
		{
			return;
			$mailer = Factory::getMailer();

			$user       = Factory::getUser($participantID);
			$userParams = json_decode($user->params, true);
			$mailer->addRecipient($user->email);

			if (!empty($userParams['language']))
			{
				Input::getInput()->set('languageTag', explode('-', $userParams['language'])[0]);
			}

			$params = Input::getParams();
			$sender = Factory::getUser($params->get('mailSender'));

			if (empty($sender->id))
			{
				return;
			}

			$mailer->setSender([$sender->email, $sender->name]);

			$course   = Courses::getCourse($courseID);
			$dateText = Courses::getDateDisplay($courseID);

			if (empty($course) or empty($dateText))
			{
				return;
			}

			$campus     = Courses::getCampus($courseID);
			$courseName = (empty($campus) or empty($campus['name'])) ?
				$course['name'] : "{$course['name']} ({$campus['name']})";
			$mailer->setSubject($courseName);
			$body = Languages::_('ORGANIZER_GREETING') . ',\n\n';

			$dates = explode(' - ', $dateText);

			if (count($dates) == 1 or $dates[0] == $dates[1])
			{
				$body .= sprintf(Languages::_('ORGANIZER_CIRCULAR_BODY_ONE_DATE') . ':\n\n', $courseName, $dates[0]);
			}
			else
			{
				$body .= sprintf(
					Languages::_('ORGANIZER_CIRCULAR_BODY_TWO_DATES') . ':\n\n',
					$courseName,
					$dates[0],
					$dates[1]
				);
			}

			$statusText = '';

			switch ($state)
			{
				case 0:
					$statusText .= Languages::_('ORGANIZER_COURSE_MAIL_STATUS_WAIT_LIST');
					break;
				case 1:
					$statusText .= Languages::_('ORGANIZER_COURSE_MAIL_STATUS_REGISTERED');
					break;
				case 2:
					$statusText .= Languages::_('ORGANIZER_COURSE_MAIL_STATUS_REMOVED');
					break;
				default:
					return;
			}

			$body .= ' => ' . $statusText . '\n\n';

			$body .= Languages::_('ORGANIZER_CLOSING') . ',\n';
			$body .= $sender->name . '\n\n';
			$body .= $sender->email . '\n';

			$addressParts = explode(' – ', $params->get('address'));

			foreach ($addressParts as $aPart)
			{
				$body .= $aPart . '\n';
			}

			$contactParts = explode(' – ', $params->get('contact'));

			foreach ($contactParts as $cPart)
			{
				$body .= $cPart . '\n';
			}

			$mailer->setBody($body);
			$mailer->Send();
		}
	*/

	/**
	 * Sets the payment status to paid.
	 *
	 * @return bool true on success, otherwise false
	 * @throws Exception invalid / unauthorized access
	 */
	public function remove()
	{
		if (!$courseID = Input::getInt('courseID') or !$participantIDs = Input::getSelectedIDs())
		{
			throw new Exception(Languages::_('ORGANIZER_400'), 400);
		}

		if (!Can::manage('course', $courseID))
		{
			throw new Exception(Languages::_('ORGANIZER_403'), 403);
		}

		$query = $this->_db->getQuery('true');
		$query->select("DISTINCT i.id")
			->from('#__thm_organizer_instances AS i')
			->innerJoin('#__thm_organizer_units AS u on u.id = i.unitID')
			->where("u.courseID = $courseID")
			->order('i.id');
		$this->_db->setQuery($query);
		$instances = implode(',', OrganizerHelper::executeQuery('loadColumn', []));

		foreach ($participantIDs as $participantID)
		{
			if (!Can::manage('participant', $participantID))
			{
				throw new Exception(Languages::_('ORGANIZER_403'), 403);
			}

			if (!$this->removeAssociations($courseID, $instances, $participantID))
			{
				// Break for error handling
				return false;
			}

			// Send mail
			// Aggregate mail for confirmation
		}

		// Send a confirmation e-mail to the sender.

		return true;
	}

	/**
	 * Removes the participants associations relevant to the course.
	 *
	 * @param   int     $courseID       the course id
	 * @param   string  $instanceIDs    the instance ids concatenated for use in a where clause
	 * @param   int     $participantID  the id of the participant
	 *
	 * @return bool
	 */
	private function removeAssociations($courseID, $instanceIDs, $participantID)
	{
		$table = $this->getTable();

		if (!$table->load(['courseID' => $courseID, 'participantID' => $participantID]))
		{
			return false;
		}

		if (!$table->delete())
		{
			return false;
		}

		$query = $this->_db->getQuery('true');
		$query->delete('#__thm_organizer_instance_participants')
			->where("instanceID IN ($instanceIDs)")
			->where("participantID = $participantID");
		$this->_db->setQuery($query);

		return OrganizerHelper::executeQuery('execute') ? true : false;

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
			throw new Exception(Languages::_('ORGANIZER_400'), 400);
		}

		if (!Can::manage('course', $courseID) or !Can::manage('participant', $participantID))
		{
			throw new Exception(Languages::_('ORGANIZER_403'), 403);
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
}