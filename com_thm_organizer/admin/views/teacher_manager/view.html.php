<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        teacher manager view
 * @description provides a list of teachers
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
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

class thm_organizersViewteacher_manager extends JView
{
    protected $pagination;
    protected $state;
    protected $subsubbar;
    
    // variable for filters
    protected $departments;
    protected $campuses;
    protected $institutions;

    function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));

        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.multiselect');

        $model = $this->getModel();
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $this->teachers 	= $this->get('Items');
        $this->pagination 	= $this->get('Pagination');
        $this->state 		= $this->get('State');
        $this->departments 	= $model->departments;
        $this->campuses		= $model->campuses;
        $this->institutions	= $model->institutions;
        
        // for sorting
        $state = $this->get('State');
        $this->orderby   = $state->get('filter_order');
        $this->direction = $state->get('filter_order_Dir');
        
        $this->addToolBar();
        if (count($this->teachers)) $this->addLinks();

        parent::display($tpl);
    }

    /**
     * addLinks
     *
     * creates links to the edit view for the particular schedule
     */
    private function addLinks()
    {
        $editURL = 'index.php?option=com_thm_organizer&view=teacher_edit&teacherID=';
        foreach($this->teachers as $key => $room)
            $this->teachers[$key]->url = $editURL.$room->id;
    }

    /**
     * addToolBar
     *
     * creates a joomla administrative tool bar
     */
    private function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER').': '.JText::_('COM_THM_ORGANIZER_TRM_TITLE');
        JToolBarHelper::title( $title, 'mni' );
        JToolBarHelper::addNew('teacher.add');
        JToolBarHelper::editList('teacher.edit');
        JToolBarHelper::deleteList(JText::_( 'COM_THM_ORGANIZER_RMM_DELETE_CONFIRM'),'teacher.delete');
        if (thm_organizerHelper::isAdmin("teacher_manager"))
        {
        	JToolBarHelper::divider();
        	JToolBarHelper::preferences('com_thm_organizer');
        }
    }
}