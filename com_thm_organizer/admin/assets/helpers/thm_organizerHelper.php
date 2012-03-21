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
 * @version     2.5.0
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
		$submenus = array(
				'main_menu' 				=> array('name' => 'COM_THM_ORGANIZER_MAIN_TITLE', 'link' => 'index.php?option=com_thm_organizer'),
				'semester_manager' 			=> array('name' => 'COM_THM_ORGANIZER_SEM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=semester_manager'),
				'schedule_manager' 			=> array('name' => 'COM_THM_ORGANIZER_SCH_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=schedule_manager'),
				'virtual_schedule_manager' 	=> array('name' => 'COM_THM_ORGANIZER_VSM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=virtual_schedule_manager'),
				'category_manager' 			=> array('name' => 'COM_THM_ORGANIZER_CAT_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=category_manager'),
				'monitor_manager' 			=> array('name' => 'COM_THM_ORGANIZER_MON_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=monitor_manager'),
				'schedule_application_settings' => array('name' => 'COM_THM_ORGANIZER_RIA_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=scheduler_application_settings'),
				'room_manager' 				=> array('name' => 'COM_THM_ORGANIZER_RMM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=room_manager'),
				'teacher_manager' 			=> array('name' => 'COM_THM_ORGANIZER_TRM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=teacher_manager'),
				'class_manager' 			=> array('name' => 'COM_THM_ORGANIZER_CLM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=class_manager'),
				'department_manager' 		=> array('name' => 'COM_THM_ORGANIZER_DPM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=department_manager'),
				'description_manager' 		=> array('name' => 'COM_THM_ORGANIZER_DSM_TITLE', 'link' => 'index.php?option=com_thm_organizer&amp;view=description_manager')
		);

		// put submenu entries togehter
		foreach($submenus as $subKey => $subValue)
		{
			if ($subKey == $thisSubmenu OR ($thisSubmenu == "" && $subKey == 'main_menu'))  // highlight active view
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
