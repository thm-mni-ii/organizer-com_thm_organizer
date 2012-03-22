<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        room manager view
 * @description provides a list of rooms
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @author      Markus Bader markusDOTbaderATmniDOTthmDOTde
 * @author      Daniel Kirsten danielDOTkirstenATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2012
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     2.5.0
 */
defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewroom_manager extends JView
{
    protected $pagination;
    protected $state;
    protected $subsubbar;

    function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));

        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.multiselect');

        $model = $this->getModel();
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $this->rooms 			= $this->get('Items');
        $this->pagination 		= $this->get('Pagination');
        $this->state 			= $this->get('State');
        $this->campuses 		= $model->campuses;
        $this->buildings 		= (count($model->buildings))? $this->buildings = $model->buildings : array();
        $this->categories 		= $model->categories;
        $this->descriptions 	= (count($model->descriptions)) ? $model->descriptions : array();
        
        // for sorting
        $state = $this->get('State');
        $this->orderby   = $state->get('filter_order');
        $this->direction = $state->get('filter_order_Dir');
        
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
        $title = JText::_('COM_THM_ORGANIZER').': '.JText::_('COM_THM_ORGANIZER_RMM_TITLE');
        JToolBarHelper::title( $title, 'mni' );
        JToolBarHelper::addNew('room.add');
        JToolBarHelper::editList('room.edit');
        JToolBarHelper::deleteList(JText::_( 'COM_THM_ORGANIZER_RMM_DELETE_CONFIRM'),'room.delete');

    }
}