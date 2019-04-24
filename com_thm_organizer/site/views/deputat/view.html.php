<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

use THM_OrganizerHelperHTML as HTML;
use Joomla\CMS\Uri\Uri;

/**
 * Class loads teacher workload statistics into the display context.
 */
class THM_OrganizerViewDeputat extends \Joomla\CMS\MVC\View\HtmlView
{
    public $endCalendar = '';

    public $hoursSelectBox = '';

    public $model = null;

    public $params = null;

    public $scheduleSelectBox = '';

    public $startCalendar = '';

    public $table = '';

    public $teachers;

    public $typeSelectBox = '';

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     * @throws Exception => unauthorized access
     */
    public function display($tpl = null)
    {
        if (!THM_OrganizerHelperAccess::isAdmin()) {
            throw new \Exception(\JText::_('COM_THM_ORGANIZER_401'), 401);
        }

        // Sets js and css
        $this->modifyDocument();

        $this->params = THM_OrganizerHelperComponent::getParams();

        $this->model          = $this->getModel();
        $this->departmentName = $this->model->departmentName;
        $this->makeScheduleSelectBox();

        if (!empty($this->model->schedule)) {
            $this->makeTeacherSelectBox();
            $this->tables = $this->getDeputatTables();
        }
        parent::display($tpl);
    }

    /**
     * Modifies document variables and adds links to external files
     *
     * @return void
     */
    private function modifyDocument()
    {
        HTML::_('jquery.ui');
        HTML::_('behavior.calendar');
        HTML::_('formbehavior.chosen', 'select');
        $document = \JFactory::getDocument();
        $document->setCharset('utf-8');
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/deputat.css');
        $document->addScript(Uri::root() . 'components/com_thm_organizer/js/deputat.js');
    }

    /**
     * Creates a select box for the active schedules
     *
     * @return void
     */
    private function makeScheduleSelectBox()
    {
        $scheduleID = $this->model->scheduleID;
        $schedules  = $this->model->getDepartmentSchedules();

        $options    = [];
        $options[0] = \JText::_('COM_THM_ORGANIZER_FILTER_SCHEDULE');
        foreach ($schedules as $schedule) {
            $options[$schedule['id']] = $schedule['name'];
        }

        $attribs             = [];
        $attribs['onChange'] = "jQuery('#reset').val('1');this.form.submit();";

        $this->scheduleSelectBox = HTML::selectBox($options, 'scheduleID', $attribs, $scheduleID);
    }

    /**
     * Creates a select box for teachers
     *
     * @return void
     */
    private function makeTeacherSelectBox()
    {
        $teachers = $this->model->teachers;

        $options      = [];
        $options['*'] = \JText::_('JALL');
        foreach ($teachers as $teacherID => $teacherName) {
            $options[$teacherID] = $teacherName;
        }

        $attribs          = ['multiple' => 'multiple', 'size' => '10'];
        $selectedTeachers = $this->model->selected;
        $this->teachers   = HTML::selectBox($options, 'teachers', $attribs, $selectedTeachers);
    }

    /**
     * Function to get a table displaying resource consumption for a schedule
     *
     * @return string  a HTML string for a consumption table
     */
    public function getDeputatTables()
    {
        $tables = [];
        foreach ($this->model->deputat as $teacherID => $deputat) {
            $displaySummary = !empty($deputat['summary']);
            $displayTally   = !empty($deputat['tally']);

            $table = '<table class="deputat-table" id="deputat-table-' . $teacherID . '">';
            $table .= '<thead class="deputat-table-head-' . $teacherID . '">';
            $table .= '<tr class="teacher-header"><th colspan="5">' . $deputat['name'] . '</th></tr></thead>';
            if ($displaySummary) {
                $table .= '<tbody class="deputat-table-body" id="deputat-table-body-sum-' . $teacherID . '">';
                $table .= '<tr class="sum-header">';
                $table .= '<th>Lehrveranstaltung</th>';
                $table .= '<th>Art<br/>(Kürzel)</th>';
                $table .= '<th>Studiengang Semester</th>';
                $table .= '<th>Wochentag u. Stunde<br/>(bei Blockveranstalt. Datum)</th>';
                $table .= '<th>Gemeldetes Deputat (SWS)<br/> und Summe</th>';
                $table .= '</tr>';
                $table .= $this->getSummaryRows($teacherID, $deputat);
                $table .= '</tbody>';
            }
            if ($displayTally) {
                $table      .= '<tbody class="deputat-table-body" id="deputat-table-body-tally-' . $teacherID . '">';
                $extraClass = $displaySummary ? 'second-group' : '';
                $table      .= '<tr class="tally-header ' . $extraClass . '">';
                $table      .= '<th>Rechtsgrundlage<br/>gemäß LVVO</th>';
                $table      .= '<th>Art der Abschlussarbeit<br/>(nur bei Betreuung als Referent/in)</th>';
                $table      .= '<th>Umfang der Anrechnung in SWS je Arbeit<br />(insgesamt max. 2 SWS)</th>';
                $table      .= '<th>Anzahl der Arbeiten</th>';
                $table      .= '<th>Gemeldetes Deputat (SWS)</th>';
                $table      .= '</tr>';
                $table      .= $this->getTallyRows($teacherID, $deputat);
                $table      .= '</tbody>';
            }
            $table    .= '</table>';
            $tables[] = $table;
        }

        return implode('', $tables);
    }

