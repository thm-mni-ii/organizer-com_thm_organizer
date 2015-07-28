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
        $this->departmentName = $this->model->departmentName;
        $this->makeScheduleSelectBox();

        if (!empty($this->model->schedule))
        {
            $this->makeTeacherSelectBox();
            $this->tables = $this->getDeputatTables();
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

        $options = array();
        $options[] = JHtml::_('select.option', '*', JText::_('JALL'));
        foreach ($teachers as $teacherID => $teacherName)
        {
            $options[] = JHtml::_('select.option', $teacherID, $teacherName);
        }

        $attribs = array('multiple' => 'multiple', 'size' => '10');
        $selectedTeachers = $this->model->selected;
        $this->teachers = JHtml::_('select.genericlist', $options, 'teachers[]', $attribs, 'value', 'text', $selectedTeachers);
    }

    /**
     * Function to get a table displaying resource consumption for a schedule
     *
     * @return  string  a HTML string for a consumption table
     */
    public function getDeputatTables()
    {
        $tables = array();
        foreach ($this->model->deputat as $deputat)
        {
            $displaySummary = !empty($deputat['summary']);
            $displayTally = !empty($deputat['tally']);

            $buttonPanel = '';
            //$buttonPanel .= '<div class="button-panel"><button id="export">';
            //$buttonPanel .= JText::_("COM_THM_ORGANIZER_ACTION_EXPORT_FORM");
            //$buttonPanel .= '<i class="icon-download"></i></button></div>';

            $table = '<table class="deputat-table"><tbody class="deputat-table-body">';
            $table .= '<tr class="teacher-header"><th colspan="5">' . $deputat['name'] . '</th></tr>';
            if ($displaySummary)
            {
                $table .= '<tr class="sum-header">';
                $table .= '<th>Lehrveranstaltung</th>';
                $table .= '<th>Art<br/>(Kürzel)</th>';
                $table .= '<th>Studiengang Semester</th>';
                $table .= '<th>Wochentag u. Stunde<br/>(bei Blockveranstalt. Datum)</th>';
                $table .= '<th>Gemeldetes Deputat (SWS)<br/> und Summe</th>';
                $table .= '</tr>';
                $table .= $this->getSummaryRows($deputat);
            }
            if ($displayTally)
            {
                $extraClass = $displaySummary? 'second-group': '';
                $table .= '<tr class="tally-header ' . $extraClass . '">';
                $table .= '<th>Rechtsgrundlage<br/>gemäß LVVO</th>';
                $table .= '<th>Art der Abschlussarbeit<br/>(nur bei Betreuung als Referent/in)</th>';
                $table .= '<th>Umfang der Anrechnung in SWS je Arbeit<br />(insgesamt max. 2 SWS)</th>';
                $table .= '<th>Anzahl der Arbeiten</th>';
                $table .= '<th>Gemeldetes Deputat (SWS)</th>';
                $table .= '</tr>';
                $table .= $this->getTallyRows($deputat);
            }
            $table .= '</tbody></table>';
            $tables[] = $buttonPanel . $table;
        }
        return implode('', $tables);
    }

    /**
     * Retrieves a rows containing information about
     *
     * @param   array   &$deputat  the table columns
     *
     * @return  string  HTML String for the summary row
     */
    private function getSummaryRows(&$deputat)
    {
        $style = 'style ="vnd.ms-excel.numberformat:@;"';

        $rows = array();
        $swsSum = 0;
        $realSum = 0;
        $weeks = $this->params->get('deputat_weeks', 13);
        foreach ($deputat['summary'] as $summary)
        {
            $periodsText = (count($summary['periods']) > 10)?
                "{$summary['startdate']} bis {$summary['enddate']}" : implode(', ', array_keys($summary['periods']));
            $row = '<tr class="data-row">';
            $row .= '<td>' . $summary['name'] . '</td>';
            $row .= '<td>' . $summary['type'] . '</td>';
            $row .= '<td>' . implode(',', $summary['pools']) . '</td>';
            $row .= '<td>' . $periodsText . '</td>';
            $sws = ceil((int) $summary['hours'] / $weeks);
            $row .= '<td ' . $style . '>' . $sws . ' (' . $summary['hours'] . ')</td>';
            $swsSum += $sws;
            $realSum += $summary['hours'];
            $row .= '</tr>';
            $rows[] = $row;
        }
        $sumRow = '<tr class="sum-row">';
        $sumRow .= '<td class="empty-cell"></td>';
        $sumRow .= '<td class="empty-cell"></td>';
        $sumRow .= '<td class="empty-cell"></td>';
        $sumRow .= '<td>Summe</td>';
        $sumRow .= '<td ' . $style . '>' . $swsSum . ' (' . $realSum . ')</td>';
        $sumRow .= '</tr>';
        $rows[] = $sumRow;

        return implode('', $rows);
    }

    /**
     * Retrieves a row containing a summary of the column values in all the other rows. In the process it removes
     * columns without values.
     *
     * @param   array   &$deputat  the table columns
     *
     * @return  string  HTML String for the summary row
     */
    private function getTallyRows(&$deputat)
    {
        $style = 'style ="vnd.ms-excel.numberformat:@;"';
        $rows = array();
        $swsSum = 0;
        foreach ($deputat['tally'] as $name => $data)
        {
            $sws = $data['rate'] * $data['count'];
            $swsSum += $sws;
            $row = '<tr class="data-row">';
            $row .= '<td>LVVO § 2 (5)</td>';
            $row .= '<td>' . $name . '</td>';
            $row .= '<td ' . $style . '>' . $data['rate'] . '</td>';
            $row .= '<td ' . $style . '>' . $data['count'] . '</td>';
            $row .= '<td ' . $style . '>' . $sws . '</td>';
            $row .= '</tr>';
            $rows[] = $row;
        }
        $sumRow = '<tr class="sum-row">';
        $sumRow .= '<td class="empty-cell"></td>';
        $sumRow .= '<td class="empty-cell"></td>';
        $sumRow .= '<td class="empty-cell"></td>';
        $sumRow .= '<td>Summe</td>';
        $sumRow .= '<td ' . $style . '>' . $swsSum . '</td>';
        $sumRow .= '</tr>';
        $rows[] = $sumRow;

        return implode('', $rows);
    }
}
