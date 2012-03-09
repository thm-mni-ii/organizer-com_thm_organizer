<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        controller thm_organizer
 * @description the entry file for the administrative area of thm_organizer
 *              accepts the controller/task parameters and redirects to specific
 *              controllers
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2012
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     2.5
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersController extends JController
{
    public function display()
    {
        thm_organizerHelper::addSubmenu(JRequest::getCmd('view'));
        parent::display();
    }
}
