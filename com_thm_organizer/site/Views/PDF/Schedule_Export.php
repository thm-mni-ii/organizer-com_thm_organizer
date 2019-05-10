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

defined('_JEXEC') or die;
define('K_PATH_IMAGES', JPATH_ROOT . '/components/com_thm_organizer/images/');

use Exception;
use Organizer\Views\BaseView;
use THM_OrganizerTemplateSchedule_Export_PDF;
jimport('tcpdf.tcpdf');

/**
 * Class creates a PDF file for the display of the filtered schedule information.
 */
class Schedule_Export extends BaseView
{
    public $document;

    /**
     * Sets context variables and renders the view.
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return void
     * @throws Exception => library missing
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function display($tpl = null)
    {
        $libraryInstalled = $this->checkLibraries();

        if (!$libraryInstalled) {
            return;
        }

        $model      = $this->getModel();
        $parameters = $model->parameters;
        $grid       = empty($model->grid) ? null : $model->grid;
        $lessons    = $model->lessons;

        $fileName = "{$parameters['documentFormat']}_{$parameters['displayFormat']}_{$parameters['pdfWeekFormat']}";
        require_once __DIR__ . "/tmpl/$fileName.php";
        new THM_OrganizerTemplateSchedule_Export_PDF($parameters, $lessons, $grid);
    }

    /**
     * Imports libraries and sets library variables
     *
     * @return bool true if the tcpdf library is installed, otherwise false
     * @throws Exception => library missing
     */
    private function checkLibraries()
    {
        $this->compiler = jimport('tcpdf.tcpdf');

        if (!$this->compiler) {
            throw new Exception(Languages::_('THM_ORGANIZER_501'), 501);
        }

        return true;
    }
}
