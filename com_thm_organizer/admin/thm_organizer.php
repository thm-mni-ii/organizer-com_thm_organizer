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

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/component.php';

if (!JFactory::getUser()->authorise('core.manage', 'com_thm_organizer')) {
    throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
}

try {
    THM_OrganizerHelperComponent::setUp(true);
} catch (Exception $exc) {
    throw $exc;
}
