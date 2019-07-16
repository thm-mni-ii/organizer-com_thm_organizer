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

use Exception;
use Organizer\Helpers\Categories;

/**
 * Class loads a form for editing category data.
 */
class CategoryEdit extends EditModel
{
    protected $deptResource = 'program';

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

        return Categories::allowEdit([$this->item->id]);
    }

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
        $this->item               = parent::getItem($pk);
        $this->item->departmentID = Categories::getDepartmentIDs($this->item->id);

        return $this->item;
    }
}
