<?php

/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewSubject_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.view');

/**
 * Retrieves a list of subjects and loads data into context.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewSubject_Manager extends THM_CoreViewList
{
    public $items;

    public $pagination;

    public $state;

    /**
     * Retrieves display items and loads them into context.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        parent::display($tpl);
    }

    /**
     * Sets Joomla view title and action buttons
     *
     * @return  void
     */
    protected function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_SUBJECT_MANAGER_VIEW_TITLE'), 'organizer_subjects');
        JToolbarHelper::addNew('subject.add');
        JToolbarHelper::editList('subject.edit');
        JToolbarHelper::custom(
            'subject.importLSFData',
            'import',
            '',
            'COM_THM_ORGANIZER_ACTION_IMPORT',
            true
        );
        JToolbarHelper::custom(
            'subject.updateAll',
            'import-all',
            '',
            'COM_THM_ORGANIZER_ACTION_IMPORT_ALL',
            false
        );
        JToolbarHelper::deleteList('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM', 'subject.delete');
        JToolbarHelper::divider();
        JToolbarHelper::preferences('com_thm_organizer');
    }
}
