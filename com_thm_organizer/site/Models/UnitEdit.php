<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Organizer\Helpers\Units;

/**
 * Class loads a form for editing unit data.
 */
class UnitEdit extends EditModel
{
    /**
     * Method to get a single record.
     *
     * @param integer $pk The id of the primary key
     *
     * @return mixed Object on success, false on failure
     */
    public function getItem($pk = null)
    {
        $this->item          = parent::getItem($pk);
        $this->item->eventID = Units::getEventID($this->item->id);

        return $this->item;
    }
}
