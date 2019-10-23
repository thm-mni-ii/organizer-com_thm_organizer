<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;
use Organizer\Controller;
use ReflectionMethod;
use RuntimeException;

/**
 * Class provides generalized functions useful for several component files.
 */
class OrganizerHelper
{
	/**
	 * Determines whether the view was called from a dynamic context
	 *
	 * @return bool true if the view was called dynamically, otherwise false
	 */
	public static function dynamic()
	{
		$app = self::getApplication();

		return (empty($app->getMenu()) or empty($app->getMenu()->getActive())) ? true : false;
	}

	/**
	 * Executes a database query
	 *
	 * @param   string  $function  the name of the query function to execute
	 * @param   mixed   $default   the value to return if an error occurred
	 * @param   mixed   $args      the arguments to use in the called function
	 * @param   bool    $rollback  whether to initiate a transaction rollback on error
	 *
	 * @return mixed the various return values appropriate to the functions called.
	 */
	public static function executeQuery($function, $default = null, $args = null, $rollback = false)
	{
		$dbo = Factory::getDbo();
		try
		{
			if ($args !== null)
			{
				if (is_string($args) or is_int($args))
				{
					return $dbo->$function($args);
				}
				if (is_array($args))
				{
					$reflectionMethod = new ReflectionMethod($dbo, $function);

					return $reflectionMethod->invokeArgs($dbo, $args);
				}
			}

			return $dbo->$function();
		}
		catch (RuntimeException $exc)
		{
			self::message($exc->getMessage(), 'error');
			if ($rollback)
			{
				$dbo->transactionRollback();
			}

			return $default;
		}
		catch (Exception $exc)
		{
			self::message($exc->getMessage(), 'error');
			if ($rollback)
			{
				$dbo->transactionRollback();
			}

			return $default;
		}

		return $default;
	}

	/**
	 * Surrounds the call to the application with a try catch so that not every function needs to have a throws tag. If
	 * the application has an error it would have never made it to the component in the first place.
	 *
	 * @return CMSApplication|null
	 */
	public static function getApplication()
	{
		try
		{
			return Factory::getApplication();
		}
		catch (Exception $exc)
		{
			return null;
		}
	}

	/**
	 * Gets the name of an object's class without its namespace.
	 *
	 * @param   mixed  $object  the object whose namespace free name is requested or the fq name of the class to be loaded
	 *
	 * @return string the name of the class without its namespace
	 */
	public static function getClass($object)
	{
		$fqName   = is_string($object) ? $object : get_class($object);
		$nsParts  = explode('\\', $fqName);
		$lastItem = array_pop($nsParts);
		if (empty($lastItem))
		{
			return 'Organizer';
		}
		elseif (strpos($lastItem, '_') !== false)
		{
			return str_replace('_', '', ucwords($lastItem, "_"));
		}
		else
		{
			return ucfirst($lastItem);
		}
	}

	/**
	 * Creates the plural of the given resource.
	 *
	 * @param   string  $resource  the resource for which the plural is needed
	 *
	 * @return string the plural of the resource name
	 */
	public static function getPlural($resource)
	{
		switch (true)
		{
			case $resource == 'equipment':
				return 'equipment';
			case mb_substr($resource, -1) == 's':
				return $resource . 'es';
			case mb_substr($resource, -2) == 'ry':
				return mb_substr($resource, 0, mb_strlen($resource) - 1) . 'ies';
				break;
			default:
				return $resource . 's';
		}
	}

	/**
	 * Resolves a view name to the corresponding resource.
	 *
	 * @param   string  $view  the view for which the resource is needed
	 *
	 * @return string the resource name
	 */
	public static function getResource($view)
	{
		$initial       = strtolower($view);
		$withoutSuffix = str_replace(['edit', 'item', 'lsf', 'merge', 'statistics', 'xml'], '', $initial);
		if ($withoutSuffix !== $initial)
		{
			return $withoutSuffix;
		}

		$listViews = [
			'categories'   => 'category',
			'courses'      => 'course',
			'departments'  => 'department',
			'groups'       => 'group',
			'equipment'    => 'equipment',
			'events'       => 'event',
			'participants' => 'participant',
			'pools'        => 'pool',
			'programs'     => 'program',
			'rooms'        => 'room',
			'roomtypes'    => 'roomtype',
			'schedules'    => 'schedule',
			'subjects'     => 'subject',
			'persons'      => 'person',
			'topics'       => 'topic'
		];

		return $listViews[$initial];
	}

	/**
	 * Instantiates an Organizer table with a given name
	 *
	 * @param   string  $name  the table name
	 *
	 * @return Table
	 */
	public static function getTable($name)
	{
		$fqn = "\\Organizer\\Tables\\$name";

		return new $fqn;
	}

	/**
	 * TODO: Including this (someday) to the Joomla Core!
	 * Checks if the device is a smartphone, based on the 'Mobile Detect' library
	 *
	 * @return boolean
	 */
	public static function isSmartphone()
	{
		$mobileCheckPath = JPATH_ROOT . '/components/com_jce/editor/libraries/classes/mobile.php';

		if (file_exists($mobileCheckPath))
		{
			if (!class_exists('Wf_Mobile_Detect'))
			{
				// Load mobile detect class
				require_once $mobileCheckPath;
			}

			$checker = new \Wf_Mobile_Detect;
			$isPhone = ($checker->isMobile() and !$checker->isTablet());

			if ($isPhone)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Masks the Joomla application enqueueMessage function
	 *
	 * @param   string  $message  the message to enqueue
	 * @param   string  $type     how the message is to be presented
	 *
	 * @return void
	 */
	public static function message($message, $type = 'message')
	{
		$message = Languages::_($message);
		self::getApplication()->enqueueMessage($message, $type);
	}

	/**
	 * Resolves a comma separated list of id values to an array of id values
	 *
	 * @param   string  $list  the list to be resolved
	 *
	 * @return array
	 */
	public static function resolveListIDs($list)
	{
		$idValues         = explode(',', $list);
		$cleanedIDValues  = ArrayHelper::toInteger($idValues);
		$filteredIDValues = array_filter($cleanedIDValues);

		return $filteredIDValues;
	}

	/**
	 * Instantiates the controller.
	 *
	 * @return void
	 */
	public static function setUp()
	{
		$handler = explode('.', Input::getTask());
		if (count($handler) == 2)
		{
			$task = $handler[1];
		}
		else
		{
			$task = $handler[0];
		}

		$controllerObj = new Controller;

		try
		{
			$controllerObj->execute($task);
		}
		catch (Exception $exception)
		{
			self::message($exception->getMessage(), 'error');
		}
		$controllerObj->redirect();
	}
}
