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

namespace Organizer\Controllers;

use Exception;

defined('_JEXEC') or die;

/**
 * Class receives user actions and performs access checks and redirection.
 */
class Controller extends \Joomla\CMS\MVC\Controller\BaseController
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param string $name   The model name. Optional.
     * @param string $prefix The class prefix. Optional.
     * @param array  $config Configuration array for model. Optional.
     *
     * @return  object|boolean  Model object on success; otherwise false on failure.
     * @throws Exception
     */
    public function getModel($name = '', $prefix = '', $config = array())
    {
        $name = empty($name) ? $this->getName() : $name;

        if (empty($name)) {
            return false;
        }

        $modelName = "Organizer\\Models\\" . \OrganizerHelper::getClass($name);

        if ($model = new $modelName($config)) {
            // Task is a reserved state
            $model->setState('task', $this->task);

            // Let's get the application object and set menu information if it's available
            $menu = \JFactory::getApplication()->getMenu();

            if (is_object($menu) && $item = $menu->getActive()) {
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
     * @param string $name   The view name. Optional, defaults to the controller name.
     * @param string $type   The view type. Optional.
     * @param string $prefix The class prefix. Optional.
     * @param array  $config Configuration array for view. Optional.
     *
     * @return  \JViewLegacy  Reference to the view or an error.
     *
     * @throws  Exception
     * @since   3.0
     */
    public function getView($name = '', $type = '', $prefix = '', $config = array())
    {
        // @note We use self so we only access stuff in this class rather than in all classes.
        if (!isset(self::$views)) {
            self::$views = array();
        }

        if (empty($name)) {
            $name = $this->getName();
        }

        $viewName = \OrganizerHelper::getClass($name);
        $type     = strtoupper(preg_replace('/[^A-Z0-9_]/i', '', $type));
        $name     = "Organizer\\Views\\$type\\$viewName";

        $config['base_path']   = JPATH_COMPONENT_SITE . "/Views/$type";
        $config['helper_path'] = JPATH_COMPONENT_SITE . "/Helpers";
        $config['template_path'] = JPATH_COMPONENT_SITE . "/Layouts/$type";

        $key = strtolower($viewName);
        if (empty(self::$views[$key][$type][$prefix])) {
            if ($view = new $name($config)) {
                self::$views[$key][$type][$prefix] = &$view;
            } else {
                throw new Exception(\JText::sprintf('JLIB_APPLICATION_ERROR_VIEW_NOT_FOUND', $name, $type, $prefix),
                    404);
            }
        }

        return self::$views[$key][$type][$prefix];
    }
}
