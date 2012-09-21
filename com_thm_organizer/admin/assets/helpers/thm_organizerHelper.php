<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        provides functions useful to multiple component files
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 *@author      Wolf Rost wolfDOTrostATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
/**
 * Class providing functions usefull to multiple component files
 * 
 * @package  Admin
 * 
 * @since    2.5.4 
 */
class thm_organizerHelper
{

    /**
     * Gets a list of the actions that can be performed.
     *
     * @param   string  $submenu  The extension.
     *
     * @return	JObject
     */
    public static function isAdmin($submenu)
    {
            $user = JFactory::getUser();
            $assetName = "com_thm_organizer.$submenu";
            return $user->authorise('core.admin', $assetName);
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
            'main_menu' => array('name' => 'COM_THM_ORGANIZER_MAIN_TITLE', 'link' => 'index.php?option=com_thm_organizer'),
            'category_manager' => array('name' => 'COM_THM_ORGANIZER_CAT_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=category_manager'),
            'schedule_manager' => array('name' => 'COM_THM_ORGANIZER_SCH_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=schedule_manager'),
            'virtual_schedule_manager' 	=> array('name' => 'COM_THM_ORGANIZER_VSM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=virtual_schedule_manager'),
            'monitor_manager' => array('name' => 'COM_THM_ORGANIZER_MON_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=monitor_manager'),
            'semesters' => array('name' => 'com_thm_organizer_SUBMENU_SEMESTERS', 'link' => 'index.php?option=com_thm_organizer&view=semesters'),
            'lecturers' => array('name' => 'com_thm_organizer_SUBMENU_LECTURERS', 'link' => 'index.php?option=com_thm_organizer&view=lecturers'),
            'assets' => array('name' => 'com_thm_organizer_SUBMENU_ASSETS', 'link' => 'index.php?option=com_thm_organizer&view=assets'),
            'colors' => array('name' => 'com_thm_organizer_SUBMENU_COLORS', 'link' => 'index.php?option=com_thm_organizer&view=colors'),
            'degrees' => array('name' => 'com_thm_organizer_SUBMENU_DEGREES', 'link' => 'index.php?option=com_thm_organizer&view=degrees'),
            'majors' => array('name' => 'com_thm_organizer_SUBMENU_MAJORS', 'link' => 'index.php?option=com_thm_organizer&view=majors')
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
     * reformats db formatted dates to the german date format
     *
     * @param   string  $date  the date from the db
     * 
     * @return string a german formatted date
     */
    public static function germanizeDate($date)
    {
        $date = date("d.m.Y", strtotime($date));
        return $date;
    }

    /**
     * reformats german formatted dates to the db date format
     *
     * @param   string  $date  the german formatted date
     * 
     * @return string a db formatted date
     */
    public static function dbizeDate($date)
    {
        $date = date("Y-m-d", strtotime($date));
        return $date;
    }
}
?>
