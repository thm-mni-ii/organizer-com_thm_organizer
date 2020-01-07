<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Controllers;

use Exception;
use Organizer\Controller;
use Organizer\Helpers\Input;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Models\Participant;

/**
 * Class receives user actions and performs access checks and redirection.
 */
class Participants extends Controller
{
	const UNREGISTERED = null;

	use CourseParticipants;

	protected $listView = 'participants';

	protected $resource = 'participant';

	/**
	 * Save user information from form and if course id defined sign in or out of course
	 * then redirect to course list view
	 *
	 * @return void
	 * @throws Exception
	 */
	public function save()
	{
		$model = new Participant();

		if ($participantID = $model->save())
		{
			OrganizerHelper::message('THM_ORGANIZER_SAVE_SUCCESS', 'success');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_SAVE_FAIL', 'error');
		}

		$this->setRedirect(Input::getString('referrer'));
	}

	/**
	 * Redirects to the referring view.
	 *
	 * @return void
	 */
	public function cancel()
	{
		$this->setRedirect(Input::getString('referrer'));
	}
}
