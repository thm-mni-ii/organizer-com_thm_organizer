<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Controllers;

use Exception;
use Joomla\CMS\Router\Route;
use Organizer\Controller;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Routing;
use Organizer\Models\Participant;

/**
 * Class receives user actions and performs access checks and redirection.
 */
class Participants extends Controller
{
	protected $listView = 'participants';

	protected $resource = 'participant';

	/**
	 * Sends an circular email to all course participants
	 *
	 * @return void
	 * @throws Exception
	 */
	public function circular()
	{
		if (empty($this->getModel('course')->circular()))
		{
			OrganizerHelper::message('THM_ORGANIZER_MAIL_SEND_FAIL', 'error');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MAIL_SEND_SUCCESS', 'error');
		}

		$lessonID = $this->input->get('lessonID');
		$redirect = Routing::getRedirectBase() . "view=courses&lessonID=$lessonID";
		$this->setRedirect(Route::_($redirect, false));
	}

	/**
	 * Save user information from form and if course id defined sign in or out of course
	 * then redirect to course list view
	 *
	 * @return void
	 */
	public function save()
	{
		$backend = $this->clientContext === self::BACKEND;
		$model   = new Participant();
		$url     = Routing::getRedirectBase();

		if ($participantID = $model->save())
		{
			OrganizerHelper::message('THM_ORGANIZER_SAVE_SUCCESS', 'success');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_SAVE_FAIL', 'error');
		}

		if ($backend)
		{
			$url .= "&view=participants";
		}
		else
		{
			$url .= "&view=courses";
		}

		$this->setRedirect(Route::_($url, false));
	}
}
