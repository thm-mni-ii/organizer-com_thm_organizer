<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @description the base file for the component frontend
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$helperPath = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_thm_organizer';
$helperPath .= DS . 'assets' . DS . 'helpers' . DS . 'thm_organizerHelper.php';
require_once $helperPath;
THM_OrganizerHelper::callController(false);

