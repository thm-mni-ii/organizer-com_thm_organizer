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
require_once JPATH_ROOT . '/media/com_thm_organizer/views/list.php';

/**
 * Class loads persistent information a filtered set of degree programs into the display context.
 */
class THM_OrganizerViewProgram_Manager extends THM_OrganizerViewList
{
    public $items;

    public $pagination;

    public $state;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     * @throws Exception => unauthorized access
     */
    public function display($tpl = null)
    {
        if (!THM_OrganizerHelperAccess::allowDocumentAccess()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_401'), 401);
        }

        parent::display($tpl);
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_PROGRAM_MANAGER_VIEW_TITLE'), 'organizer_degree_programs');
        JToolbarHelper::addNew('program.add');
        JToolbarHelper::editList('program.edit');
        JToolbarHelper::custom(
            'program.importLSFData',
            'import',
            '',
            'COM_THM_ORGANIZER_ACTION_IMPORT',
            true
        );
        JToolbarHelper::custom(
            'program.updateLSFData',
            'loop',
            '',
            'COM_THM_ORGANIZER_ACTION_UPDATE_SUBJECTS',
            true
        );
        JToolbarHelper::deleteList('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM', 'program.delete');

        if (THM_OrganizerHelperAccess::isAdmin()) {
            JToolbarHelper::preferences('com_thm_organizer');
        }
    }
}
