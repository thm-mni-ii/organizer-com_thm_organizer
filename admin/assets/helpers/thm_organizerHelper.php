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
    public static function getActions($submenu, $assetID = 0)
    {
        $user = JFactory::getUser();
        $result = new JObject;
        $component = 'com_thm_organizer';

        if(empty ($categoryID)) $assetName = $component;
        else $assetName = $component.".$submenu.".(int) $assetID;

        $actions = array( 'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete' );

        foreach ($actions as $action) {
                $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }

    /**
     * Configure the Linkbar.
     */
    public static function addSubmenu($thisSubmenu)
    {
        $possibleSubmenus = array(
            'main_menu' => array('name' => 'Main Menu', 'link' => 'index.php?option=com_thm_organizer'),
            'category_manager' => array('name' => 'Category Manager', 'link' => 'index.php?option=com_thm_organizer&amp;view=category_manager'),
            'monitor_manager' => array('name' => 'Monitor Manager', 'link' => 'index.php?option=com_thm_organizer&amp;view=monitor_manager'),
            'semester_manager' => array('name' => 'Semester Manager', 'link' => 'index.php?option=com_thm_organizer&amp;view=semester_manager'),
            'schedule_application_settings' => array('name' => 'Scheduler Application Settings', 'link' => 'index.php?option=com_thm_organizer&amp;view=scheduler_application_settings'),
            'virtual_schedule_manager' => array('name' => 'Virtual Schedule Manager', 'link' => 'index.php?option=com_thm_organizer&amp;view=virtual_schedule_manager')
        );
        foreach($possibleSubmenus as $subKey => $subValue)
        {
            if($subKey == $thisSubmenu) continue;
            JSubMenuHelper::addEntry(JText::_($subValue['name']), $subValue['link']);
        }
    }

}
?>
