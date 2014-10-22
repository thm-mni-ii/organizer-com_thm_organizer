<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewProgram_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.view');

/**
 * Class THM_OrganizerViewProgram_Manager for component com_thm_organizer
 * Class provides methods to display the view degree program manager
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewProgram_Manager extends THM_CoreViewList
{
    public $items;

    public $pagination;

    public $state;

    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (default: null)
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        parent::display($tpl);
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return  void
     */
    protected function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_PROGRAM_MANAGER_VIEW_TITLE'), 'organizer_degree_programs');
        JToolbarHelper::addNew('program.add');
        JToolbarHelper::editList('program.edit');
        JToolbarHelper::custom(
                               'program.importLSFData',
                               'export',
                               '',
                               'COM_THM_ORGANIZER_ACTION_IMPORT',
                               true
                              );
        JToolbarHelper::deleteList('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM', 'program.delete');
        JToolbarHelper::divider();
        JToolbarHelper::preferences('com_thm_organizer', '500');
    }
}
