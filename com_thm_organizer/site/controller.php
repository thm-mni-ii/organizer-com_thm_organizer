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

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/component.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/courses.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

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
     * @throws Exception
     */
    public function changeParticipantState()
    {
        $lang     = THM_OrganizerHelperLanguage::getLanguage();
        $app      = JFactory::getApplication();
        $formData = $app->input->get('jform', [], 'array');
        $url      = THM_OrganizerHelperComponent::getRedirectBase();

        if (empty($formData) or empty($formData['id'])) {
            $app->enqueueMessage($lang->_("COM_THM_ORGANIZER_MESSAGE_INVALID_REQUEST"), "error");
            $app->redirect(JRoute::_($url, false));
        }

        $success = $this->getModel('course')->changeParticipantState();

        if (empty($success)) {
            $msgText = $lang->_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $msgType = 'error';
        } else {
            $msgText = $lang->_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $msgType = 'success';
        }

        $app->enqueueMessage($msgText, $msgType);

        $url .= "&view=course_manager&lessonID={$formData['id']}";
        $app->redirect(JRoute::_($url, false));
    }

    /**
     * Sends an circular email to all course participants
     *
     * @return void
     * @throws Exception
     */
    public function circular()
    {
        $lang = THM_OrganizerHelperLanguage::getLanguage();
        $app  = JFactory::getApplication();

        $success = $this->getModel('course')->circular();

        if (empty($success)) {
            $msgText = $lang->_('COM_THM_ORGANIZER_MESSAGE_MAIL_SEND_FAIL');
            $msgType = 'error';
        } else {
            $msgText = $lang->_('COM_THM_ORGANIZER_MESSAGE_MAIL_SEND_SUCCESS');
            $msgType = 'success';
        }

        $app->enqueueMessage($msgText, $msgType);
        $lessonID = $app->input->get("lessonID");
        $redirect = THM_OrganizerHelperComponent::getRedirectBase() . "view=course_manager&lessonID=$lessonID";
        $app->redirect(JRoute::_($redirect, false));
    }

    /**
     * Check if a course was selected and is valid. Check if the required participant data exists, if not redirect to the
     * participant edit view. Otherwise register/deregister the user from the course.
     *
     * @return void
     * @throws Exception
     */
    public function register()
    {
        $app      = JFactory::getApplication();
        $input    = $app->input;
        $courseID = $input->getInt('lessonID');
        $url      = THM_OrganizerHelperComponent::getRedirectBase();

        // No chosen lesson => should not occur
        if (empty($courseID) or !THM_OrganizerHelperCourses::isRegistrationOpen()) {
            $app->redirect(JRoute::_($url, false));
        }

        $lang               = THM_OrganizerHelperLanguage::getLanguage();
        $formData           = $input->get('jform', [], 'array');
        $participantModel   = $this->getModel('participant');
        $participantEditURL = "{$url}&view=participant_edit&lessonID=$courseID";

        // Called from participant profile form
        if (!empty($formData)) {
            $participantSaved = $participantModel->save();

            if (empty($participantSaved)) {
                $app->enqueueMessage($lang->_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL'), 'error');
                $app->redirect(JRoute::_($participantEditURL, false));

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
            $app->redirect(JRoute::_($participantEditURL, false));

            return;
        }

        $type        = 'error';
        $userState   = THM_OrganizerHelperCourses::getParticipantState();

        // 1 = Register | 2 = Deregister
        $action = empty($userState) ? 1 : 2;
        $success = $participantModel->register($participant->id, $courseID, $action);

        if ($success) {
            $type = 'success';

            if (!empty($userState)) {
                $msg = $lang->_("COM_THM_ORGANIZER_DEREGISTRATION_SUCCESS");
            } else {
                $successMessage = $lang->_('COM_THM_ORGANIZER_REGISTRATION_SUCCESS');
                $newUserState = THM_OrganizerHelperCourses::getParticipantState();
                $status = $newUserState["status"] ? "COM_THM_ORGANIZER_COURSE_REGISTERED" : "COM_THM_ORGANIZER_WAIT_LIST";
                $statusMessage  = $lang->_($status);
                $msg            = sprintf($successMessage, $statusMessage);
            }
        } else {
            $msg = $lang->_("COM_THM_ORGANIZER_STATUS_FAILURE");
        }

        $view = explode('.', $input->get('task', ''))[0];

        if ($view == 'subject') {
            $subjectID = $input->getInt('id', 0);
            $url       .= "&view=subject_details&id=$subjectID";
        } elseif (empty($menuID)) {
            $url .= '&view=course_list';
        }

        $app->enqueueMessage($msg, $type);
        $app->redirect(JRoute::_($url, false));
    }

    /**
     * Save user information from form and if course id defined sign in or out of course
     * then redirect to course list view
     *
     * @return void
     * @throws Exception
     */
    public function save()
    {
        $app       = JFactory::getApplication();
        $input     = $app->input;
        $formData  = $input->get('jform', [], 'array');
        $lang      = THM_OrganizerHelperLanguage::getLanguage();
        $modelName = explode('.', $input->get('task', ''))[0];
        $model     = $this->getModel($modelName);

        // Request manipulation
        if (empty($model) or empty($formData) or empty($formData['id'])) {
            $app->enqueueMessage($lang->_("COM_THM_ORGANIZER_MESSAGE_INVALID_REQUEST"), "error");
            $app->redirect(JRoute::_(JUri::base(), false));
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
            $app->enqueueMessage($lang->_("COM_THM_ORGANIZER_MESSAGE_NO_ACCESS_ACTION"), "error");
            $app->redirect(JRoute::_(JUri::base(), false));
        }

        $success = $model->save();

        if (empty($success)) {
            $app->enqueueMessage($lang->_("COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL"), 'error');

            if ($modelName == 'subject') {
                $url .= "&view=subject_edit&id={$formData['id']}";
            }
            if ($modelName == 'course') {
                $url .= "&view=course_manager";
            } elseif ($modelName == 'participant') {
                $url .= '&view=participant_edit';
            }
        } else {
            $app->enqueueMessage($lang->_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS'), 'success');

            if ($modelName == 'course') {
                $url .= '&view=course_manager';
            } elseif ($modelName == 'participant') {
                $url .= '&view=course_list';
            }
        }

        if ($modelName == 'course' or $modelName == 'subject') {
            $lessonID = $modelName == 'course' ? $formData['id'] : $input->getInt('lessonID');
            $url      .= "&lessonID=$lessonID";
        }

        $app->redirect(JRoute::_($url, false));
    }
}
