<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerTableLecturers
 * @description lecturers table class
 * @author      Markus Baier markusDOTbaierATmniDOTthmDOTde
 * @author      Wolf Rost wolfDOTrostATmniDOTthmDOTde
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.table');

/**
 * Class representing the lecturers table. 
 * 
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerTableLecturers extends JTable
{
    /**
     * Constructor function for the class representing the lecturers table
     * 
     * @param   JDatabase  &$dbo  A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_lecturers', 'id', $dbo);
    }
}
