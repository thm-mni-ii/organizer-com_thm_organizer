<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewSchedule_Merge
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * Class provides functions for the merging of schedules
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewSchedule_Merge extends JViewLegacy
{
    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (default: null)
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/thm_organizer.css');

        $model = $this->getModel();

        $this->schedules = $model->schedules;

        // Set the toolbar
        $this->addToolBar();

        // Display the template
        parent::display($tpl);
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return  void
     */
    protected function addToolBar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_SCH_MERGE_TITLE'));
        JToolbarHelper::custom('schedule.merge', 'merge', 'merge', 'COM_THM_ORGANIZER_MERGE', false);
        JToolbarHelper::cancel('schedule.cancel', 'JTOOLBAR_CANCEL');
    }
}
