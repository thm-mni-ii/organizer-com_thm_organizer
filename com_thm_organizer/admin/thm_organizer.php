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

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/OrganizerHelper.php';

if (!\JFactory::getUser()->authorise('core.manage', 'com_thm_organizer')) {
    throw new \Exception(Languages::_('THM_ORGANIZER_403'), 403);
}

try {
    OrganizerHelper::setUp();
} catch (Exception $exc) {
    throw $exc;
}
