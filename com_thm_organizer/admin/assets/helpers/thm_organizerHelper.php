<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        provides functions useful to multiple component files
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

/**
 * Class providing functions usefull to multiple component files
 *
 * @category  Joomla.Component.Admin
 * @package   thm_organizer
 */
class THM_OrganizerHelper
{
    /**
     * Calls the appropriate controller
     * 
     * @param   boolean  $isAdmin  whether the file is being called from the backend
     * 
     * @return  void
     */
    public static function callController($isAdmin = true)
    {
        $basePath = $isAdmin? JPATH_COMPONENT_ADMINISTRATOR : JPATH_COMPONENT_SITE;
        
        $controller = "";
        $handler = explode(".", JFactory::getApplication()->input->getCmd('task', ''));
        if (!empty($handler))
        {
            if (count($handler) == 2)
            {
                list($controller, $task) = $handler;
            }
            else
            {
                $task = JRequest::getVar('task');
            }
        }

        if (!empty($controller))
        {
            $path = $basePath . '/controllers/' . $controller . '.php';
            if (file_exists($path))
            {
                require_once $path;
            }
            else
            {
                require_once $basePath . '/controller.php';
                $controller = '';
            }
        }
        else
        {
            require_once $basePath . '/controller.php';
        }
        $classname = 'THM_OrganizerController' . $controller;
        $controllerObj = new $classname;
        $controllerObj->execute($task);
        $controllerObj->redirect();
    }

    /**
     * Configure the Linkbar.
     *
     * @param   string  $thisSubmenu  the name of the submenu calling the function
     *
     * @return void
     */
    public static function addSubmenu($thisSubmenu)
    {
        // No submenu creation while editing a resource
        if (strpos($thisSubmenu, 'edit'))
        {
                return;
        }

        // All submenu entries
        $submenus = array(
            'main_menu' => array('name' => 'COM_THM_ORGANIZER_MAIN_TITLE',
                                 'link' => 'index.php?option=com_thm_organizer'),
            'category_manager' => array('name' => 'COM_THM_ORGANIZER_CAT_TITLE',
                                        'link' => 'index.php?option=com_thm_organizer&amp;view=category_manager'),
            'schedule_manager' => array('name' => 'COM_THM_ORGANIZER_SCH_TITLE',
                                        'link' => 'index.php?option=com_thm_organizer&amp;view=schedule_manager'),
            'virtual_schedule_manager' => array('name' => 'COM_THM_ORGANIZER_VSM_TITLE',
                                                'link' => 'index.php?option=com_thm_organizer&amp;view=virtual_schedule_manager'),
            'degree_manager' => array('name' => 'COM_THM_ORGANIZER_DEG_TITLE',
                                      'link' => 'index.php?option=com_thm_organizer&view=degree_manager'),
            'color_manager' => array('name' => 'COM_THM_ORGANIZER_CLM_TITLE',
                                     'link' => 'index.php?option=com_thm_organizer&view=color_manager'),
            'field_manager' => array('name' => 'COM_THM_ORGANIZER_FLM_TITLE',
                                     'link' => 'index.php?option=com_thm_organizer&view=field_manager'),
            'program_manager' => array('name' => 'COM_THM_ORGANIZER_PRM_TITLE',
                                              'link' => 'index.php?option=com_thm_organizer&view=program_manager'),
            'pool_manager' => array('name' => 'COM_THM_ORGANIZER_POM_TITLE',
                                      'link' => 'index.php?option=com_thm_organizer&view=pool_manager'),
            'subject_manager' => array('name' => 'COM_THM_ORGANIZER_SUM_TITLE',
                                      'link' => 'index.php?option=com_thm_organizer&view=subject_manager'),
            'teacher_manager' => array('name' => 'COM_THM_ORGANIZER_TRM_TITLE',
                                       'link' => 'index.php?option=com_thm_organizer&view=teacher_manager'),
            'room_manager' => array('name' => 'COM_THM_ORGANIZER_RMM_TITLE',
                                    'link' => 'index.php?option=com_thm_organizer&view=room_manager'),
            'monitor_manager' => array('name' => 'COM_THM_ORGANIZER_MON_TITLE',
                                       'link' => 'index.php?option=com_thm_organizer&amp;view=monitor_manager')
        );

        // Put submenu entries togehter
        foreach ($submenus as $subKey => $subValue)
        {
            // Highlight the active view
            if ($subKey == $thisSubmenu OR ($thisSubmenu == "" && $subKey == 'main_menu'))
            {
                JSubMenuHelper::addEntry(JText::_($subValue['name']), $subValue['link'], true);
            }
            else
            {
                JSubMenuHelper::addEntry(JText::_($subValue['name']), $subValue['link'], false);
            }
        }
    }

    /**
     * Attempts to delete entries from a standard table
     * 
     * @param   string  $table  the table name
     * 
     * @return  boolean  true on success, otherwise false
     */
    public static function delete($table)
    {
        $cids = "'" . implode("', '", JRequest::getVar('cid', array(), 'post', 'array')) . "'";

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->delete("#__thm_organizer_$table");
        $query->where("id IN ( $cids )");
        $dbo->setQuery($query);
        try
        {
            $dbo->query();
        }
        catch (Exception $exception)
        {
            return false;
        }
        return true;
    }

    /**
     * Provides a generic populate state function using typical list fields
     * 
     * @param   object  &$model  the model whose state needs to be set
     * 
     * @return  void
     */
    public static function populateState(&$model)
    {
        $app = JFactory::getApplication('administrator');
        $context = $model->get('context');

        $orderBy = $app->getUserStateFromRequest($context . '.filter_order', 'filter_order', '');
        $model->setState('list.ordering', $orderBy);

        $orderDir = $app->getUserStateFromRequest($context . '.filter_order_Dir', 'filter_order_Dir', '');
        $model->setState('list.direction', $orderDir);

        $filter = $app->getUserStateFromRequest($context . '.filter', 'filter', '');
        $model->setState('filter', $filter);

        $limit = $app->getUserStateFromRequest($context . '.limit', 'limit', '');
        $model->setState('limit', $limit);
    }
}
