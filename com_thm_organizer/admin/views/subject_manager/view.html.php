<?php

/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewSubject_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/**
 * Retrieves a list of subjects and loads data into context.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewSubject_Manager extends JViewLegacy
{
    /**
     * Retrieves display items and loads them into context.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.tooltip');
        JHTML::_('behavior.modal', 'a.modal-button');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/thm_organizer.css');

        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $model = $this->getModel();

        $this->programSelect = $this->getProgramSelect($model->programs);
        $programID = $this->state->get('filter.program');
        if (!empty($programID) and !empty($model->pools))
        {
            $this->poolSelect = $this->getPoolSelect($model->pools);
        }

        $this->addToolBar();
        parent::display($tpl);

    }

    /**
     * Sets Joomla view title and action buttons
     *
     * @return  void
     */
    public function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_SUM_TOOLBAR_TITLE'), 'organizer_subjects');
        JToolbarHelper::addNew('subject.edit');
        JToolbarHelper::editList('subject.edit');
        JToolbarHelper::custom(
                               'subject.importLSFData',
                               'export',
                               '',
                               'COM_THM_ORGANIZER_PRM_IMPORT',
                               true,
                               true
                              );
        JToolbarHelper::deleteList('COM_THM_ORGANIZER_SUM_DELETE_CONFIRM', 'subject.delete');
    }

    /**
     * Retrieves a select box with the mapped programs
     *
     * @param   array  $programs  the mapped programs
     *
     * @return  string  html select box
     */
    private function getProgramSelect($programs)
    {
        $selectPrograms = array();
        $selectPrograms[] = array('id' => '-1', 'name' => JText::_('COM_THM_ORGANIZER_SEARCH_PROGRAM'));
        $selectPrograms[] = array('id' => '-1', 'name' => JText::_('COM_THM_ORGANIZER_ALL_PROGRAMS'));
        $selectPrograms[] = array('id' => '-2', 'name' => JText::_('COM_THM_ORGANIZER_NO_PROGRAMS'));
        $programs = array_merge($selectPrograms, $programs);
        $programSelect = JHTML::_('select.genericlist', $programs, 'filter_program',
                                  'onchange="this.form.submit();"', 'id', 'name',
                                  $this->state->get('filter.program')
                                 );
        return $programSelect;
    }

    /**
     * Retrieves a select box with the mapped programs
     *
     * @param   array  $pools  the mapped pools
     *
     * @return  string  html select box
     */
    private function getPoolSelect($pools)
    {
        $selectPools = array();
        $selectPools[] = array('id' => '-1', 'name' => JText::_('COM_THM_ORGANIZER_SUM_SEARCH_POOLS'));
        $selectPools[] = array('id' => '-1', 'name' => JText::_('COM_THM_ORGANIZER_SUM_ALL_POOLS'));
        $selectPools[] = array('id' => '-2', 'name' => JText::_('COM_THM_ORGANIZER_SUM_NO_POOLS'));
        $pools = array_merge($selectPools, $pools);
        $poolSelect = JHTML::_('select.genericlist', $pools, 'filter_pool',
                               'onchange="this.form.submit();"', 'id', 'name',
                               $this->state->get('filter.pool')
                              );
        return $poolSelect;
    }
}
