<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\JSON;

use Organizer\Helpers\Instances as InstancesHelper;

/**
 * Class answers dynamic term related queries
 */
class Instances extends BaseView
{
    /**
     * loads model data into view context
     *
     * @return void
     */
    public function display()
    {
        $conditions = InstancesHelper::getConditions();
        echo json_encode(InstancesHelper::getItems($conditions));
    }
}
