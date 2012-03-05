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

class thm_organizersViewroom_manager extends JView
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

        $this->rooms = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->institutions = $model->institutions;
        $this->campuses = (count($model->campuses))? $this->campuses = $model->campuses : array();
        $this->buildings = (count($model->buildings))? $this->buildings = $model->buildings : array();
        $this->types = $model->types;
        $this->details = (count($model->details))? $model->details : array();
        $this->addToolBar();
        if(count($this->rooms))$this->addLinks();

        parent::display($tpl);
    }

    /**
     * addLinks
     *
     * creates links to the edit view for the particular schedule
     */
    private function addLinks()
    {
        $editURL = 'index.php?option=com_thm_organizer&view=room_edit&roomID=';
        foreach($this->rooms as $key => $room)
            $this->rooms[$key]->url = $editURL.$room->id;
    }

    /**
     * addToolBar
     *
     * creates a joomla administrative tool bar
     */
    private function addToolBar()
    {
        $title = JText::_( 'COM_THM_ORGANIZER_RMM_TITLE' );
        $title .= ($this->state->get('semesterName'))? ": ".$this->state->get('semesterName') : '';
        JToolBarHelper::title($title);
        JToolBarHelper::addNew('room.add');
        JToolBarHelper::editList('room.edit');
        JToolBarHelper::deleteList(JText::_( 'COM_THM_ORGANIZER_RMM_DELETE_CONFIRM'),'schedule.delete');
    }
}