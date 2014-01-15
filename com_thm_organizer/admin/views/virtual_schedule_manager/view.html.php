<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.view
 * @name        THM_OrganizerViewVirtual_Schedule_Manager
 * @description provides a list of virtual schedules
 * @author      Wolf Rost,  <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class THM_OrganizerViewVirtual_Schedule_Manager for component com_thm_organizer
 * Class provides methods to display a list of virtual schedules
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin.view
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewVirtual_Schedule_Manager extends JView
{
    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (Default: null)
     *
     * @return void
     */
    public function display($tpl = null)
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/thm_organizer.css');

        $this->addToolBar();
        parent::display($tpl);
    }

    /**
     * Method to add the toolbar
     *
     * @return  void
     */
    private function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER') . ': ' . JText::_('COM_THM_ORGANIZER_VSM_TITLE');
        JToolBarHelper::title($title, 'mni');
    }
}
