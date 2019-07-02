<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class loads a form for editing participant data.
 */
class Participant_Edit extends EditModel
{
    /**
     * Method to get a single record.
     *
     * @param integer $pk The id of the primary key.
     *
     * @return mixed    Object on success, false on failure.
     * @throws Exception => unauthorized access
     */
    public function getItem($pk = null)
    {
        $item           = parent::getItem($pk);
        $item->tag      = Languages::getTag();
        $item->lessonID = OrganizerHelper::getInput()->getInt('lessonID');

        return $item;
    }
}
