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
    public $model = null;

    public $roomsTable = '';

    public $teachersTable = '';

    public $scheduleSelectBox = '';

    public $typeSelectBox = '';

    public $startCalendar = '';

    public $endCalendar = '';

    public $exportButton = '';

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
        $this->makeTypeSelectBox();
        
        if (!empty($this->model->schedule))
        {
            $this->makeCalendars();
            if ($this->model->process['rooms'])
            {
                $this->makeResourceSelectBox('rooms');
                $this->makeResourceSelectBox('roomtypes');
                $this->makeConsumptionTable('rooms');
            }
            if ($this->model->process['teachers'])
            {
                $this->makeResourceSelectBox('teachers');
                $this->makeResourceSelectBox('fields');
                $this->makeConsumptionTable('teachers');
            }
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
        JHTML::_('behavior.calendar');
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
        $schedules = $this->getModel()->getActiveSchedules();

        $options = array();
        $options[] = JHtml::_('select.option', 0, JText::_("COM_THM_ORGANIZER_CONSUMPTION_SELECT_SCHEDULE"));
        foreach ($schedules as $schedule)
        {
            $options[] = JHtml::_('select.option', $schedule['id'], $schedule['name']);
        }

        $attribs = array('onChange' => "$('#reset').val('1');this.form.submit();");

        $this->scheduleSelectBox = JHtml::_('select.genericlist', $options, 'activated', $attribs, 'value', 'text', $scheduleID);
    }

    /**
     * Creates the calendars for restricting the dates for consumption calculation
     *
     * @return  void  sets context variables $startCalendar and $endCalendar
     */
    private function makeCalendars()
    {
        $attribs = array('size' => '10');
        $this->startCalendar = JHtml::calendar($this->model->startDate, 'startdate', 'startdate', '%d.%m.%Y', $attribs);
        $this->endCalendar = JHtml::calendar($this->model->endDate, 'enddate', 'enddate', '%d.%m.%Y', $attribs);
    }

    /**
     * Creates a select box for the resource types
     *
     * @return  void
     */
    private function makeTypeSelectBox()
    {
        $options = array();
        $options[] = JHtml::_('select.option', ROOM, JText::_("COM_THM_ORGANIZER_ROOMS"));
        $options[] = JHtml::_('select.option', TEACHER, JText::_("COM_THM_ORGANIZER_TEACHERS"));

        $attribs = array('onChange' => "$('#reset').val('1');this.form.submit();");
        $selectedType = $this->model->type;

        $this->typeSelectBox = JHtml::_('select.genericlist', $options, 'type', $attribs, 'value', 'text', $selectedType);
    }

    /**
     * Creates a select box for resources
     *
     * @param   string  $type  the resource type
     *
     * @return  void
     */
    private function makeResourceSelectBox($type)
    {
        $textConstant = 'COM_THM_ORGANIZER_ALL_' . strtoupper($type);
        $boxName = $type . 'SelectBox';

        $resources = $this->model->names[$type];
        asort($resources);
        $selectedResources = $this->model->selected[$type];
        $options = array();
        $options[] = JHtml::_('select.option', '*', JText::_($textConstant));
        foreach ($resources as $resourceID => $resourceName)
        {
            $options[] = JHtml::_('select.option', $resourceID, $resourceName);
        }

        $type = $type . '[]';
        $attribs = array('multiple' => 'multiple', 'size' => '10');

        $this->$boxName = JHtml::_('select.genericlist', $options, $type, $attribs, 'value', 'text', $selectedResources);
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
        $this->$tableName = $this->getModel()->getConsumptionTable($type);
        $this->exportButton = '<button id="' . $type . 'Export">' . JText::_("COM_THM_ORGANIZER_EXPORT_TABLE_EXCEL") . '</button>';
    }
}
