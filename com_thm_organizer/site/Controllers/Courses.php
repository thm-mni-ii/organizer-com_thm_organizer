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
use Organizer\Helpers\Courses as CoursesHelper;
use Organizer\Helpers\Input;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Routing;
use Organizer\Models\Course;

/**
 * Class receives user actions and performs access checks and redirection.
 */
class Courses extends Controller
{
	protected $listView = 'courses';

	protected $resource = 'course';

	/**
	 * Save user information from form and if course id defined sign in or out of course
	 * then redirect to course list view
	 *
	 * @return void
	 */
	public function changeParticipantState()
	{
		$courseID = Input::getID();
		$url      = Routing::getRedirectBase();

		if (empty($courseID))
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_INVALID_REQUEST', 'error');
			$this->setRedirect(Route::_($url, false));
		}

		$success = $this->getModel('course')->changeParticipantState();

		if (empty($success))
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
		}

		$url .= "&view=courses&id=$courseID";
		$this->setRedirect(Route::_($url, false));
	}

	/**
	 * Check if a course was selected and is valid. Check if the required participant data exists, if not redirect to
	 * the participant edit view. Otherwise register/deregister the user from the course.
	 *
	 * @return void
	 */
	public function register()
	{
		$courseID = $this->input->getInt('lessonID');
		$url      = Routing::getRedirectBase();

		// No chosen lesson => should not occur
		if (empty($courseID) or !CoursesHelper::isRegistrationOpen())
		{
			$this->setRedirect(Route::_($url, false));
		}

		$formItems          = Input::getFormItems();
		$participantModel   = $this->getModel('participant');
		$participantEditURL = "{$url}&view=participant_edit&lessonID=$courseID";

		// Called from participant profile form
		if (!empty($formItems->count()))
		{
			$participantSaved = $participantModel->save();

			if (empty($participantSaved))
			{
				OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
				$this->setRedirect(Route::_($participantEditURL, false));

				return;
			}
		}

		// Always based on the current user, no further validation required.
		$participant = parent::getModel('participant_edit')->getItem();

		// Ensure participant data is complete
		$invalidParticipant = (empty($participant->address)
			or empty($participant->zipCode)
			or empty($participant->city)
			or empty($participant->programID)
			or empty($participant->forename)
			or empty($participant->surname)
		);

		// Participant entry is incomplete
		if ($invalidParticipant)
		{
			$this->setRedirect(Route::_($participantEditURL, false));

			return;
		}

		$userState = CoursesHelper::getParticipantState();

		// 1 = Register | 2 = Deregister
		$action  = empty($userState) ? 1 : 2;
		$success = $participantModel->register($participant->id, $courseID, $action);

		if ($success)
		{
			if (!empty($userState))
			{
				OrganizerHelper::message('THM_ORGANIZER_DEREGISTRATION_SUCCESS');
			}
			else
			{
				$newState = CoursesHelper::getParticipantState();
				$msg      = $newState['status'] ?
					'THM_ORGANIZER_REGISTRATION_REGISTERED' : 'THM_ORGANIZER_REGISTRATION_WAIT';
				OrganizerHelper::message($msg);
			}
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_STATUS_FAILURE', 'error');
		}

		if ($this->resource == 'subject')
		{
			$subjectID = $this->input->getInt('id', 0);
			$url       .= "&view=subject_item&id=$subjectID";
		}
		else
		{
			$url .= '&view=course_list';
		}

		$this->setRedirect(Route::_($url, false));
	}

	/**
	 * Saves course information and redirects.
	 *
	 * @return void modifies saved course data
	 * @throws Exception => unauthorized access
	 */
	public function save()
	{
		$backend = $this->clientContext === self::BACKEND;
		$model   = new Course();
		$url     = Routing::getRedirectBase();

		if ($courseID = $model->save())
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_SUCCESS', 'success');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
		}

		if ($backend or empty($courseID))
		{
			$url .= "&view=courses";
		}
		else
		{
			$url .= "&view=courses&id=$courseID";
		}

		$this->setRedirect(Route::_($url, false));
	}
}
