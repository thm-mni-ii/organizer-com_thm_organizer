<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\PDF;

define('K_PATH_IMAGES', JPATH_ROOT . '/components/com_thm_organizer/images/');

use Organizer\Views\BaseView;

jimport('tcpdf.tcpdf');

/**
 * Class loads room statistic information into the display context.
 */
class Room_Statistics extends BaseView
{
    public $fields = [];

    public $date;

    public $timePeriods;

    public $terms;

    public $departments;

    public $programs;

    public $roomIDs;

    /**
     * Sets context variables and renders the view.
     *
     * @param string $tpl template
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();

        $this->model = $this->getModel();

        parent::display($tpl);
    }
}
