<?php
/**
 * @version     v0.1.0
 * @category    Joomla component 
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerTableMonitors
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

// No direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.table');

/**
 * Class representing the monitors table. 
 * 
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerTableMonitors extends JTable
{
    /**
     * Constructor to call the parent constructor
     * 
     * @param   JDatabase  &$dbo  A database connector object
     */
    public function __construct(&$dbo)
    {
    	parent::__construct('#__thm_organizer_monitors', 'monitorID', $dbo);
    }
}
