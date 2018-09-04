<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class receives user actions and performs access checks and redirection.
 */
class THM_OrganizerController extends JControllerLegacy
{
    private $resource = '';

    /**
     * Class constructor
     *
     * @param array $config An optional associative [] of configuration settings.
     *
     * @throws Exception
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $task           = JFactory::getApplication()->input->get('task', '');
        $taskParts      = explode('.', $task);
        $this->resource = $taskParts[0];
    }

    /**
     * Performs access checks. Checks if the schedule is already active. If the
     * schedule is not already active, calls the activate function of the
     * schedule model.
     *
     * @return void
     * @throws Exception
     */
    public function activate()
    {
        $model = $this->getModel($this->resource);

        $functionsAvailable = (method_exists($model, 'activate') and method_exists($model, 'checkIfActive'));
        if ($functionsAvailable) {
            $count = JFactory::getApplication()->input->getInt('boxchecked', 0);
            if ($count === 1) {
                $active = $model->checkIfActive();
                if ($active) {
                    $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_ERROR_ACTIVE_YES');
                    $type = 'warning';
                } else {
                    $success = $model->activate();
                    if ($success) {
                        $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_ACTIVATE_SUCCESS');
                        $type = 'message';
                    } else {
                        $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_ACTIVATE_FAIL');
                        $type = 'error';
                    }
                }
            } else {
                $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_ERROR_ONE_ALLOWED');
                $type = 'error';
            }
        }

        $url = "index.php?option=com_thm_organizer&view={$this->resource}_manager";
        $this->setRedirect(JRoute::_($url, false), $msg, $type);
    }

    /**
     * Redirects to the edit view without an item id. Access checks performed in the view.
     *
     * @return void
     * @throws Exception
     */
    public function add()
    {
        JFactory::getApplication()->input->set('view', "{$this->resource}_edit");
        parent::display();
    }

    /**
     * Makes call to the models's save function, and redirects to the same view.
     *
     * @return void
     * @throws Exception
     */
    public function apply()
    {
        $resourceID = $this->getModel($this->resource)->save();

        if (!empty($resourceID)) {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $type = 'message';
        } else {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $type = 'error';
        }

        $app = JFactory::getApplication();
        $app->enqueueMessage($msg, $type);
        $app->input->set('view', "{$this->resource}_edit");
        $app->input->set('id', $resourceID);
        parent::display();
    }

    /**
     * Makes call to the models's batch function, and redirects to the manager view.
     *
     * @return void
     * @throws Exception
     */
    public function batch()
    {
        $success = $this->getModel($this->resource)->batch();

        if ($success) {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $type = 'message';
        } else {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $type = 'error';
        }

        $app = JFactory::getApplication();
        $app->enqueueMessage($msg, $type);
        $app->input->set('view', "{$this->resource}_manager");
        parent::display();
    }

    /**
     * Redirects to the manager from the form.
     *
     * @return void
     * @throws Exception
     */
    public function cancel()
    {
        JFactory::getApplication()->input->set('view', "{$this->resource}_manager");
        parent::display();
    }

