<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.view
 * @name        THM_OrganizerViewVirtual_Schedule_Manager
 * @description provides a list of virtual schedules
 * @author      Wolf Rost,  <Wolf.Rost@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.view');

/**
 * Class THM_OrganizerViewVirtual_Schedule_Manager for component com_thm_organizer
 * Class provides methods to display a list of virtual schedules
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin.view
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewVirtual_Schedule_Manager extends THM_CoreViewList
{
    public $items;

    public $pagination;

    public $state;

    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (Default: null)
     *
     * @return void
     */
    public function display($tpl = null)
    {
        parent::display($tpl);
    }

    /**
     * Method to add the toolbar
     *
     * @return  void
     */
    protected  function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER_VIRTUAL_SCHEDULE_MANAGER_VIEW_TITLE');
        JToolbarHelper::title($title, 'mni');
    }
}
