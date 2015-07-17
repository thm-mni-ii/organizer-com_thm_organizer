<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewDeputat
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class loads deputat information into the view context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewDeputat extends JViewLegacy
{
    public $params = null;

    public $model = null;

    public $scheduleSelectBox = '';

    public $typeSelectBox = '';

    public $startCalendar = '';

    public $endCalendar = '';

    public $hoursSelectBox = '';

    public $table = '';

    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (default: null)
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Sets js and css
        $this->modifyDocument();

        $this->params = JFactory::getApplication()->getParams();

        $this->model = $this->getModel();
        $this->makeScheduleSelectBox();

        if (!empty($this->model->schedule))
        {
            $this->makeTeacherSelectBox();
            $this->table = $this->getModel()->getDeputatTable();
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
        JHtml::_('jquery.ui');
        JHTML::_('behavior.calendar');
        JHtml::_('formbehavior.chosen', 'select');
        $document = JFactory::getDocument();
        $document->setCharset("utf-8");
        $document->addStyleSheet($this->baseurl . "/media/com_thm_organizer/css/deputat.css");
        $document->addScript($this->baseurl . '/media/com_thm_organizer/js/deputat.js');
    }

    /**
     * Creates a select box for the active schedules
     *
     * @return  void
     */
    private function makeScheduleSelectBox()
    {
        $scheduleID = $this->model->scheduleID;
        $schedules = $this->getModel()->getActiveSchedules();

        $options = array();
        $options[] = JHtml::_('select.option', 0, JText::_("COM_THM_ORGANIZER_FILTER_SCHEDULE"));
        foreach ($schedules as $schedule)
        {
            $options[] = JHtml::_('select.option', $schedule['id'], $schedule['name']);
        }

        $attribs = array();
        $attribs['onChange'] = "jQuery('#reset').val('1');this.form.submit();";

        $this->scheduleSelectBox = JHtml::_('select.genericlist', $options, 'scheduleID', $attribs, 'value', 'text', $scheduleID);
    }

    /**
     * Creates a select box for teachers
     *
     * @return  void
     */
    private function makeTeacherSelectBox()
    {
        $teachers = $this->model->teachers;
        asort($teachers);

        $options = array();
        $options[] = JHtml::_('select.option', '*', JText::_('JALL'));
        foreach ($teachers as $teacherID => $teacherName)
        {
            $options[] = JHtml::_('select.option', $teacherID, $teacherName);
        }

        $attribs = array('multiple' => 'multiple', 'size' => '10');
        $selectedTeachers = $this->model->selected;
        $this->teacherSelectBox = JHtml::_('select.genericlist', $options, 'teachers[]', $attribs, 'value', 'text', $selectedTeachers);
    }
}
