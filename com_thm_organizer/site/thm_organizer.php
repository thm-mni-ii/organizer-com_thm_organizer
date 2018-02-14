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
    require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/component.php';
    THM_OrganizerHelperComponent::callController(false);
} catch (Exception $exc) {
    JLog::add($exc->__toString(), JLog::ERROR, "com_thm_organizer");
    throw $exc;
}
