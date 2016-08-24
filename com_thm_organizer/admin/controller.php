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
	 * @param   array $config An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$task            = JFactory::getApplication()->input->get('task', '');
		$taskParts       = explode('.', $task);
		$this->_resource = $taskParts[0];
	}

	/**
	 * Performs access checks. Checks if the schedule is already active. If the
	 * schedule is not already active, calls the activate function of the
	 * schedule model.
	 *
	 * @return  void
	 */
	public function activate()
	{
		$model = $this->getModel($this->_resource);

		$functionsAvailable = (method_exists($model, 'activate') AND method_exists($model, 'checkIfActive'));
		if ($functionsAvailable)
		{
			$count = JFactory::getApplication()->input->getInt('boxchecked', 0);
			if ($count === 1)
			{
				$active = $model->checkIfActive();
				if ($active)
				{
					$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_ERROR_ACTIVE_YES');
					$type = 'warning';
				}
				else
				{
					$success = $model->activate();
					if ($success)
					{
						$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_ACTIVATE_SUCCESS');
						$type = 'message';
					}
					else
					{
						$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_ACTIVATE_FAIL');
						$type = 'error';
					}
				}
			}
			else
			{
				$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_ERROR_ONE_ALLOWED');
				$type = 'error';
			}
		}

		$url = "index.php?option=com_thm_organizer&view={$this->_resource}_manager";
		$this->setRedirect(JRoute::_($url, false), $msg, $type);
	}

	/**
	 * Redirects to the edit view without an item id. Access checks performed in the view.
	 *
	 * @return void
	 */
	public function add()
	{
		JFactory::getApplication()->input->set('view', "{$this->_resource}_edit");
		parent::display();
	}

	/**
	 * Makes call to the models's save function, and redirects to the manager view.
	 *
	 * @return  void
	 */
	public function apply()
	{
		$resourceID = $this->getModel($this->_resource)->save($this->_resource);

		if (!empty($resourceID))
		{
			$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
			$type = 'message';
		}
		else
		{
			$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
			$type = 'error';
		}

		$app = JFactory::getApplication();
		$app->enqueueMessage($msg, $type);
		$app->input->set('view', "{$this->_resource}_edit");
		$app->input->set('id', $resourceID);
		parent::display();
	}

	/**
	 * Redirects to the manager from the form.
	 *
	 * @return  void
	 */
	public function cancel()
	{
		JFactory::getApplication()->input->set('view', "{$this->_resource}_manager");
		parent::display();
	}

	/**
	 * Makes call to the models's delete function, and redirects to the manager view.
	 *
	 * @return  void
	 */
	public function delete()
	{
		$success = $this->getModel($this->_resource)->delete($this->_resource);

		if ($success)
		{
			$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_SUCCESS');
			$type = 'message';
		}
		else
		{
			$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_FAIL');
			$type = 'error';
		}

		$app = JFactory::getApplication();
		$app->enqueueMessage($msg, $type);
		$app->input->set('view', "{$this->_resource}_manager");
		parent::display();
	}

	/**
	 * Method to display a view.
	 *
	 * @param   boolean $cachable  If true, the view output will be cached
	 * @param   array   $urlparams An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
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
		$cid        = $this->input->get('cid', array(), 'array');
		$resourceID = count($cid) > 0 ? $cid[0] : 0;

		$this->input->set('view', "{$this->_resource}_edit");
		$this->input->set('id', $resourceID);
		parent::display();
	}

	/**
	 * Makes call to the models's importLSFData function, and redirects to the manager view.
	 *
	 * @return  void
	 */
	public function importLSFData()
	{
		$modelName = "LSF" . ucfirst($this->_resource);
		$success   = $this->getModel($modelName)->importBatch();
		if ($success)
		{
			$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_IMPORT_SUCCESS');
			$type = 'message';
		}
		else
		{
			$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_IMPORT_FAIL');
			$type = 'error';
		}

		$app = JFactory::getApplication();
		$app->enqueueMessage($msg, $type);
		$app->input->set('view', "{$this->_resource}_manager");
		parent::display();
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
			$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_SUCCESS');
			$type = 'message';
		}
		else
		{
			$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_FAIL');
			$type = 'error';
		}

		$app = JFactory::getApplication();
		$app->enqueueMessage($msg, $type);
		$app->input->set('view', "{$this->_resource}_manager");
		parent::display();
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

		$input    = JFactory::getApplication()->input;
		$selected = $input->get('cid', array(), 'array');
		if (count($selected) == 1)
		{
			$msg = JText::_('COM_THM_ORGANIZER_MESSAGE_ERROR_TOOFEW');
			$this->setRedirect(JRoute::_($url, false), $msg, 'warning');
		}

		$model             = $this->getModel($this->_resource);
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
	 * Performs access checks and uses the model's upload function to validate
	 * and save the file to the database should validation be successful
	 *
	 * @return void
	 */
	public function migrate()
	{
		$model = $this->getModel($this->_resource);

		$type              = 'error';
		$functionAvailable = method_exists($model, 'migrate');
		$view              = 'manager';

		if ($functionAvailable)
		{
			$success = $model->migrate();
			$msg     = $success ?
				JText::_('COM_THM_ORGANIZER_MESSAGE_MIGRATE_SUCCESS') : JText::_('COM_THM_ORGANIZER_MESSAGE_MIGRATE_FAIL');
			$type    = 'message';
		}
		else
		{
			$msg = JText::_('COM_THM_ORGANIZER_MESSAGE_FUNCTION_UNAVAILABLE');
		}


		$app = JFactory::getApplication();
		if (!empty($msg))
		{
			$app->enqueueMessage($msg, $type);
		}
		$app->input->set('view', "{$this->_resource}_{$view}");
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

		if (!empty($success))
		{
			$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
			$type = 'message';
		}
		else
		{
			$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
			$type = 'error';
		}

		$app = JFactory::getApplication();
		$app->enqueueMessage($msg, $type);
		$app->input->set('view', "{$this->_resource}_manager");
		parent::display();
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
			$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
			$type = 'message';
		}
		else
		{
			$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
			$type = 'error';
		}

		$app = JFactory::getApplication();
		$app->enqueueMessage($msg, $type);
		$app->input->set('view', "{$this->_resource}_manager");
		parent::display();
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
			$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
			$type = 'message';
		}
		else
		{
			$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
			$type = 'error';
		}

		$app = JFactory::getApplication();
		$app->enqueueMessage($msg, $type);
		$app->input->set('view', "{$this->_resource}_edit");
		$app->input->set('id', 0);
		parent::display();
	}

	/**
	 * performs access checks, activates/deactivates the chosen schedule in the
	 * context of its planning period, and redirects to the schedule manager view
	 *
	 * @return void
	 */
	public function setReference()
	{
		if ($this->_resource != 'schedule')
		{
			return;
		}

		$type  = 'error';
		$count = JFactory::getApplication()->input->getInt('boxchecked', 0);
		if ($count === 1)
		{
			$model  = $this->getModel('schedule');
			$active = $model->checkIfActive();
			if ($active)
			{
				$msg = JText::_("COM_THM_ORGANIZER_MESSAGE_ERROR_ACTIVE_YES");
			}
			else
			{
				$success = $model->setReference();
				if ($success)
				{
					$msg  = JText::_("COM_THM_ORGANIZER_MESSAGE_REFERENCE_SUCCESS");
					$type = 'message';
				}
				else
				{
					$msg = JText::_("COM_THM_ORGANIZER_MESSAGE_REFERENCE_FAIL");
				}
			}
		}
		else
		{
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
	 */
	public function toggle()
	{
		$model = $this->getModel($this->_resource);

		$type              = 'error';
		$functionAvailable = method_exists($model, 'autoMerge');

		if ($functionAvailable)
		{
			$success = $model->toggle();
			if ($success)
			{
				$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
				$type = 'message';
			}
			else
			{
				$msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
			}
		}
		else
		{
			$msg = JText::_('COM_THM_ORGANIZER_MESSAGE_FUNCTION_UNAVAILABLE');
		}

		$app = JFactory::getApplication();
		$app->enqueueMessage($msg, $type);
		$app->input->set('view', "{$this->_resource}_manager");
		parent::display();
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
		$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=subject_manager', false));
		$this->getTask();
	}

	/**
	 * Performs access checks and uses the model's upload function to validate
	 * and save the file to the database should validation be successful
	 *
	 * @return void
	 */
	public function upload()
	{
		$url   = "index.php?option=com_thm_organizer&view={$this->_resource}_";
		$model = $this->getModel($this->_resource);

		$type              = 'error';
		$functionAvailable = method_exists($model, 'upload');

		if ($functionAvailable)
		{
			$form      = JFactory::getApplication()->input->files->get('jform', array(), 'array');
			$file      = $form['file'];
			$validType = (!empty($file['type']) AND $file['type'] == 'text/xml');
			if ($validType)
			{
				$success = $model->upload();
				$view    = $success ? 'manager' : 'edit';
			}
			else
			{
				$view = 'edit';
				$msg  = JText::_("COM_THM_ORGANIZER_MESSAGE_ERROR_FILETYPE");
			}
		}
		else
		{
			$view = 'manager';
			$msg  = JText::_('COM_THM_ORGANIZER_MESSAGE_FUNCTION_UNAVAILABLE');
		}


		$app = JFactory::getApplication();
		if (!empty($msg))
		{
			$app->enqueueMessage($msg, $type);
		}
		$app->input->set('view', "{$this->_resource}_{$view}");
		parent::display();
	}
}
