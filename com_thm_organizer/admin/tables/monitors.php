<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        monitors table class
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.table');
/**
 * Class representing the monitors table. 
 * 
 * @package  Admin
 * 
 * @since    2.5.4
 */
class THM_OrganizerTablemonitors extends JTable
{
    /**
     * Constructor function for the class representing the monitors table
     * 
     * @param   JDatabase  &$dbo  A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_monitors', 'id', $dbo);
    }
}
