<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        provides functions useful to multiple component files
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
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
                $task = JFactory::getApplication()->input->getString('task', '');
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
     * Attempts to delete entries from a standard table
     * 
     * @param   string  $table  the table name
     * 
     * @return  boolean  true on success, otherwise false
     */
    public static function delete($table)
    {
        $cids = JFactory::getApplication()->input->get('cid', array(), 'array');
        $formattedIDs = "'" . implode("', '", $cids) . "'";

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->delete("#__thm_organizer_$table");
        $query->where("id IN ( $formattedIDs )");
        $dbo->setQuery($query);
        try
        {
            $dbo->execute();
        }
        catch (Exception $exception)
        {
            return false;
        }
        return true;
    }

    /**
     * Configure the Linkbar.
     *
     * @param   object  &$view  the view context calling the function
     *
     * @return void
     */
    public static function addSubmenu(&$view)
    {
        $viewName = $view->get('name');
        // No submenu creation while editing a resource
        if (!strpos($viewName, 'manager'))
        {
            return;
        }

        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_CAT_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=category_manager',
            $viewName == 'category_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_CLM_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=color_manager',
            $viewName == 'color_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_DEG_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=degree_manager',
            $viewName == 'degree_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_PRM_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=program_manager',
            $viewName == 'program_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_FLM_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=field_manager',
            $viewName == 'field_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_MON_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=monitor_manager',
            $viewName == 'monitor_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_RMM_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=room_manager',
            $viewName == 'room_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_SCH_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=schedule_manager',
            $viewName == 'schedule_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_POM_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=pool_manager',
            $viewName == 'pool_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_SUM_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=subject_manager',
            $viewName == 'subject_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_TRM_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=teacher_manager',
            $viewName == 'teacher_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_USM_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=user_manager',
            $viewName == 'user_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_VSM_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=virtual_schedule_manager',
            $viewName == 'virtual_schedule_manager'
        );

        $view->sidebar = JHtmlSidebar::render();
    }
}
