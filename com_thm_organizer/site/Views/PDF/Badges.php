<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\PDF;

use Exception;
use Organizer\Helpers as Helpers;
use Organizer\Layouts\PDF\Badges as BadgesLayout;
use Organizer\Tables\Participants;
use Organizer\Views\BaseView;

/**
 * Class loads persistent information about a course into the display context.
 */
class Badges extends BaseView
{
	protected $_layout = 'Badges';

	/**
	 * Method to get display
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 * @throws Exception => invalid request / unauthorized access
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function display($tpl = null)
	{
		if (!$courseID = Helpers\Input::getInt('courseID'))
		{
			throw new Exception(Helpers\Languages::_('THM_ORGANIZER_400'), 400);
		}
		elseif (!Helpers\Can::manage('course', $courseID))
		{
			throw new Exception(Helpers\Languages::_('THM_ORGANIZER_401'), 401);
		}

		if (!$participants = $this->getParticipants($courseID))
		{
			throw new Exception(Helpers\Languages::_('THM_ORGANIZER_400'), 400);
		}

		$badges = new BadgesLayout($courseID);
		$badges->fill($participants);
		$badges->render();
	}

	/**
	 * Retrieves a list of relevant participants.
	 *
	 * @param   int  $courseID  the id of the course
	 *
	 * @return array the participants
	 */
	private function getParticipants($courseID)
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
			$table = new Participants;
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
