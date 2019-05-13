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

defined('_JEXEC') or die;

use Organizer\Helpers\Plan_Pools;

/**
 * Class loads a form for editing plan (subject) pool data.
 */
class Plan_Pool_Edit extends EditModel
{
    /**
     * Checks for user authorization to access the view.
     *
     * @return bool  true if the user can access the edit view, otherwise false
     */
    public function allowEdit()
    {
        if (empty($this->item->id)) {
            return false;
        }

        return Plan_Pools::allowEdit([$this->item->id]);
    }
}