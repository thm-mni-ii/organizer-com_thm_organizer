<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/courses.php';

/**
 * Class receives user actions and performs access checks and redirection.
 */
class THM_OrganizerController extends JControllerLegacy
{
    /**
     * Save user information from form and if course id defined sign in or out of course
     * then redirect to course list view
     *
     * @return void
     */
    public function changeParticipantState()
    {
        $formData = $this->input->get('jform', [], 'array');
        $url      = THM_OrganizerHelperComponent::getRedirectBase();

        if (empty($formData) or empty($formData['id'])) {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_INVALID_REQUEST', 'error');
            $this->setRedirect(JRoute::_($url, false));
        }

        $success = $this->getModel('course')->changeParticipantState();

        if (empty($success)) {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
        } else {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
        }

        $url .= "&view=course_manager&lessonID={$formData['id']}";
        $this->setRedirect(JRoute::_($url, false));
    }

    /**
     * Sends an circular email to all course participants
     *
     * @return void
     */
    public function circular()
    {
        if (empty($this->getModel('course')->circular())) {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_MAIL_SEND_FAIL', 'error');
        } else {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_MAIL_SEND_SUCCESS', 'error');
        }

        $lessonID = $this->input->get('lessonID');
        $redirect = THM_OrganizerHelperComponent::getRedirectBase() . "view=course_manager&lessonID=$lessonID";
        $this->setRedirect(JRoute::_($redirect, false));
    }

    /**
     * Check if a course was selected and is valid. Check if the required participant data exists, if not redirect to the
     * participant edit view. Otherwise register/deregister the user from the course.
     *
     * @return void
     */
    public function register()
    {
        $courseID = $this->input->getInt('lessonID');
        $url      = THM_OrganizerHelperComponent::getRedirectBase();

        // No chosen lesson => should not occur
        if (empty($courseID) or !THM_OrganizerHelperCourses::isRegistrationOpen()) {
            $this->setRedirect(JRoute::_($url, false));
        }

        $formData           = $this->input->get('jform', [], 'array');
        $participantModel   = $this->getModel('participant');
        $participantEditURL = "{$url}&view=participant_edit&lessonID=$courseID";

        // Called from participant profile form
        if (!empty($formData)) {
            $participantSaved = $participantModel->save();

            if (empty($participantSaved)) {
                THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
                $this->setRedirect(JRoute::_($participantEditURL, false));

                return;
            }
        }

        // Always based on the current user, no further validation required.
        $participant = parent::getModel('participant_edit')->getItem();

        // Ensure participant data is complete
        $invalidParticipant = (empty($participant->address)
            or empty($participant->zip_code)
            or empty($participant->city)
            or empty($participant->programID)
            or empty($participant->forename)
            or empty($participant->surname)
        );

        // Participant entry is incomplete
        if ($invalidParticipant) {
            $this->setRedirect(JRoute::_($participantEditURL, false));

            return;
        }

        $userState = THM_OrganizerHelperCourses::getParticipantState();

        // 1 = Register | 2 = Deregister
        $action  = empty($userState) ? 1 : 2;
        $success = $participantModel->register($participant->id, $courseID, $action);

        if ($success) {

            if (!empty($userState)) {
                THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_DEREGISTRATION_SUCCESS');
            } else {
                $newState   = THM_OrganizerHelperCourses::getParticipantState();
                $msg = $newState['status'] ?
                    'COM_THM_ORGANIZER_REGISTRATION_SUCCESS_REGISTERED' : 'COM_THM_ORGANIZER_REGISTRATION_SUCCESS_WAIT';
                THM_OrganizerHelperComponent::message($msg);
            }
        } else {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_STATUS_FAILURE', 'error');
        }

        $view = explode('.', $this->input->get('task', ''))[0];

        if ($view == 'subject') {
            $subjectID = $this->input->getInt('id', 0);
            $url       .= "&view=subject_details&id=$subjectID";
        } else {
            $url .= '&view=course_list';
        }

        $this->setRedirect(JRoute::_($url, false));
    }

    /**
     * Save user information from form and if course id defined sign in or out of course
     * then redirect to course list view
     *
     * @return void
     */
    public function save()
    {
        $formData  = $this->input->get('jform', [], 'array');
        $modelName = explode('.', $this->input->get('task', ''))[0];
        $model     = $this->getModel($modelName);

        // Request manipulation
        if (empty($model) or empty($formData) or empty($formData['id'])) {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_INVALID_REQUEST', 'error');
            $this->setRedirect(JRoute::_(JUri::base(), false));
        }

        $authorized = false;
        $url        = THM_OrganizerHelperComponent::getRedirectBase();

        if ($modelName == 'subject') {
            $authorized = THM_OrganizerHelperSubjects::allowEdit($formData['id']);
        } elseif ($modelName == 'course') {
            $authorized = THM_OrganizerHelperCourses::authorized($formData['id']);
        } elseif ($modelName == 'participant') {
            $authorized = JFactory::getUser()->id == $formData['id'];
        }

        if (empty($authorized)) {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_NO_ACCESS_ACTION', 'error');
            $this->setRedirect(JRoute::_(JUri::base(), false));
        }

        $success = $model->save();

        if (empty($success)) {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');

            if ($modelName == 'subject') {
                $url .= "&view=subject_edit&id={$formData['id']}";
            }
            if ($modelName == 'course') {
                $url .= '&view=course_manager';
            } elseif ($modelName == 'participant') {
                $url .= '&view=participant_edit';
            }
        } else {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS', 'success');

            if ($modelName == 'course') {
                $url .= '&view=course_manager';
            } elseif ($modelName == 'participant') {
                $url .= '&view=course_list';
            }
        }

        if ($modelName == 'course' or $modelName == 'subject') {
            $lessonID = $modelName == 'course' ? $formData['id'] : $this->input->getInt('lessonID');
            $url      .= "&lessonID=$lessonID";
        }

        $this->setRedirect(JRoute::_($url, false));
    }
}
