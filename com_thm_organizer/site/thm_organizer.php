<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @description the base file for the component frontend
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;

// Include the JLog class.
jimport('joomla.log.log');

$componentName = "com_thm_organizer";

// Get the date.
$date = JFactory::getDate()->format('Y-m');
JLog::addLogger(
array(
    'text_file' => $componentName . '_site' . DIRECTORY_SEPARATOR . $componentName . '_' . $date . '.php'
    ),
    JLog::ALL & ~JLog::DEBUG,
    array($componentName)
);

try
{
    require_once JPATH_COMPONENT_ADMINISTRATOR . '/assets/helpers/thm_organizerHelper.php';
    THM_OrganizerHelper::callController(false);
}
catch (Exception $e)
{
    JLog::add($e->__toString(), JLog::ERROR, $componentName);
    throw $e;
}
