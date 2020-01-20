<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Routing;

/**
 * Class receives user actions and performs access checks and redirection.
 */
class Controller extends BaseController
{
	const BACKEND = true, FRONTEND = false;

	public $clientContext;

	protected $listView = '';

	protected $resource = '';

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

		$this->clientContext = OrganizerHelper::getApplication()->isClient('administrator');
		$this->registerTask('add', 'edit');
	}

	/**
	 * Makes call to the models's save function, and redirects to the same view.
	 *
	 * @return void
	 */
	public function apply()
	{
		$modelName = "Organizer\\Models\\" . OrganizerHelper::getClass($this->resource);
		$model     = new $modelName;

		if ($resourceID = $model->save())
		{
			OrganizerHelper::message('ORGANIZER_SAVE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('ORGANIZER_SAVE_FAIL', 'error');
		}

		$url = Routing::getRedirectBase() . "&view={$this->resource}_edit&id=$resourceID";
		$this->setRedirect($url);
	}

	/**
	 * Redirects to the manager from the form.
	 *
	 * @return void
	 */
	public function cancel()
	{
		$url = Routing::getRedirectBase() . "&view={$this->listView}";
		$this->setRedirect($url);
	}

	/**
	 * Makes call to the models's delete function, and redirects to the manager view.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function delete()
	{
		$modelName = "Organizer\\Models\\" . OrganizerHelper::getClass($this->resource);
		$model     = new $modelName;

		if ($model->delete($this->resource))
		{
			OrganizerHelper::message('ORGANIZER_DELETE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('ORGANIZER_DELETE_FAIL', 'error');
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
		$document = Factory::getDocument();
		$format   = $this->input->get('format', $document->getType());
		$name     = $this->input->get('view', $this->default_view);
		$template = $this->input->get('layout', 'default', 'string');

		$view = $this->getView(
			$name,
			$format,
			'',
			array('base_path' => $this->basePath, 'layout' => $template)
		);

		// Only html views require models
		if ($format === 'html' and $model = $this->getModel($name))
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		$view->document = $document;

		try
		{
			$view->display();
		}
		catch (Exception $exception)
		{
			OrganizerHelper::message($exception->getMessage(), 'error');
			$this->setRedirect(Uri::base());
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
		$this->input->set('view', "{$this->resource}_edit");
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
			$menu = OrganizerHelper::getApplication()->getMenu();

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
				$message = sprintf(Languages::_('ORGANIZER_VIEW_NOT_FOUND'), $name, $type, $prefix);
				throw new Exception($message, 404);
			}
		}

		return self::$views[$key][$type][$prefix];
	}

	/**
	 * Performs access checks, makes call to the models's merge function, and
	 * redirects to the room manager view
	 *
	 * @return void
	 */
	public function merge()
	{
		$modelName = "Organizer\\Models\\" . OrganizerHelper::getClass($this->resource);
		$model     = new $modelName;

		if ($model->merge($this->resource))
		{
			OrganizerHelper::message('ORGANIZER_MERGE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('ORGANIZER_MERGE_FAIL', 'error');
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
			OrganizerHelper::message('ORGANIZER_DEBUG_ON', 'error');
			$this->setRedirect($url);

			return;
		}

		$selectedIDs = Input::getSelectedIDs();
		if (count($selectedIDs) == 1)
		{
			$msg = Languages::_('ORGANIZER_TOO_FEW');
			$this->setRedirect(Route::_($url, false), $msg, 'warning');

			return;
		}

		// Reliance on POST requires a different method of redirection
		$this->input->set('view', "{$this->resource}_merge");
		parent::display();
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
			OrganizerHelper::message('ORGANIZER_SAVE_FAIL', 'error');

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
			OrganizerHelper::message('ORGANIZER_SAVE_SUCCESS', 'success');

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
		$modelName = "Organizer\\Models\\" . OrganizerHelper::getClass($this->resource);
		$model     = new $modelName;

		if ($model->save2copy())
		{
			OrganizerHelper::message('ORGANIZER_SAVE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('ORGANIZER_SAVE_FAIL', 'error');
		}

		$url = Routing::getRedirectBase() . "&view={$this->listView}";
		$this->setRedirect($url);
	}

	/**
	 * Makes call to the models's save2new function, and redirects to the edit view.
	 *
	 * @return void
	 */
	public function save2new()
	{
		$modelName = "Organizer\\Models\\" . OrganizerHelper::getClass($this->resource);
		$model     = new $modelName;

		if ($model->save())
		{
			OrganizerHelper::message('ORGANIZER_SAVE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('ORGANIZER_SAVE_FAIL', 'error');
		}

		$url = Routing::getRedirectBase() . "&view={$this->resource}_edit&id=0";
		$this->setRedirect($url);
	}

	/**
	 * Toggles binary resource properties from a list view.
	 *
	 * @return void
	 */
	public function toggle()
	{
		$modelName = "Organizer\\Models\\" . OrganizerHelper::getClass($this->resource);
		$model     = new $modelName;

		if ($model->toggle())
		{
			OrganizerHelper::message('ORGANIZER_SAVE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('ORGANIZER_SAVE_FAIL', 'error');
		}

		$url = Routing::getRedirectBase() . "&view={$this->listView}";
		$this->setRedirect($url);
	}
}
