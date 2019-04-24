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

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/courses.php';

/**
 * Class receives user actions and performs access checks and redirection.
 */
class THM_OrganizerController extends \Joomla\CMS\MVC\Controller\BaseController
{
    private $resource = '';

    /**
     * Class constructor
     *
     * @param array $config An optional associative [] of configuration settings.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $task           = $this->input->get('task', '');
        $taskParts      = explode('.', $task);
        $this->resource = $taskParts[0];
    }

    /**
     * Performs access checks. Checks if the schedule is already active. If the
     * schedule is not already active, calls the activate function of the
     * schedule model.
     *
     * @return void
     */
    public function activate()
    {
        $model = $this->getModel($this->resource);

        $functionsAvailable = (method_exists($model, 'activate') and method_exists($model, 'checkIfActive'));
        if ($functionsAvailable) {
            $count = $this->input->getInt('boxchecked', 0);
            if ($count === 1) {
                $active = $model->checkIfActive();
                if ($active) {
                    THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_ERROR_ACTIVE_YES', 'warning');
                } else {
                    $success = $model->activate();
                    if ($success) {
                        THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_ACTIVATE_SUCCESS');
                    } else {
                        THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_ACTIVATE_FAIL', 'error');
                    }
                }
            } else {
                THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_ERROR_ONE_ALLOWED', 'error');
            }
        }

        $this->setRedirect("index.php?option=com_thm_organizer&view={$this->resource}_manager");
    }

    /**
     * Redirects to the edit view without an item id. Access checks performed in the view.
     *
     * @return void
     */
    public function add()
    {
        $this->input->set('view', "{$this->resource}_edit");
        parent::display();
    }

    /**
     * Makes call to the models's save function, and redirects to the same view.
     *
     * @return void
     */
    public function apply()
    {
        $resourceID = $this->getModel($this->resource)->save();

        if (!empty($resourceID)) {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
        } else {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
        }

        $url = THM_OrganizerHelperComponent::getRedirectBase();
        $url .= "&view={$this->resource}_edit&id=$resourceID";
        $this->setRedirect($url);
    }

    /**
     * Makes call to the models's batch function, and redirects to the manager view.
     *
     * @return void
     */
    public function batch()
    {
        $success = $this->getModel($this->resource)->batch();

        if ($success) {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
        } else {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
        }

        $url = THM_OrganizerHelperComponent::getRedirectBase();
        $url .= "&view={$this->resource}_manager";
        $this->setRedirect($url);
    }

