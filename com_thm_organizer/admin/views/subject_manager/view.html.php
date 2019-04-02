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

require_once JPATH_COMPONENT . '/views/list.php';

/**
 * Class loads persistent information a filtered set of subjects into the display context.
 */
class THM_OrganizerViewSubject_Manager extends THM_OrganizerViewList
{
    public $items;

    public $pagination;

    public $state;

    /**
     * Retrieves display items and loads them into context.
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return void
     * @throws Exception => unauthorized access
     */
    public function display($tpl = null)
    {
        if (!THM_OrganizerHelperAccess::allowDocumentAccess()) {
            throw new \Exception(\JText::_('COM_THM_ORGANIZER_401'), 401);
        }

        parent::display($tpl);
    }

    /**
     * Sets Joomla view title and action buttons
     *
     * @return void
     */
    protected function addToolBar()
    {
        \JToolbarHelper::title(\JText::_('COM_THM_ORGANIZER_SUBJECT_MANAGER_VIEW_TITLE'), 'organizer_subjects');
        \JToolbarHelper::addNew('subject.add');
        \JToolbarHelper::editList('subject.edit');
        \JToolbarHelper::custom(
            'subject.importLSFData',
            'upload',
            'upload',
            'COM_THM_ORGANIZER_ACTION_IMPORT',
            true
        );
        \JToolbarHelper::deleteList('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM', 'subject.delete');

        if (THM_OrganizerHelperAccess::isAdmin()) {
            \JToolbarHelper::preferences('com_thm_organizer');
        }
    }
}
