<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/component.php';
/** @noinspection PhpIncludeInspection */
jimport('phpexcel.library.PHPExcel');

/**
 * Class creates a XLS file for the display of the filtered schedule information.
 */
class THM_OrganizerViewSchedule_Export extends JViewLegacy
{
    private $parameters;

    private $lessons;

    /**
     * Sets context variables and renders the view.
     *
     * @param string $tpl template
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $model      = $this->getModel();
        $parameters = $model->parameters;

        $fileName = $parameters['documentFormat'] . '_' . $parameters['xlsWeekFormat'];
        require_once __DIR__ . "/tmpl/$fileName.php";
        $export = new THM_OrganizerTemplateExport_XLS($parameters, $model->lessons);
        $export->render();
        ob_flush();
    }
}
