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

jimport('phpexcel.library.PHPExcel');

/**
 * Class instantiates and renders an XLS File with the department statistics.
 */
class THM_OrganizerViewDepartment_Statistics extends \Joomla\CMS\MVC\View\HtmlView
{
    /**
     * Sets context variables and renders the view.
     *
     * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        require_once __DIR__ . '/tmpl/document.php';
        $export = new THM_OrganizerTemplateDepartment_Statistics_XLS($model);
        $export->render();
        ob_flush();
    }
}
