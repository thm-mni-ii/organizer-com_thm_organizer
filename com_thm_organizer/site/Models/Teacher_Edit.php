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

use Exception;
use Organizer\Helpers\Access;
use Organizer\Helpers\Teachers;

/**
 * Class loads a form for editing teacher data.
 */
class Teacher_Edit extends EditModel
{
    protected $deptResource = 'teacher';

    /**
     * Checks for user authorization to access the view.
     *
     * @return bool  true if the user can access the view, otherwise false
     */
    protected function allowEdit()
    {
        return Access::allowHRAccess();
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
        $this->item->departmentID = Teachers::getDepartmentIDs($this->item->id);

        return $this->item;
    }
}
