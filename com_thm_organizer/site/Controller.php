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

namespace Organizer;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Router\Route;
use Organizer\Helpers\Courses;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Routing;

/**
 * Class receives user actions and performs access checks and redirection.
 */
class Controller extends BaseController
{
	private $listView = '';

	private $resource = '';

	/**
	 * Class constructor
	 *
	 * @param   array  $config  An optional associative [] of configuration settings.
	 */
	public function __construct($config = [])
	{
		$config['base_path']    = JPATH_COMPONENT_SITE;
		$config['model_prefix'] = '';
		parent::__construct($config);
		$task           = Input::getTask();
		$taskParts      = explode('.', $task);
		$this->resource = $taskParts[0];
		$this->listView = OrganizerHelper::getPlural($this->resource);
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
		if ($functionsAvailable)
		{
			$count = $this->input->getInt('boxchecked', 0);
			if ($count === 1)
			{
				$active = $model->checkIfActive();
				if ($active)
				{
					OrganizerHelper::message('THM_ORGANIZER_SCHEDULE_IS_ACTIVE', 'warning');
				}
				else
				{
					$success = $model->activate();
					if ($success)
					{
						OrganizerHelper::message('THM_ORGANIZER_MESSAGE_ACTIVATE_SUCCESS');
					}
					else
					{
						OrganizerHelper::message('THM_ORGANIZER_MESSAGE_ACTIVATE_FAIL', 'error');
					}
				}
			}
			else
			{
				OrganizerHelper::message('THM_ORGANIZER_ONLY_ONE_ALLOWED', 'error');
			}
		}

		$this->setRedirect("index.php?option=com_thm_organizer&view={$this->listView}");
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

		if (!empty($resourceID))
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
		}

		$url = Routing::getRedirectBase();
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

