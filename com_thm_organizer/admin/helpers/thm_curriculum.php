<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerHelper
 * @description THM_OrganizerHelper component admin helper
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Class THM_CurriculumHelper for component com_thm_organizer
 *
 * Class provides methods to build the submenu and check user authorisation
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerHelper
{
	/**
	 * Method to configure the linkbab
	 *
	 * @param   String  $vName  The name of the active view
	 *
	 * @return  void
	 */
	public static function addSubmenu($vName)
	{

		JSubMenuHelper::addEntry(
				JText::_('COM_THM_ORGANIZER_SUBMENU_SEMESTERS'), 'index.php?option=com_thm_organizer&view=semesters', $vName == 'semesters'
		);

		JSubMenuHelper::addEntry(
				JText::_('COM_THM_ORGANIZER_SUBMENU_LECTURERS'), 'index.php?option=com_thm_organizer&view=lecturers', $vName == 'lecturers'
		);

		JSubMenuHelper::addEntry(
				JText::_('COM_THM_ORGANIZER_SUBMENU_ASSETS'), 'index.php?option=com_thm_organizer&view=assets', $vName == 'assets'
		);

		JSubMenuHelper::addEntry(
				JText::_('COM_THM_ORGANIZER_SUBMENU_COLORS'), 'index.php?option=com_thm_organizer&view=colors', $vName == 'colors'
		);

		JSubMenuHelper::addEntry(
				JText::_('COM_THM_ORGANIZER_SUBMENU_DEGREES'), 'index.php?option=com_thm_organizer&view=degrees', $vName == 'degrees'
		);

		JSubMenuHelper::addEntry(
				JText::_('COM_THM_ORGANIZER_SUBMENU_MAJORS'), 'index.php?option=com_thm_organizer&view=majors', $vName == 'majors'
		);
	}

	/**
	 * Method to configure the linkbab
	 *
	 * @param   Integer  $messageId  The message id  (default: 0)
	 *
	 * @return  JObject
	 */
	public static function getActions($messageId = 0)
	{
		$user = JFactory::getUser();
		$result = new JObject;

		if (empty($messageId))
		{
			$assetName = 'com_thm_organizer';
		}
		else
		{
			$assetName = 'com_thm_organizer.message.' . (int) $messageId;
		}
		$actions = array('core.admin', 'core.manage', 'core.create', 'core.edit', 'core.delete');

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}
		return $result;
	}
}
