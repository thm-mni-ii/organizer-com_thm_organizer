<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        helper submenuhelper
 * @description provides functions useful to multiple files
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
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
    	// no submenu in edit mode
        if(strpos($thisSubmenu, 'edit'))
        	return;
        
        // all submenu entries
        $possibleSubmenus = array(
            'main_menu' 				=> array('name' => 'COM_THM_ORGANIZER_MAIN_TITLE', 'link' => 'index.php?option=com_thm_organizer'),
            'semester_manager' 			=> array('name' => 'COM_THM_ORGANIZER_SEM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=semester_manager'),
            'schedule_manager' 			=> array('name' => 'COM_THM_ORGANIZER_SCH_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=schedule_manager'),
            'virtual_schedule_manager' 	=> array('name' => 'COM_THM_ORGANIZER_VSM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=virtual_schedule_manager'),
            'resource_manager' 			=> array('name' => 'COM_THM_ORGANIZER_RES_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=resource_manager'),
            'category_manager' 			=> array('name' => 'COM_THM_ORGANIZER_CAT_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=category_manager'),
            'monitor_manager' 			=> array('name' => 'COM_THM_ORGANIZER_MON_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=monitor_manager'),
            'schedule_application_settings' => array('name' => 'COM_THM_ORGANIZER_RIA_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=scheduler_application_settings')
        );
        
        // all subsubmenu entries for resource manager
        $resourceSubmenus = array(
            'class_manager' 		=> array('name' => 'COM_THM_ORGANIZER_CLM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=class_manager'),
            'teacher_manager' 		=> array('name' => 'COM_THM_ORGANIZER_TRM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=teacher_manager'),
            'room_manager' 			=> array('name' => 'COM_THM_ORGANIZER_RMM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=room_manager'),
            'description_manager' 	=> array('name' => 'COM_THM_ORGANIZER_DSM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=description_manager'),
            'department_manager' 	=> array('name' => 'COM_THM_ORGANIZER_DPM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=department_manager'),
            'period_manager' 		=> array('name' => 'COM_THM_ORGANIZER_TPM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=period_manager'),
            'subject_manager' 		=> array('name' => 'COM_THM_ORGANIZER_SUM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=subject_manager')
        );
        
        // put submenu entries togehter, leave current one out
        foreach($possibleSubmenus as $subKey => $subValue)
        {
            if ($subKey == $thisSubmenu
               OR ($subKey == 'resource_manager' AND key_exists($thisSubmenu, $resourceSubmenus))
               OR $thisSubmenu == "") 
            continue;  // skip current
            
            JSubMenuHelper::addEntry(JText::_($subValue['name']), $subValue['link']);
        }
        
        // put subsubmenu entries together, leave current one out
        if($thisSubmenu == 'resource_manager' OR key_exists($thisSubmenu, $resourceSubmenus))
        {
            $resourcemenu = JToolBar::getInstance('subsubmenu');

            foreach($resourceSubmenus as $resourceKey => $resourceSubmenu)
            {
                if ($resourceKey == $thisSubmenu) continue;  // skip current
                
                $resourcemenu->appendButton(JText::_($resourceSubmenu['name']), $resourceSubmenu['link'], false);
            }
            
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

    /**
     * germanizeDate
     *
     * reformats db formatted dates to the german date format
     *
     * @param string $date the date from the db
     * @return string a german formatted date
     */
    public static function germanizeDate($date)
    {
        $date = date("d.m.Y", strtotime($date));
        return $date;
    }
    /**
     * dbizeDate
     *
     * reformats german formatted dates to the db date format
     *
     * @param string $date the german formatted date
     * @return string a db formatted date
     */
    public static function dbizeDate($date)
    {
        $date = date("Y-m-d", strtotime($date));
        return $date;
    }
}
?>
