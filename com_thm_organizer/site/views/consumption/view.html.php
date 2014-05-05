<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        consumption view
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
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
        $model = $this->getModel();
        
        $document = JFactory::getDocument();
        $document->setCharset("utf-8");
        $document->addScript($this->baseurl . '/media/com_thm_organizer/js/consumption.js');
        $document->addStyleSheet($this->baseurl . '/media/com_thm_organizer/css/consumption.css');
        
        $schedules = $model->getSchedulesFromDB();
        $input = JFactory::getApplication()->input;
        $scheduleID = $input->getInt('activated', 0);
        
        $currentValue = $scheduleID;
        $options = array();
        
        array_push(
            $options,
            JHtml::_('select.option',
                0,
                JText::_("COM_THM_ORGANIZER_CONSUMPTION_SELECT_SCHEDULE"))
        );
        
        foreach ($schedules as $schedule)
        {
            array_push(
                $options,
                JHtml::_('select.option',
                    $schedule->id,
                    $schedule->departmentname . " - " . $schedule->semestername . " - " . $schedule->creationdate .
                    " (" . $schedule->startdate . " - " . $schedule->enddate . ")")
            );
        }
        
        $this->schedulesSelectBox = JHtml::_('select.genericlist', $options, 'activated', null, 'value', 'text', $currentValue);
        
        $this->consumptionRoomTable = "";
        $this->consumptionTeacherTable = "";
        
        if ($scheduleID !== 0)
        {
            $schedule = $model->getScheduleJSONFromDB($scheduleID);
            if ($schedule === null)
            {
                JError::raiseWarning(500, JText::_("COM_THM_ORGANIZER_CONSUMPTION_NO_SCHEDULE"));
            }
            else
            {
                $schedule = json_decode($schedule->schedule);
                $consumptions = $model->getConsumptionFromSchedule($schedule);
                
                /**
                * Get room consumption
                 */
                $roomColumns = array_keys(get_object_vars($consumptions->rooms));
                
                $roomRows = array();
                
                foreach ($consumptions->rooms as $rooms)
                {
                    $roomRows = array_merge($roomRows, get_object_vars($rooms));
                }
                
                $this->consumptionRoomTable = $model->getConsumptionTable($roomColumns, $roomRows, $consumptions, "rooms", $schedule);
                
                $this->consumptionRoomTable .= "<div><button id='btnRoomExport'>" . JText::_("COM_THM_ORGANIZER_CONSUMPTION_BUTTON_EXPORT_ROOM_TABLE") . "</button></div>";
                        
                /**
                 * Get teacher consumption
                 */
                $teacherColumns = array_keys(get_object_vars($consumptions->teachers));
                
                $teacherRows = array();
                
                foreach ($consumptions->teachers as $teachers)
                {
                    $teacherRows = array_merge($teacherRows, get_object_vars($teachers));
                }
                
                $this->consumptionTeacherTable = $model->getConsumptionTable($teacherColumns, $teacherRows, $consumptions, "teachers", $schedule);
                
                $this->consumptionTeacherTable .= "<div><button id='btnTeacherExport'>" . JText::_("COM_THM_ORGANIZER_CONSUMPTION_BUTTON_EXPORT_TEACHER_TABLE") . "</button></div>";
            }
        }
        
        parent::display($tpl);
    }
}
