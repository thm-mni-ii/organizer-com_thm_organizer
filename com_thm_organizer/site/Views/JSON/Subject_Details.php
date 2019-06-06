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

use Organizer\Models\Subject_Details as Subject_DetailsModel;

/**
 * Class loads the subject into the display context.
 */
class Subject_Details extends BaseView
{
    /**
     * loads model data into view context
     *
     * @return void
     */
    public function display()
    {
        $model = new Subject_DetailsModel;
        echo json_encode($model->get('Item'));
    }
}