    /**
     * Retrieves a rows containing information about
     *
     * @param int    $teacherID the teacherID
     * @param array &$deputat   the table columns
     *
     * @return string  HTML string for the summary row
     */
    private function getSummaryRows($teacherID, &$deputat)
    {
        $rows      = [];
        $swsSum    = 0;
        $realSum   = 0;
        $weeks     = $this->params->get('deputat_weeks', 13);
        $rowNumber = 0;
        foreach ($deputat['summary'] as $summary) {
            $remove      = '<a id="remove-data-row-' . $teacherID . '-' . $rowNumber . '" onclick="removeRow(this)">';
            $remove      .= '<i class="icon-remove"></i>';
            $remove      .= '</a>';
            $periodsText = (count($summary['periods']) > 10) ?
                "{$summary['startDate']} bis {$summary['endDate']}" : implode(', ', array_keys($summary['periods']));
            $row         = '<tr class="data-row" id="data-row-' . $teacherID . '-' . $rowNumber . '">';
            $row         .= '<td>' . $summary['name'] . '</td>';
            $row         .= '<td>' . $summary['type'] . '</td>';
            $row         .= '<td>' . implode(',', $summary['pools']) . '</td>';
            $row         .= '<td>' . $periodsText . '</td>';
            $sws         = ceil((int)$summary['hours'] / $weeks);
            $row         .= '<td>';
            $row         .= '<span class="row-sws" id="row-sws-' . $teacherID . '-' . $rowNumber . '">' . $sws . '</span>';
            $row         .= ' (<span class="row-sws" id="row-total-' . $teacherID . '-' . $rowNumber . '">' . $summary['hours'] . '</span>)';
            $row         .= $remove . '</td>';
            $swsSum      += $sws;
            $realSum     += $summary['hours'];
            $row         .= '</tr>';
            $rows[]      = $row;
            $rowNumber++;
        }
        $sumRow = '<tr class="sum-row-' . $teacherID . '">';
        $sumRow .= '<td class="empty-cell"></td>';
        $sumRow .= '<td class="empty-cell"></td>';
        $sumRow .= '<td class="empty-cell"></td>';
        $sumRow .= '<td>Summe</td>';
        $sumRow .= '<td>';
        $sumRow .= '<span class="sum-sws" id="sum-sws-' . $teacherID . '">' . $swsSum . '</span>';
        $sumRow .= ' (<span class="sum-total" id="sum-total-' . $teacherID . '">' . $realSum . '</span>)';
        $sumRow .= '</td>';
        $sumRow .= '</tr>';
        $rows[] = $sumRow;

        return implode('', $rows);
    }

    /**
     * Retrieves a row containing a summary of the column values in all the other rows. In the process it removes
     * columns without values.
     *
     * @param int    $teacherID the teacherID
     * @param array &$deputat   the table columns
     *
     * @return string  HTML string for the summary row
     */
    private function getTallyRows($teacherID, &$deputat)
    {
        $rows   = [];
        $swsSum = 0;
        foreach ($deputat['tally'] as $name => $data) {
            $sws    = $data['rate'] * $data['count'];
            $swsSum += $sws;
            $row    = '<tr class="data-row-' . $teacherID . '">';
            $row    .= '<td>LVVO § 2 (5)</td>';
            $row    .= '<td>' . $name . '</td>';
            $row    .= '<td>' . $data['rate'] . '</td>';
            $row    .= '<td>' . $data['count'] . '</td>';
            $row    .= '<td>' . $sws . '</td>';
            $row    .= '</tr>';
            $rows[] = $row;
        }
        $sumRow = '<tr class="sum-row-' . $teacherID . '">';
        $sumRow .= '<td class="empty-cell"></td>';
        $sumRow .= '<td class="empty-cell"></td>';
        $sumRow .= '<td class="empty-cell"></td>';
        $sumRow .= '<td>Summe</td>';
        $sumRow .= '<td>' . $swsSum . '</td>';
        $sumRow .= '</tr>';
        $rows[] = $sumRow;

        return implode('', $rows);
    }
}
