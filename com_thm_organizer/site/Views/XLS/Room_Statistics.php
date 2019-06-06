<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\XLS;

/**
 * Class instantiates and renders an XLS File with the room statistics.
 */
class Room_Statistics extends BaseXMLView
{
    /**
     * Sets context variables and renders the view.
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        require_once __DIR__ . '/tmpl/document.php';
        $export = new \THM_OrganizerTemplateRoom_Statistics_XLS($model);
        $export->render();
        ob_flush();
    }
}
