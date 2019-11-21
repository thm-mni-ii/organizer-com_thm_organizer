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
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Organizer\Helpers\Can;
use Organizer\Helpers\Courses;
use Organizer\Helpers\Input;
use Organizer\Tables\Courses as CoursesTable;

/**
 * Class which manages stored course data.
 */
class Course extends BaseModel
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
	 * Sends a circular mail to all course participants
	 *
	 * @return bool true on success, false on error
	 * @throws Exception => not found / unauthorized access
	 */
	public function circular()
	{
		$courseID = Input::getID();

		if (empty($courseID))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_404'), 404);
		}

		if (empty(Can::manage('course', $courseID)))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		$data = Input::getFormItems()->toArray();

		if (empty($data['text']))
		{
			return false;
		}

		$sender = Factory::getUser(Input::getParams()->get('mailSender'));

		if (empty($sender->id))
		{
			return false;
		}

		$recipients = Courses::getFullParticipantData($courseID, (bool) $data['includeWaitList']);

		if (empty($recipients))
		{
			return false;
		}

		$sent = true;
		foreach ($recipients as $recipient)
		{
			$mailer = JFactory::getMailer();
			$mailer->setSender([$sender->email, $sender->name]);
			$mailer->setSubject($data['subject']);
			$mailer->setBody($data['text']);
			$mailer->addRecipient($recipient['email']);
			$sent = ($sent and $mailer->Send());
		}

		return $sent;
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
		return new CoursesTable;
	}
}