    /**
     * Makes call to the models's delete function, and redirects to the manager view.
     *
     * @return void
     * @throws Exception
     */
    public function delete()
    {
        $success = $this->getModel($this->resource)->delete($this->resource);

        if ($success) {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_SUCCESS');
            $type = 'message';
        } else {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_FAIL');
            $type = 'error';
        }

        $app = JFactory::getApplication();
        $app->enqueueMessage($msg, $type);
        $app->input->set('view', "{$this->resource}_manager");
        parent::display();
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
     * @throws Exception
     */
    public function importLSFData()
    {
        $modelName = "LSF" . ucfirst($this->resource);
        $success   = $this->getModel($modelName)->importBatch();
        if ($success) {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_IMPORT_SUCCESS');
            $type = 'message';
        } else {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_IMPORT_FAIL');
            $type = 'error';
        }

        $app = JFactory::getApplication();
        $app->enqueueMessage($msg, $type);
        $app->input->set('view', "{$this->resource}_manager");
        parent::display();
    }

    /**
     * Performs access checks, makes call to the models's merge function, and
     * redirects to the room manager view
     *
     * @return void
     * @throws Exception
     */
    public function merge()
    {
        $success = $this->getModel($this->resource)->merge($this->resource);
        if ($success) {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_SUCCESS');
            $type = 'message';
        } else {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_FAIL');
            $type = 'error';
        }

        $app = JFactory::getApplication();
        $app->enqueueMessage($msg, $type);
        $app->input->set('view', "{$this->resource}_manager");
        parent::display();
    }

    /**
     * Attempts to automatically merge the selected resources, if the corresponding function is available. Redirects to
     * the merge view if the automatic merge was unavailable or implausible.
     *
     * @return void
     * @throws Exception
     */
    public function mergeView()
    {
        $url = "index.php?option=com_thm_organizer&view={$this->resource}_manager";

        $input    = JFactory::getApplication()->input;
        $selected = $input->get('cid', [], '[]');
        if (count($selected) == 1) {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_ERROR_TOOFEW');
            $this->setRedirect(JRoute::_($url, false), $msg, 'warning');
        }

        $model             = $this->getModel($this->resource);
        $functionAvailable = method_exists($model, 'autoMerge');
        if ($functionAvailable) {
            $autoMerged = $model->autoMerge();
            if ($autoMerged) {
                $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_SUCCESS');
                $this->setRedirect(JRoute::_($url, false), $msg);
            }
        }

        // Reliance on POST requires a different method of redirection
        $input->set('view', "{$this->resource}_merge");
        parent::display();
    }

    /**
     * Makes call to the models's save function, and redirects to the manager view.
     *
     * @return void
     * @throws Exception
     */
    public function save()
    {
        $success = $this->getModel($this->resource)->save();

        if (!empty($success)) {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $type = 'message';
        } else {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $type = 'error';
        }

        $app = JFactory::getApplication();
        $app->enqueueMessage($msg, $type);
        $app->input->set('view', "{$this->resource}_manager");
        parent::display();
    }

    /**
     * Makes call to the models's save2copy function, and redirects to the manager view.
     *
     * @return void
     * @throws Exception
     */
    public function save2copy()
    {
        $success = $this->getModel($this->resource)->save2copy();
        if ($success) {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $type = 'message';
        } else {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $type = 'error';
        }

        $app = JFactory::getApplication();
        $app->enqueueMessage($msg, $type);
        $app->input->set('view', "{$this->resource}_manager");
        parent::display();
    }

    /**
     * Makes call to the models's save2new function, and redirects to the edit view.
     *
     * @return void
     * @throws Exception
     */
    public function save2new()
    {
        $success = $this->getModel($this->resource)->save();
        if ($success) {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $type = 'message';
        } else {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $type = 'error';
        }

        $app = JFactory::getApplication();
        $app->enqueueMessage($msg, $type);
        $app->input->set('view', "{$this->resource}_edit");
        $app->input->set('id', 0);
        parent::display();
    }

    /**
     * performs access checks, activates/deactivates the chosen schedule in the
     * context of its planning period, and redirects to the schedule manager view
     *
     * @return void
     * @throws Exception
     */
    public function setReference()
    {
        if ($this->resource != 'schedule') {
            return;
        }

        $type  = 'error';
        $count = JFactory::getApplication()->input->getInt('boxchecked', 0);
        if ($count === 1) {
            $model  = $this->getModel('schedule');
            $active = $model->checkIfActive();
            if ($active) {
                $msg = JText::_("COM_THM_ORGANIZER_MESSAGE_ERROR_ACTIVE_YES");
            } else {
                $success = $model->setReference();
                if ($success) {
                    $msg  = JText::_("COM_THM_ORGANIZER_MESSAGE_REFERENCE_SUCCESS");
                    $type = 'message';
                } else {
                    $msg = JText::_("COM_THM_ORGANIZER_MESSAGE_REFERENCE_FAIL");
                }
            }
        } else {
            $msg = JText::_("COM_THM_ORGANIZER_MESSAGE_ERROR_ONE_ALLOWED");
        }

        $app = JFactory::getApplication();
        $app->enqueueMessage($msg, $type);
        $app->input->set('view', "schedule_manager");
        parent::display();
    }

    /**
     * Toggles category behaviour properties
     *
     * @return void
     * @throws Exception
     */
    public function toggle()
    {
        $model = $this->getModel($this->resource);

        $type              = 'error';
        $functionAvailable = method_exists($model, 'toggle');

        if ($functionAvailable) {
            $success = $model->toggle();
            if ($success) {
                $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
                $type = 'message';
            } else {
                $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            }
        } else {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_FUNCTION_UNAVAILABLE');
        }

        $app = JFactory::getApplication();
        $app->enqueueMessage($msg, $type);
        $app->input->set('view', "{$this->resource}_manager");
        parent::display();
    }

    /**
     * Makes call to the models's updateLSFData function, and redirects to the manager view.
     *
     * @return void
     * @throws Exception
     */
    public function updateLSFData()
    {
        $modelName = "LSF" . ucfirst($this->resource);
        $success   = $this->getModel($modelName)->updateBatch();

        if ($success) {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_UPDATE_SUCCESS');
            $type = 'message';
        } else {
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_UPDATE_FAIL');
            $type = 'error';
        }

        $app = JFactory::getApplication();
        $app->enqueueMessage($msg, $type);
        $app->input->set('view', "{$this->resource}_manager");
        parent::display();
    }

    /**
     * Performs access checks and uses the model's upload function to validate
     * and save the file to the database should validation be successful
     *
     * @return void
     * @throws Exception
     */
    public function upload()
    {
        $model = $this->getModel($this->resource);

        $type              = 'error';
        $functionAvailable = method_exists($model, 'upload');

        if ($functionAvailable) {
            $form      = JFactory::getApplication()->input->files->get('jform', [], '[]');
            $file      = $form['file'];
            $validType = (!empty($file['type']) and $file['type'] == 'text/xml');

            if ($validType) {
                if (mb_detect_encoding($file['tmp_name'], 'UTF-8', true) === 'UTF-8') {
                    $success = $model->upload();
                    $view    = $success ? 'manager' : 'edit';
                } else {
                    $view = 'edit';
                    $msg  = JText::_("COM_THM_ORGANIZER_MESSAGE_ERROR_FILE_ENCODING");
                }

            } else {
                $view = 'edit';
                $msg  = JText::_("COM_THM_ORGANIZER_MESSAGE_ERROR_FILE_TYPE");
            }
        } else {
            $view = 'manager';
            $msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_FUNCTION_UNAVAILABLE');
        }

        $app = JFactory::getApplication();

        if (!empty($msg)) {
            $app->enqueueMessage($msg, $type);
        }

        $app->input->set('view', "{$this->resource}_{$view}");
        parent::display();
    }
}
