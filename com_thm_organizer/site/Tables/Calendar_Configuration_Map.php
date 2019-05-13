<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Tables;

defined('_JEXEC') or die;

/**
 * Class instantiates a Table Object associated with the calendar_configuration_map table.
 */
class Calendar_Configuration_Map extends BaseTable
{
    /**
     * Declares the associated table
     *
     * @param \JDatabaseDriver &$dbo A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_calendar_configuration_map', 'id', $dbo);
    }
}
