<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        helper submenuhelper
 * @description creates the links to other submenus,and gets the available actions
 *              for the user for submenus
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
class thm_organizerHelper
{

    /**
     * Gets a list of the actions that can be performed.
     *
     * @param	string $submenu	The extension.
     * @param	int $categoryId	The category ID.
     *
     * @return	JObject
     * @since	1.6
     */
    public static function isAdmin($submenu)
    {
        $user = JFactory::getUser();
        $assetName = "com_thm_organizer.$submenu";
        return $user->authorise('core.admin', $assetName);
    }

    /**
     * Configure the Linkbar.
     */
    public static function addSubmenu($thisSubmenu)
    {
        $possibleSubmenus = array(
            'main_menu' => array('name' => 'COM_THM_ORGANIZER_MAIN_TITLE', 'link' => 'index.php?option=com_thm_organizer'),
            'category_manager' => array('name' => 'COM_THM_ORGANIZER_CAT_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=category_manager'),
            'monitor_manager' => array('name' => 'COM_THM_ORGANIZER_MON_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=monitor_manager'),
            'semester_manager' => array('name' => 'COM_THM_ORGANIZER_SEM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=semester_manager'),
            'schedule_manager' => array('name' => 'COM_THM_ORGANIZER_SCH_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=schedule_manager'),
            'schedule_application_settings' => array('name' => 'COM_THM_ORGANIZER_RIA_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=scheduler_application_settings'),
            'virtual_schedule_manager' => array('name' => 'COM_THM_ORGANIZER_VSM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=virtual_schedule_manager')
        );
        foreach($possibleSubmenus as $subKey => $subValue)
        {
            if($subKey == $thisSubmenu) continue;
            JSubMenuHelper::addEntry(JText::_($subValue['name']), $subValue['link']);
        }
    }

    /**
     * noAccess
     *
     * issues a generic warning when unauthorized function calls are performed
     */
    public static function noAccess()
    {
        JError::raiseError( 777, JText::_('COM_THM_ORGANIZER_NO_ACCESS') );
    }
}
?>
