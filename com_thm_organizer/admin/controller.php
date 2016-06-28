<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerController
 * @description main controller class for thm organizer admin area
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

/**
 * Class for non-specific component calls
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerController extends JControllerLegacy
{
    private $_resource = '';

    /**
     * Class constructor
     *
     * @param   mixed  $properties  Either and associative array or another
     *                              object to set the initial properties of the object.
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $task = JFactory::getApplication()->input->get('task', '');
        $taskParts = explode('.', $task);
        $this->_resource = $taskParts[0];
    }

    /**
     * Redirects to the edit view without an item id. Access checks performed in the view.
     *
     * @return void
     */
    public function add()
    {
        $this->setRedirect("index.php?option=com_thm_organizer&view={$this->_resource}_edit");
    }

    /**
     * Makes call to the models's save function, and redirects to the manager view.
     *
     * @return  void
     */
    public function apply()
    {
        $resourceID = $this->getModel($this->_resource)->save($this->_resource);
        $url = "index.php?option=com_thm_organizer&view={$this->_resource}_edit";
        if (!empty($resourceID))
        {
            $idQuery = "&id=$resourceID";
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $type = 'message';
        }
        else
        {
            $idQuery = '';
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $type = 'error';
        }
        $this->setRedirect(JRoute::_($url . $idQuery, false), $msg, $type);
    }

    /**
     * Redirects to the manager from the form.
     *
     * @return  void
     */
    public function cancel()
    {
        $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view={$this->_resource}_manager", false));
    }

    /**
     * Makes call to the models's delete function, and redirects to the manager view.
     *
     * @return  void
     */
    public function delete()
    {
        $success = $this->getModel($this->_resource)->delete($this->_resource);
        $url = "index.php?option=com_thm_organizer&view={$this->_resource}_manager";
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_SUCCESS');
            $type = 'message';
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_FAIL');
            $type = 'error';
        }
        $this->setRedirect(JRoute::_($url, false), $msg, $type);
    }

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return  JController  This object to support chaining.
     */
    public function display($cachable = false, $urlparams = array())
    {
        parent::display($cachable, $urlparams);
    }

    /**
     * Redirects to the edit view with an item id. Access checks performed in the view.
     *
     * @return  void
     */
    public function edit()
    {
        $cid = $this->input->get('cid', array(), 'array');

        // Only edit the first id in the list
        if (count($cid) > 0)
        {
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view={$this->_resource}_edit&id=$cid[0]", false));
        }
        else
        {
            $this->setRedirect("index.php?option=com_thm_organizer&view={$this->_resource}_edit");
        }
    }

    /**
     * Makes call to the models's importLSFData function, and redirects to the manager view.
     *
     * @return  void
     */
    public function importLSFData()
    {
        $modelName = "LSF" . ucfirst($this->_resource);
        $success = $this->getModel($modelName)->importBatch();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_IMPORT_SUCCESS');
            $type = 'message';
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_IMPORT_FAIL');
            $type = 'error';
        }
        $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view={$this->_resource}_manager", false), $msg, $type);
    }

    /**
     * Performs access checks, makes call to the models's merge function, and
     * redirects to the room manager view
     *
     * @return  void
     */
    public function merge()
    {
        $success = $this->getModel($this->_resource)->merge($this->_resource);
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_SUCCESS');
            $type = 'message';
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_FAIL');
            $type = 'error';
        }
        $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view={$this->_resource}_manager", false), $msg, $type);
    }

    /**
     * Attempts to automatically merge the selected resources, if the corresponding function is available. Redirects to
     * the merge view if the automatic merge was unavailable or implausible.
     *
     * @return  void
     */
    public function mergeView()
    {
        $url = "index.php?option=com_thm_organizer&view={$this->_resource}_manager";

        $input = JFactory::getApplication()->input;
        $selected = $input->get('cid', array(), 'array');
        if (count($selected) == 1)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_ERROR_TOOFEW');
            $this->setRedirect(JRoute::_($url, false), $msg, 'warning');
        }

        $model = $this->getModel($this->_resource);
        $functionAvailable = method_exists($model, 'autoMerge');
        if ($functionAvailable)
        {
            $autoMerged = $model->autoMerge($this->_resource);
            if ($autoMerged)
            {
                $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_SUCCESS');
                $this->setRedirect(JRoute::_($url, false), $msg);
            }
        }

        // Reliance on POST requires a different method of redirection
        $input->set('view', "{$this->_resource}_merge");
        parent::display();
    }

    /**
     * Makes call to the models's save function, and redirects to the manager view.
     *
     * @return  void
     */
    public function save()
    {
        $success = $this->getModel($this->_resource)->save($this->_resource);
        $url = "index.php?option=com_thm_organizer&view={$this->_resource}_manager";
        if (!empty($success))
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $type = 'message';
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $type = 'error';
        }
        $this->setRedirect(JRoute::_($url, false), $msg, $type);
    }

    /**
     * Makes call to the models's save2copy function, and redirects to the manager view.
     *
     * @return  void
     */
    public function save2copy()
    {
        $success = $this->getModel($this->_resource)->save2copy();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $type = 'message';
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $type = 'error';
        }
        $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view={$this->_resource}_manager", false), $msg, $type);
    }

    /**
     * Makes call to the models's save2new function, and redirects to the edit view.
     *
     * @return  void
     */
    public function save2new()
    {
        $success = $this->getModel($this->_resource)->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $type = 'message';
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $type = 'error';
        }
        $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view={$this->_resource}_edit&id=0", false), $msg, $type);
    }

    /**
     * Peforms an update(import) of all existing subjects.
     *
     * @return void
     */
    public function updateAll()
    {
        $model = JModelLegacy::getInstance('LSFSubject', 'THM_OrganizerModel');
        $model->updateAll();
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=subject_manager', false));$this->getTask();
    }
}