    /**
     * Redirects to the manager from the form.
     *
     * @return void
     */
    public function cancel()
    {
        $this->input->set('view', "{$this->resource}_manager");
        parent::display();
    }

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
            $this->setRedirect(\JRoute::_($url, false));
        }

        $success = $this->getModel('course')->changeParticipantState();

        if (empty($success)) {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
        } else {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
        }

        $url .= "&view=course_manager&lessonID={$formData['id']}";
        $this->setRedirect(\JRoute::_($url, false));
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
        $this->setRedirect(\JRoute::_($redirect, false));
    }

    /**
     * Makes call to the models's delete function, and redirects to the manager view.
     *
     * @return void
     */
    public function delete()
    {
        $success = $this->getModel($this->resource)->delete($this->resource);

        if ($success) {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_DELETE_SUCCESS');
        } else {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_DELETE_FAIL', 'error');
        }

        $url = THM_OrganizerHelperComponent::getRedirectBase();
        $url .= "&view={$this->resource}_manager";
        $this->setRedirect($url);
    }

    /**
     * Redirects to the edit view with an item id. Access checks performed in the view.
     *
     * @return void
     */
    public function edit()
    {
        $cid        = $this->input->get('cid', [], '[]');
        $resourceID = count($cid) > 0 ? $cid[0] : 0;

        $this->input->set('view', "{$this->resource}_edit");
        $this->input->set('id', $resourceID);
        parent::display();
    }

    /**
     * Makes call to the models's importLSFData function, and redirects to the manager view.
     *
     * @return void
     */
    public function importLSFData()
    {
        $modelName = 'LSF' . ucfirst($this->resource);
        $success   = $this->getModel($modelName)->importBatch();
        if ($success) {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_IMPORT_SUCCESS');
        } else {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_IMPORT_FAIL', 'error');
        }

        $url = THM_OrganizerHelperComponent::getRedirectBase();
        $url .= "&view={$this->resource}_manager";
        $this->setRedirect($url);
    }

    /**
     * Performs access checks, makes call to the models's merge function, and
     * redirects to the room manager view
     *
     * @return void
     */
    public function merge()
    {
        $success = $this->getModel($this->resource)->merge($this->resource);
        if ($success) {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_MERGE_SUCCESS');
        } else {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_MERGE_FAIL', 'error');
        }

        $url = THM_OrganizerHelperComponent::getRedirectBase();
        $url .= "&view={$this->resource}_manager";
        $this->setRedirect($url);
    }

    /**
     * Attempts to automatically merge the selected resources, if the corresponding function is available. Redirects to
     * the merge view if the automatic merge was unavailable or implausible.
     *
     * @return void
     */
    public function mergeView()
    {
        $url = "index.php?option=com_thm_organizer&view={$this->resource}_manager";

        $selected = $this->input->get('cid', [], '[]');
        if (count($selected) == 1) {
            $msg = \JText::_('COM_THM_ORGANIZER_MESSAGE_ERROR_TOOFEW');
            $this->setRedirect(\JRoute::_($url, false), $msg, 'warning');
            return;
        }

        $model             = $this->getModel($this->resource);
        $functionAvailable = method_exists($model, 'autoMerge');
        if ($functionAvailable) {
            $autoMerged = $model->autoMerge();
            if ($autoMerged) {
                $msg = \JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_SUCCESS');
                $this->setRedirect(\JRoute::_($url, false), $msg);
                return;
            }
        }

        // Reliance on POST requires a different method of redirection
        $this->input->set('view', "{$this->resource}_merge");
        parent::display();
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
            $this->setRedirect(\JRoute::_($url, false));
        }

        $formData           = $this->input->get('jform', [], 'array');
        $participantModel   = $this->getModel('participant');
        $participantEditURL = "{$url}&view=participant_edit&lessonID=$courseID";

        // Called from participant profile form
        if (!empty($formData)) {
            $participantSaved = $participantModel->save();

            if (empty($participantSaved)) {
                THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
                $this->setRedirect(\JRoute::_($participantEditURL, false));

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
            $this->setRedirect(\JRoute::_($participantEditURL, false));

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
                $newState = THM_OrganizerHelperCourses::getParticipantState();
                $msg      = $newState['status'] ?
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

        $this->setRedirect(\JRoute::_($url, false));
    }

    /**
     * Save user information from form and if course id defined sign in or out of course
     * then redirect to course list view
     *
     * @return void
     */
    public function save()
    {
        $resourceID = $this->getModel($this->resource)->save();

        $isBackend  = THM_OrganizerHelperComponent::getApplication()->isClient('administrator');
        $data       = $this->input->get('jform', [], 'array');
        $formID     = empty($data['id']) ? 0 : (int)$data['id'];
        $lessonID = $this->resource == 'course' ? $formID : $this->input->getInt('lessonID', 0);
        $url        = THM_OrganizerHelperComponent::getRedirectBase();
        if (empty($resourceID)) {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');

            if ($isBackend) {
                $url .= "&view={$this->resource}_manager";
            } else {
                switch ($this->resource) {
                    case 'participant':
                        $url .= '&view=participant_edit';
                        break;
                    case 'subject' :
                        $url .= "&view=subject_edit&id={$formID}";
                        $url .= empty($lessonID) ? '' : "&lessonID=$lessonID";
                        break;
                    default:
                        $url .= "&view=course_manager";
                        $url .= empty($lessonID) ? '' : "&lessonID=$lessonID";
                        break;
                }
            }
        } else {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS', 'success');

            if ($isBackend) {
                $url .= "&view={$this->resource}_manager";
            } else {
                switch ($this->resource) {
                    case 'participant':
                        $url .= '&view=course_list';
                        break;
                    default:
                        $url .= "&view=course_manager";
                        $url .= empty($lessonID) ? '' : "&lessonID=$lessonID";
                        break;
                }
            }
        }

        $this->setRedirect(\JRoute::_($url, false));
    }

    /**
     * Makes call to the models's save2copy function, and redirects to the manager view.
     *
     * @return void
     */
    public function save2copy()
    {
        $success = $this->getModel($this->resource)->save2copy();
        if ($success) {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
        } else {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
        }

        $url = THM_OrganizerHelperComponent::getRedirectBase();
        $url .= "&view={$this->resource}_manager";
        $this->setRedirect($url);
    }

    /**
     * Makes call to the models's save2new function, and redirects to the edit view.
     *
     * @return void
     */
    public function save2new()
    {
        $success = $this->getModel($this->resource)->save();
        if ($success) {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
        } else {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
        }

        $url = THM_OrganizerHelperComponent::getRedirectBase();
        $url .= "&view={$this->resource}_edit&id=0";
        $this->setRedirect($url);
    }

    /**
     * performs access checks, activates/deactivates the chosen schedule in the
     * context of its planning period, and redirects to the schedule manager view
     *
     * @return void
     */
    public function setReference()
    {
        if ($this->resource != 'schedule') {
            return;
        }

        $count = $this->input->getInt('boxchecked', 0);
        if ($count === 1) {
            $model  = $this->getModel('schedule');
            $active = $model->checkIfActive();
            if ($active) {
                THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_ERROR_ACTIVE_YES', 'error');
            } else {
                $success = $model->setReference();
                if ($success) {
                    THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_REFERENCE_SUCCESS');
                } else {
                    THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_REFERENCE_FAIL', 'error');
                }
            }
        } else {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_ERROR_ONE_ALLOWED', 'error');
        }

        $url = THM_OrganizerHelperComponent::getRedirectBase();
        $url .= "&view=schedule_manager";
        $this->setRedirect($url);
    }

    /**
     * Toggles category behaviour properties
     *
     * @return void
     */
    public function toggle()
    {
        $model = $this->getModel($this->resource);

        $functionAvailable = method_exists($model, 'toggle');

        if ($functionAvailable) {
            $success = $model->toggle();
            if ($success) {
                THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS', 'error');
            } else {
                THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
            }
        } else {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_FUNCTION_UNAVAILABLE', 'error');
        }

        $url = THM_OrganizerHelperComponent::getRedirectBase();
        $url .= "&view={$this->resource}_manager";
        $this->setRedirect($url);
    }

    /**
     * Makes call to the models's updateLSFData function, and redirects to the manager view.
     *
     * @return void
     */
    public function updateLSFData()
    {
        $modelName = 'LSF' . ucfirst($this->resource);
        $success   = $this->getModel($modelName)->updateBatch();

        if ($success) {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_UPDATE_SUCCESS');
        } else {
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_UPDATE_FAIL', 'error');
        }

        $url = THM_OrganizerHelperComponent::getRedirectBase();
        $url .= "&view={$this->resource}_manager";
        $this->setRedirect($url);
    }

    /**
     * Performs access checks and uses the model's upload function to validate
     * and save the file to the database should validation be successful
     *
     * @param boolean $shouldNotify true if Upload and Notify button is pressed
     *
     * @return void
     */
    public function upload($shouldNotify = false)
    {
        $model             = $this->getModel($this->resource);
        $functionAvailable = method_exists($model, 'upload');

        if ($functionAvailable) {
            $form      = $this->input->files->get('jform', [], '[]');
            $file      = $form['file'];
            $validType = (!empty($file['type']) and $file['type'] == 'text/xml');

            if ($validType) {
                if (mb_detect_encoding($file['tmp_name'], 'UTF-8', true) === 'UTF-8') {
                    $success = $model->upload($shouldNotify);
                    $view    = $success ? 'manager' : 'edit';
                } else {
                    $view = 'edit';
                    THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_ERROR_FILE_ENCODING', 'error');
                }

            } else {
                $view = 'edit';
                THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_ERROR_FILE_TYPE', 'error');
            }
        } else {
            $view = 'manager';
            THM_OrganizerHelperComponent::message('COM_THM_ORGANIZER_MESSAGE_FUNCTION_UNAVAILABLE', 'error');
        }

        $url = THM_OrganizerHelperComponent::getRedirectBase();
        $url .= "&view={$this->resource}_{$view}";
        $this->setRedirect($url);
    }

    /**
     * Calls the upload function and notifies all subscribed users
     *
     * @return void
     */
    public function uploadAndNotify()
    {
        $this->upload(true);
    }
}
