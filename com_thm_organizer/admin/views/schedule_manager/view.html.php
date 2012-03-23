<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        schedule manager view
 * @description provides a list of schedules
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewschedule_manager extends JView
{
    protected $pagination;
    protected $state;

    function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));

        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.multiselect');

        $model = $this->getModel();
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $this->schedules = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->semesterName = $model->semesterName;
        $this->semesters = $model->semesters;
        $this->plantypes = $model->plantypes;
        $this->addToolBar();
        if(count($this->semesters))$this->addLinks();

        parent::display($tpl);
    }

    /**
     * addLinks
     *
     * creates links to the edit view for the particular schedule
     */
    private function addLinks()
    {
        $editURL = 'index.php?option=com_thm_organizer&view=schedule_edit&scheduleID=';
        foreach($this->schedules as $key => $schedule)
            $this->schedules[$key]->url = $editURL.$schedule->id;
    }

    /**
     * addToolBar
     *
     * creates a joomla administrative tool bar
     */
    private function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER').': '.JText::_('COM_THM_ORGANIZER_SCH_TITLE');
        if($this->state->get('semesterName')) $title .= " ".$this->state->get('semesterName');
        JToolBarHelper::title( $title, 'mni' );
        JToolBarHelper::addNew('schedule.add');
        JToolBarHelper::editList('schedule.edit');
        JToolBarHelper::makeDefault('schedule.setDefault', 'COM_THM_ORGANIZER_SCH_ACTIVATE_TITLE');
        JToolBarHelper::deleteList(JText::_( 'COM_THM_ORGANIZER_SCH_DELETE_CONFIRM'),'schedule.delete');
    }
}