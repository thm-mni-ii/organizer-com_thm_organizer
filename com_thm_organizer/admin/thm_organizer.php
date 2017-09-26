<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @description the base file for the component backend
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

try
{
	if (!JFactory::getUser()->authorise('core.manage', 'com_thm_organizer'))
	{
		throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
	}
	/** @noinspection PhpIncludeInspection */
	require_once JPATH_COMPONENT_ADMINISTRATOR . '/assets/helpers/thm_organizerHelper.php';
	THM_OrganizerHelper::callController();
}
catch (Exception $exc)
{
	JLog::add($exc->__toString(), JLog::ERROR, 'com_thm_organizer');
	throw $exc;
}
