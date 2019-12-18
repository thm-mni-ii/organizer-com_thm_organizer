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

use Organizer\Helpers as Helpers;
use Organizer\Tables as Tables;

/**
 * Class retrieves information for a filtered set of participants.
 */
class Badges extends BaseModel
{
	/**
	 * Retrieves a list of relevant participants.
	 *
	 * @param   int  $courseID  the id of the course
	 *
	 * @return array the participants
	 */
	public function getParticipants($courseID)
	{
		$allParticipants = Helpers\Courses::getParticipants($courseID);
		if ($participantID = Helpers\Input::getInt('participantID'))
		{
			$selected = [$participantID];
		}
		else
		{
			$selected = Helpers\Input::getSelectedIDs();
		}

		// Participants were requested who are not registered to the course.
		if (array_diff($selected, $allParticipants))
		{
			return [];
		}

		$participantTemplate = ['address', 'city', 'forename', 'id', 'surname', 'zipCode'];
		$selected            = $selected ? $selected : $allParticipants;
		$participants        = [];
		foreach ($selected as $participantID)
		{
			$table = new Tables\Participants;
			if (!$table->load($participantID))
			{
				continue;
			}

			$participant = [];
			foreach ($participantTemplate as $property)
			{
				if (empty($table->$property))
				{
					unset($participants[$participantID]);
					continue 2;
				}

				$participant[$property] = $table->$property;
			}

			$participants[] = $participant;
		}

		return $participants;
	}
}
