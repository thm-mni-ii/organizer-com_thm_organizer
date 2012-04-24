<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        class manager view
 * @description provides a list of classes
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

class thm_organizersViewclass_manager extends JView
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

        $this->classes 			= $this->get('Items');
        $this->pagination 		= $this->get('Pagination');
        $this->state 			= $this->get('State');
        $this->managers 		= $model->managers;
        $this->semesters 		= $model->semesters;
        $this->majors 			= $model->majors;
        
        // for sorting
        $state = $this->get('State');
        $this->orderby   = $state->get('filter_order');
        $this->direction = $state->get('filter_order_Dir');
        
        $this->addToolBar();
        if(count($this->classes))$this->addLinks();

        parent::display($tpl);
    }

    /**
     * addLinks
     *
     * creates links to the edit view for the particular schedule
     */
    private function addLinks()
    {
        $editURL = 'index.php?option=com_thm_organizer&view=class_edit&classID=';
        foreach($this->classes as $key => $class)
            $this->classes[$key]->url = $editURL.$class->id;
    }

    /**
     * addToolBar
     *
     * creates a joomla administrative tool bar
     */
    private function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER').': '.JText::_('COM_THM_ORGANIZER_CLM_TITLE');
        JToolBarHelper::title($title, "mni");
        JToolBarHelper::addNew('class.add');
        JToolBarHelper::editList('class.edit');
        JToolBarHelper::deleteList(JText::_( 'COM_THM_ORGANIZER_CLM_DELETE_CONFIRM'),'class.delete');
        if (thm_organizerHelper::isAdmin("class_manager"))
        {
        	JToolBarHelper::divider();
        	JToolBarHelper::preferences('com_thm_organizer');
        }

    }
}