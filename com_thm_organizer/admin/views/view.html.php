<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        view monitor_manager
 * @description lists registered monitors along with associated rooms and display content
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';
class thm_organizersViewmonitor_manager extends JView
{

    public function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));

        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.multiselect');

        $model = $this->getModel();
        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $this->monitors = $this->get('Items');
        $this->prepareMonitors();
        $this->state = $this->get('State');
        $this->pagination = $this->get('Pagination');
        $this->behaviours = $model->behaviours;
        $this->rooms = $model->rooms;
        $this->addToolBar();

        parent::display($tpl);
    }

    private function prepareMonitors()
    {
        if(!count($this->monitors))return;
        $link = "index.php?option=com_thm_organizer&view=monitor_edit&monitorID=";
        foreach($this->monitors as $k => $monitor)
        {
            if(empty($monitor->room)) $this->monitors[$k]->room = $monitor->roomID;
            $this->monitors[$k]->behaviour = JText::_($monitor->behaviour);
            $this->monitors[$k]->link = $link.$monitor->monitorID;
        }
    }

    /**
     * addToolBar
     *
     * creates the toolbar for user actions
     */
    private function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER').': '.JText::_('COM_THM_ORGANIZER_VSM_TITLE');
        JToolBarHelper::title( $title, 'mni' );
        JToolBarHelper::addNew( 'monitor.add' );
        JToolBarHelper::editList('monitor.edit');
        JToolBarHelper::deleteList( JText::_('COM_THM_ORGANIZER_MON_DELETE_CONFIRM'), 'monitor.delete');
    }

}
	