		if ($success)
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
		}

		$url = Routing::getRedirectBase();
		$url .= "&view={$this->listView}";
		$this->setRedirect($url);
	}

	/**
	 * Redirects to the manager from the form.
	 *
	 * @return void
	 */
	public function cancel()
	{
		$this->input->set('view', $this->listView);
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
		$lessonID = Input::getID();
		$url      = Routing::getRedirectBase();

		if (empty($lessonID))
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

		$url .= "&view=courses&lessonID=$lessonID";
		$this->setRedirect(Route::_($url, false));
	}

	/**
	 * Sends an circular email to all course participants
	 *
	 * @return void
	 */
	public function circular()
	{
		if (empty($this->getModel('course')->circular()))
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_MAIL_SEND_FAIL', 'error');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_MAIL_SEND_SUCCESS', 'error');
		}

		$lessonID = $this->input->get('lessonID');
		$redirect = Routing::getRedirectBase() . "view=courses&lessonID=$lessonID";
		$this->setRedirect(Route::_($redirect, false));
	}

	/**
	 * Makes call to the models's delete function, and redirects to the manager view.
	 *
	 * @return void
	 */
	public function delete()
	{
		$success = $this->getModel($this->resource)->delete($this->resource);

		if ($success)
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_DELETE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_DELETE_FAIL', 'error');
		}

		$url = Routing::getRedirectBase();
		$url .= "&view={$this->listView}";
		$this->setRedirect($url);
	}

	/**
	 * Typical view method for MVC based architecture.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types.
	 *
	 * @return BaseController  A BaseController object to support chaining.
	 * @throws Exception
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$document   = Factory::getDocument();
		$viewType   = $document->getType();
		$viewName   = $this->input->get('view', $this->default_view);
		$viewLayout = $this->input->get('layout', 'default', 'string');

		$view = $this->getView(
			$viewName,
			$viewType,
			'',
			array('base_path' => $this->basePath, 'layout' => $viewLayout)
		);

		// JSON Views rely on standard functions available in helper files
		if ($viewType !== 'json' and $model = $this->getModel($viewName))
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		$view->document = $document;

		// Display the view
		if ($cachable && $viewType !== 'feed' && \JFactory::getConfig()->get('caching') >= 1)
		{
			$option = $this->input->get('option');

			if (is_array($urlparams))
			{
				$app = \JFactory::getApplication();

				if (!empty($app->registeredurlparams))
				{
					$registeredurlparams = $app->registeredurlparams;
				}
				else
				{
					$registeredurlparams = new \stdClass;
				}

				foreach ($urlparams as $key => $value)
				{
					// Add your safe URL parameters with variable type as value {@see \JFilterInput::clean()}.
					$registeredurlparams->$key = $value;
				}

				$app->registeredurlparams = $registeredurlparams;
			}

			$cache = Factory::getCache($option, 'view');
			$cache->get($view, 'display');
		}
		else
		{
			$view->display();
		}

		return $this;
	}

	/**
	 * Redirects to the edit view with an item id. Access checks performed in the view.
	 *
	 * @return void
	 */
	public function edit()
	{
		$selectedIDs = Input::getSelectedIDs();
		$resourceID  = count($selectedIDs) > 0 ? $selectedIDs[0] : 0;

		$this->input->set('view', "{$this->resource}_edit");
		$this->input->set('id', $resourceID);
		parent::display();
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object|boolean  Model object on success; otherwise false on failure.
	 * @throws Exception
	 */
	public function getModel($name = '', $prefix = '', $config = array())
	{
		$name = empty($name) ? $this->getName() : $name;

		if (empty($name))
		{
			return false;
		}

		$modelName = "Organizer\\Models\\" . OrganizerHelper::getClass($name);

		if ($model = new $modelName($config))
		{
			// Task is a reserved state
			$model->setState('task', $this->task);

			// Let's get the application object and set menu information if it's available
			$menu = Factory::getApplication()->getMenu();

			if (is_object($menu) && $item = $menu->getActive())
			{
				$params = $menu->getParams($item->id);

				// Set default state data
				$model->setState('parameters.menu', $params);
			}
		}

		return $model;
	}

	/**
	 * Method to get a reference to the current view and load it if necessary.
	 *
	 * @param   string  $name    The view name. Optional, defaults to the controller name.
	 * @param   string  $type    The view type. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for view. Optional.
	 *
	 * @return  HtmlView  Reference to the view or an error.
	 *
	 * @throws  Exception
	 */
	public function getView($name = '', $type = '', $prefix = 'x', $config = array())
	{
		// @note We use self so we only access stuff in this class rather than in all classes.
		if (!isset(self::$views))
		{
			self::$views = array();
		}

		if (empty($name))
		{
			$name = $this->getName();
		}

		$viewName = OrganizerHelper::getClass($name);
		$type     = strtoupper(preg_replace('/[^A-Z0-9_]/i', '', $type));
		$name     = "Organizer\\Views\\$type\\$viewName";

		$config['base_path']     = JPATH_COMPONENT_SITE . "/Views/$type";
		$config['helper_path']   = JPATH_COMPONENT_SITE . "/Helpers";
		$config['template_path'] = JPATH_COMPONENT_SITE . "/Layouts/$type";

		$key = strtolower($viewName);
		if (empty(self::$views[$key][$type][$prefix]))
		{
			if ($view = new $name($config))
			{
				self::$views[$key][$type][$prefix] = &$view;
			}
			else
			{
				$message = sprintf(Languages::_('THM_ORGANIZER_VIEW_NOT_FOUND'), $name, $type, $prefix);
				throw new Exception($message, 404);
			}
		}

		return self::$views[$key][$type][$prefix];
	}

	/**
	 * Makes call to the models's importLSFData function, and redirects to the manager view.
	 *
	 * @return void
	 */
	public function importLSFData()
	{
		$modelName = ucfirst($this->resource) . 'LSF';
		$success   = $this->getModel($modelName)->importBatch();
		if ($success)
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_IMPORT_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_IMPORT_FAIL', 'error');
		}

		$url = Routing::getRedirectBase();
		$url .= "&view={$this->listView}";
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
		if ($success)
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_MERGE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_MERGE_FAIL', 'error');
		}

		$url = Routing::getRedirectBase();
		$url .= "&view={$this->listView}";
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
		$url = "index.php?option=com_thm_organizer&view={$this->listView}";

		if (JDEBUG)
		{
			OrganizerHelper::message('THM_ORGANIZER_DEBUG_ON', 'error');
			$this->setRedirect($url);

			return;
		}

		$selectedIDs = Input::getSelectedIDs();
		if (count($selectedIDs) == 1)
		{
			$msg = Languages::_('THM_ORGANIZER_TOO_FEW');
			$this->setRedirect(Route::_($url, false), $msg, 'warning');

			return;
		}

		$model = $this->getModel($this->resource);
		if (method_exists($model, 'autoMerge'))
		{
			$autoMerged = $model->autoMerge();
			if ($autoMerged)
			{
				$msg = Languages::_('THM_ORGANIZER_MESSAGE_MERGE_SUCCESS');
				$this->setRedirect(Route::_($url, false), $msg);

				return;
			}
		}

		// Reliance on POST requires a different method of redirection
		$this->input->set('view', "{$this->resource}_merge");
		parent::display();
	}

	/**
	 * Makes call to migrate the data
	 *
	 * @return void
	 * @throws Exception
	 */
	public function migrateConfigurations()
	{
		$success = $this->getModel($this->resource)->migrateConfigurations();

		if (!empty($success))
		{
			OrganizerHelper::message('Configurations have been migrated');
		}
		else
		{
			OrganizerHelper::message('Configurations have not been migrated', 'error');
		}

		$url = Routing::getRedirectBase() . "&view={$this->resource}";
		$this->setRedirect($url);
	}

	/**
	 * Makes call to migrate the data
	 *
	 * @return void
	 * @throws Exception
	 */
	public function migrateSchedules()
	{
		$success = $this->getModel($this->resource)->migrateSchedules();

		if (!empty($success))
		{
			OrganizerHelper::message('Schedules have been migrated');
		}
		else
		{
			OrganizerHelper::message('Schedules have not been migrated', 'error');
		}

		$url = Routing::getRedirectBase() . "&view={$this->resource}";
		$this->setRedirect($url);
	}

	/**
	 * Makes call to migrate the data
	 *
	 * @return void
	 * @throws Exception
	 */
	public function migrateUserLessons()
	{
		$success = $this->getModel($this->resource)->migrateUserLessons();

		if (!empty($success))
		{
			OrganizerHelper::message('User lessons have been migrated');
		}
		else
		{
			OrganizerHelper::message('User lessons have not been migrated', 'error');
		}

		$url = Routing::getRedirectBase() . "&view={$this->resource}";
		$this->setRedirect($url);
	}

	/**
	 * Sets the publication status for any group / complete term pairing to true
	 *
	 * @return void
	 * @throws Exception
	 */
	public function publishPast()
	{
		$success = $this->getModel('group')->publishPast();
		$url     = Routing::getRedirectBase() . '&view=groups';
		if ($success)
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_SUCCESS', 'success');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
		}

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
		if (empty($courseID) or !Courses::isRegistrationOpen())
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

		$userState = Courses::getParticipantState();

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
				$newState = Courses::getParticipantState();
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
	 * Save user information from form and if course id defined sign in or out of course
	 * then redirect to course list view
	 *
	 * @return void
	 */
	public function save()
	{
		$resourceID = $this->getModel($this->resource)->save();

		$isBackend = OrganizerHelper::getApplication()->isClient('administrator');
		$requestID = Input::getID();
		$lessonID  = $this->resource == 'course' ? $requestID : Input::getInt('lessonID');
		$url       = Routing::getRedirectBase();
		if (empty($resourceID))
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');

			if ($isBackend)
			{
				$url .= "&view={$this->listView}";
			}
			else
			{
				switch ($this->resource)
				{
					case 'participant':
						$url .= '&view=participant_edit';
						break;
					case 'subject':
						$url .= "&view=subject_edit&id={$requestID}";
						$url .= empty($lessonID) ? '' : "&lessonID=$lessonID";
						break;
					default:
						$url .= "&view=courses";
						$url .= empty($lessonID) ? '' : "&lessonID=$lessonID";
						break;
				}
			}
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_SUCCESS', 'success');

			if ($isBackend)
			{
				$url .= "&view={$this->listView}";
			}
			else
			{
				switch ($this->resource)
				{
					case 'participant':
						$url .= '&view=course_list';
						break;
					default:
						$url .= "&view=courses";
						$url .= empty($lessonID) ? '' : "&lessonID=$lessonID";
						break;
				}
			}
		}

		$this->setRedirect(Route::_($url, false));
	}

	/**
	 * Makes call to the models's save2copy function, and redirects to the manager view.
	 *
	 * @return void
	 */
	public function save2copy()
	{
		$success = $this->getModel($this->resource)->save2copy();
		if ($success)
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
		}

		$url = Routing::getRedirectBase();
		$url .= "&view={$this->listView}";
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
		if ($success)
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
		}

		$url = Routing::getRedirectBase();
		$url .= "&view={$this->resource}_edit&id=0";
		$this->setRedirect($url);
	}

	/**
	 * performs access checks, activates/deactivates the chosen schedule in the
	 * context of its term, and redirects to the schedule manager view
	 *
	 * @return void
	 */
	public function setReference()
	{
		if ($this->resource != 'schedule')
		{
			return;
		}

		$count = $this->input->getInt('boxchecked', 0);
		if ($count === 1)
		{
			$model  = $this->getModel('schedule');
			$active = $model->checkIfActive();
			if ($active)
			{
				OrganizerHelper::message('THM_ORGANIZER_SCHEDULE_IS_ACTIVE', 'error');
			}
			else
			{
				$success = $model->setReference();
				if ($success)
				{
					OrganizerHelper::message('THM_ORGANIZER_MESSAGE_REFERENCE_SUCCESS');
				}
				else
				{
					OrganizerHelper::message('THM_ORGANIZER_MESSAGE_REFERENCE_FAIL', 'error');
				}
			}
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_ONLY_ONE_ALLOWED', 'error');
		}

		$url = Routing::getRedirectBase();
		$url .= "&view=schedules";
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
		if (method_exists($model, 'toggle'))
		{
			$success = $model->toggle();
			if ($success)
			{
				OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_SUCCESS', 'error');
			}
			else
			{
				OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
			}
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_FUNCTION_UNAVAILABLE', 'error');
		}

		$url = Routing::getRedirectBase();
		$url .= "&view={$this->listView}";
		$this->setRedirect($url);
	}

	/**
	 * Makes call to the models's updateLSFData function, and redirects to the manager view.
	 *
	 * @return void
	 */
	public function updateLSFData()
	{
		$modelName = ucfirst($this->resource) . 'LSF';
		$success   = $this->getModel($modelName)->updateBatch();

		if ($success)
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_UPDATE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_UPDATE_FAIL', 'error');
		}

		$url = Routing::getRedirectBase();
		$url .= "&view={$this->listView}";
		$this->setRedirect($url);
	}

	/**
	 * Performs access checks and uses the model's upload function to validate
	 * and save the file to the database should validation be successful
	 *
	 * @param   boolean  $shouldNotify  true if Upload and Notify button is pressed
	 *
	 * @return void
	 */
	public function upload($shouldNotify = false)
	{
		$url = Routing::getRedirectBase();
		if (JDEBUG)
		{
			OrganizerHelper::message('THM_ORGANIZER_DEBUG_ON', 'error');
			$url .= "&view=Schedules";
			$this->setRedirect($url);

			return;
		}

		$model = $this->getModel($this->resource);

		if (method_exists($model, 'upload'))
		{
			$form      = $this->input->files->get('jform', [], '[]');
			$file      = $form['file'];
			$validType = (!empty($file['type']) and $file['type'] == 'text/xml');

			if ($validType)
			{
				if (mb_detect_encoding($file['tmp_name'], 'UTF-8', true) === 'UTF-8')
				{
					$success = $model->upload($shouldNotify);
					$view    = $success ? 'Schedules' : 'Schedule_Edit';
				}
				else
				{
					$view = 'Schedule_Edit';
					OrganizerHelper::message('THM_ORGANIZER_FILE_ENCODING_INVALID', 'error');
				}
			}
			else
			{
				$view = 'Schedule_Edit';
				OrganizerHelper::message('THM_ORGANIZER_FILE_TYPE_NOT_ALLOWED', 'error');
			}
		}
		else
		{
			$view = 'Schedules';
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_FUNCTION_UNAVAILABLE', 'error');
		}
		$url .= "&view={$view}";
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
