<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

try {
    if (!JFactory::getUser()->authorise('core.manage', 'com_thm_organizer')) {
        throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
    }
    /** @noinspection PhpIncludeInspection */
    require_once JPATH_COMPONENT_ADMINISTRATOR . '/assets/helpers/thm_organizerHelper.php';
    THM_OrganizerHelper::callController();
} catch (Exception $exc) {
    JLog::add($exc->__toString(), JLog::ERROR, 'com_thm_organizer');
    throw $exc;
}
