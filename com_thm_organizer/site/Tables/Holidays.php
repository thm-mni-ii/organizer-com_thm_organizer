<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Tables;

use Organizer\Helpers\OrganizerHelper;

defined('_JEXEC') or die;

/**
 * Class instantiates a Table Object associated with the holidays table.
 */
class Holidays extends BaseTable
{
    /**
     * Declares the associated table
     *
     * @param \JDatabaseDriver &$dbo A database connector object
     */

    public function __construct(&$dbo = null)
    {
        parent::__construct('#__thm_organizer_holidays', 'id', $dbo);
    }

    /**
     * Checks the start date and end date
     *
     * @return boolean true on success, otherwise false
     */

    public function check()
    {
        if ($this->endDate < $this->startDate) {
            OrganizerHelper::message('THM_ORGANIZER_DATE_CHECK', 'error');

            return false;
        }

        return true;
    }
}