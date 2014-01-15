<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewmonitor_manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Daniel Kirsten, <daniel.kirsten@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * Class loading a list of persistent monitor entries into the view context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewMonitor_Manager extends JView
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
        JFactory::getDocument()->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/thm_organizer.css');

        $this->monitors = $this->get('Items');
        $this->state = $this->get('State');
        $this->pagination = $this->get('Pagination');
        $this->behaviours = $this->getModel()->behaviours;
        $this->rooms = $this->getModel()->rooms;
        $this->prepareBehaviours();
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
        foreach (array_keys($this->monitors) as $key)
        {
            $this->monitors[$key]->display = $this->behaviours[$this->monitors[$key]->display];
        }
    }

    /**
     * creates joomla toolbar elements
     *
     * @return void
     */
    private function addToolBar()
    {
        JToolBarHelper::title(JText::_('COM_THM_ORGANIZER_MON_TOOLBAR_TITLE'), 'organizer_monitors');
        JToolBarHelper::addNew('monitor.add');
        JToolBarHelper::editList('monitor.edit');
        JToolBarHelper::deleteList(JText::_('COM_THM_ORGANIZER_MON_DELETE_CONFIRM'), 'monitor.delete');
        JToolBarHelper::divider();
        JToolBarHelper::preferences('com_thm_organizer');
    }
}
