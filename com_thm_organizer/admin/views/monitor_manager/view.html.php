<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewmonitor_manager
 * @description monitor manager view
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Daniel Kirsten, <daniel.kirsten@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class loading a list of persistent monitor entries into the view context 
 * 
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerViewmonitor_manager extends JView
{
    /**
     * Loads data from the model into the view context
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
