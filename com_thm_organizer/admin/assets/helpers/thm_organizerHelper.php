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
}
