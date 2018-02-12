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
jimport('joomla.database.table');

/**
 * Class instantiates a JTable Object associated with the calendar_configuration_map table.
 */
class THM_OrganizerTableCalendar_Configuration_Map extends JTable
{
    /**
     * Declares the associated table
     *
     * @param JDatabaseDriver &$dbo A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_calendar_configuration_map', 'id', $dbo);
    }
}
