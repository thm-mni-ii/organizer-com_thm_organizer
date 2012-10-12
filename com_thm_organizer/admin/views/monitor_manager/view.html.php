<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        monitor manager view
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 *@author      Daniel Kirsten danielDOTkirstenATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';
/**
 * Class loading a list of persistent monitor entries into the view context 
 * 
 * @package  Admin
 * 
 * @since    2.5.4
 */
class thm_organizersViewmonitor_manager extends JView
{
    /**
     * loads data from the model into the view context
     * 
     * @param   string  $tpl  the name of the template to be used
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
        $document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");

        $this->monitors = $this->get('Items');
        $this->state = $this->get('State');
        $this->pagination = $this->get('Pagination');
        $this->behaviours = $model->behaviours;
        $this->prepareBehaviours();
        $this->rooms = $model->rooms;
        $this->addToolBar();

        parent::display($tpl);
    }

    /**
     * resolves the display constant to text
     * 
     * @return void 
     */
    private function prepareBehaviours()
    {
        if (!count($this->monitors))
        {
            return;
        }
        foreach ($this->monitors as $k => $monitor)
        {
            $this->monitors[$k]->display = $this->behaviours[$this->monitors[$k]->display];
        }
    }

    /**
     * creates joomla toolbar elements
     * 
     * @return void
     */
    private function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER') . ': ' . JText::_('COM_THM_ORGANIZER_MON_TITLE');
        JToolBarHelper::title($title, 'mni');
        JToolBarHelper::addNew('monitor.add');
        JToolBarHelper::editList('monitor.edit');
        JToolBarHelper::deleteList(JText::_('COM_THM_ORGANIZER_MON_DELETE_CONFIRM'), 'monitor.delete');
        if (thm_organizerHelper::isAdmin("monitor_manager"))
        {
        	JToolBarHelper::divider();
        	JToolBarHelper::preferences('com_thm_organizer');
        }
    }
}
