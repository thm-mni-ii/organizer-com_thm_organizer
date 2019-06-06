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

/**
 * Class retrieves dynamic program options.
 */
class Department_Ajax extends BaseModel
{
    /**
     * Gets the program options as a string
     *
     * @return string the concatenated category options
     */
    public function getOptions()
    {
        $options = Departments::getOptions();

        return json_encode($options);
    }
}
