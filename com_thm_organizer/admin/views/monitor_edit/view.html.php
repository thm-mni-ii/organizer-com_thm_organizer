<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewmonitor_edit
 * @description monitor edit view
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
 * Class loading a monitor entry into the view context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewMonitor_Edit extends JView
{
    /**
     * loads monitor information into the view context
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

        JHtml::_('behavior.framework', true);
        JHTML::_('behavior.formvalidation');
        JHTML::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/thm_organizer.css');
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/monitor_edit.js'));

        $this->form = $this->get('Form');

        $this->addToolBar();

        parent::display($tpl);
    }

    /**
     * Adds joomla toolbar elements to the view context
     *
     * @return void
     */
    private function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER') . ': ';
        $title .= ($this->form->getValue('id'))? JText::_('JTOOLBAR_EDIT') : JText::_('JTOOLBAR_NEW');
        $title .= " " . JText::_('COM_THM_ORGANIZER_MONITOR');
        JToolBarHelper::title($title, 'organizer_monitors');
        JToolBarHelper::save('monitor.save');
        JToolBarHelper::save2new('monitor.save2new');
        JToolBarHelper::cancel('monitor.cancel');
    }
}
