<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        schedule manager view
 * @description provides a list of schedules
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
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
        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.multiselect');

        $model = $this->getModel();
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $model = $this->getModel();
        $this->schedules = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->semesterName = $model->semesterName;
        $this->semesters = $model->semesters;
        $this->plantypes = $model->plantypes;
        $this->access = thm_organizerHelper::isAdmin('schedule_manager');

        $title = JText::_( 'COM_THM_ORGANIZER_SCH_TITLE' );
        $title .= ($this->state->get('semesterName'))? ": ".$this->state->get('semesterName') : '';
        JToolBarHelper::title($title);
        if(thm_organizerHelper::isAdmin('schedule_manager'))
        {
            $this->addToolBar();
            thm_organizerHelper::addSubmenu('schedule_manager');
        }
         parent::display($tpl);
    }

    private function addToolBar()
    {
        if($this->state->get('semesterName'))
        {
            JToolBarHelper::custom('schedule.upload', 'upload', 'upload', 'COM_THM_ORGANIZER_SCH_UPLOAD', false);
            JToolBarHelper::divider();
        }
        JToolBarHelper::makeDefault('schedule.setDefault', 'COM_THM_ORGANIZER_SCH_ACTIVATE_TITLE');
        JToolBarHelper::editList('schedule.edit');
        JToolBarHelper::deleteList
        (
            JText::_( 'COM_THM_ORGANIZER_SCH_DELETE_CONFIRM'),
            'schedule.delete'
        );
    }
}