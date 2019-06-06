<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Organizer\Helpers\Access;

/**
 * Class loads a form for editing (degree) program data.
 */
class Program_Edit extends EditModel
{
    public $children = null;

    /**
     * Checks for user authorization to access the view.
     *
     * @return bool  true if the user can access the edit view, otherwise false
     */
    public function allowEdit()
    {
        $programID = (isset($this->item->id) and !empty($this->item->id)) ? $this->item->id : 0;
        if (empty($programID) or !Access::checkAssetInitialization('program', $programID)) {
            return Access::allowDocumentAccess();
        }

        return Access::allowDocumentAccess('program', $programID);
    }
}
