<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewSchedule_Manager
 * @description view output file for schedule lists
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class which loads data into the view output context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewSchedule_Manager extends JViewLegacy
{
    /**
     * jpagination object holding data relevant to the number of results to be
     * displayed and query limit values
     *
     * @var JPagination
     */
    protected $pagination;

    /**
     * jstate object holding data relevant to filter information
     *
     * @var JState
     */
    protected $state;

    /**
     * loads data into view output context and initiates functions creating html
     * elements
     *
     * @param   string  $tpl  the template to be used
     *
     * @return void
     */
    public function display($tpl = null)
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.multiselect');

        $model = $this->getModel();
        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/thm_organizer.css');
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/schedule_errors.js'));

        $this->state = $this->get('State');
        $this->schedules = $this->get('Items');
        
        $this->pagination = $this->get('Pagination');
        $this->departments = $model->departments;
        $this->semesters = $model->semesters;
        $this->addToolBar();
        if (count($this->semesters))
        {
            $this->addLinks();
        }

        parent::display($tpl);
    }

    /**
     * creates links to the edit view for each individual schedule
     *
     * @return void
     */
    private function addLinks()
    {
        $editURL = 'index.php?option=com_thm_organizer&view=schedule_edit&scheduleID=';
        foreach ($this->schedules as $key => $schedule)
        {
            $this->schedules[$key]->url = $editURL . $schedule->id;
        }
    }

    /**
     * creates a joomla administrative tool bar
     *
     * @return void
     */
    private function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER') . ': ' . JText::_('COM_THM_ORGANIZER_SCH_TITLE');
        JToolbarHelper::title($title, 'organizer_schedules');
        JToolbarHelper::addNew('schedule.add');
        JToolbarHelper::editList('schedule.edit');
        JToolbarHelper::custom('schedule.mergeView', 'merge', 'merge', 'COM_THM_ORGANIZER_MERGE', true);
        JToolbarHelper::custom('schedule.activate', 'default', 'default', 'COM_THM_ORGANIZER_SCH_ACTIVATE_TITLE', true);
        JToolbarHelper::custom('schedule.setReference', 'move', 'move', 'COM_THM_ORGANIZER_SCH_REFERENCE_TITLE', true);
        JToolbarHelper::deleteList(JText::_('COM_THM_ORGANIZER_SCH_DELETE_CONFIRM'), 'schedule.delete');
        JToolbarHelper::divider();
        JToolbarHelper::preferences('com_thm_organizer');
    }
}
