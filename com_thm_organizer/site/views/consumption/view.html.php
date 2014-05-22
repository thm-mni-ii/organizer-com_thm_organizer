<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        consumption view
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

jimport('jquery.jquery');

/**
 * Class loads consumption information into the view context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewConsumption extends JViewLegacy
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
        $this->model = $this->getModel();
        $this->modifyDocument();
        $scheduleID = JFactory::getApplication()->input->getInt('activated', 0);
        $this->makeScheduleSelectBox($scheduleID);
        $this->roomsTable = "";
        $this->teachersTable = "";
        
        if ($scheduleID !== 0)
        {
            $this->makeConsumptionTable('rooms');
            $this->makeConsumptionTable('teachers');
        }
        
        parent::display($tpl);
    }

    /**
     * Modifies document variables and adds links to external files
     * 
     * @return  void
     */
    private function modifyDocument()
    {
        JFactory::getDocument()->setCharset("utf-8");
        JFactory::getDocument()->addScript($this->baseurl . '/media/com_thm_organizer/js/consumption.js');
        JFactory::getDocument()->addStyleSheet($this->baseurl . '/media/com_thm_organizer/css/consumption.css');
    }

    /**
     * Creates a select box for the active schedules
     * 
     * @param   int  $scheduleID  the id of the previously selected schedule
     * 
     * @return  void
     */
    private function makeScheduleSelectBox($scheduleID)
    {
        $schedules = $this->model->getActiveSchedules();

        $options = array();
        $options[] = JHtml::_('select.option', 0, JText::_("COM_THM_ORGANIZER_CONSUMPTION_SELECT_SCHEDULE"));
        foreach ($schedules as $schedule)
        {
            $options[] = JHtml::_('select.option', $schedule['id'], $schedule['name']);
        }

        $attribs = array('onChange' => 'this.form.submit()');

        $this->schedulesSelectBox = JHtml::_('select.genericlist', $options, 'activated', $attribs, 'value', 'text', $scheduleID);
        
    }

    /**
     * Creates a consumption table for a particular resource type
     * 
     * @param   string  $type  the type of resource for which a table should be created
     * 
     * @return  void
     */
    private function makeConsumptionTable($type)
    {
        $tableName = $type . 'Table';
        $this->$tableName = $this->model->getConsumptionTable($type);
        $this->$tableName .= "<div><button id='{$type}Export'>" . JText::_("COM_THM_ORGANIZER_CONSUMPTION_BUTTON_EXPORT_TABLE") . "</button></div>";
    }
}
