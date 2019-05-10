<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Teachers;

/**
 * Class loads the schedule export filter form into the display context.
 */
class Schedule_Export extends BaseHTMLView
{
    public $date;

    public $departments;

    public $fields = [];

    public $planningPeriods;

    public $pools;

    public $programs;

    public $rooms;

    public $teachers;

    public $timePeriods;

    /**
     * Sets context variables and displays the schedule
     *
     * @param string $tpl template
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();

        $this->model = $this->getModel();

        $this->setFilterFields();
        $this->setFormatFields();
        $this->setResourceFields();

        parent::display($tpl);
    }

    /**
     * Checks whether the view has been set for seeing impaired users.
     *
     * @return bool true if the view has been configured for seeing impaired users, otherwise false
     */
    public function isSeeingImpaired()
    {
        $app = OrganizerHelper::getApplication();
        if (empty($app->getMenu()) or empty($app->getMenu()->getActive())) {
            return 0;
        }

        return (int)$app->getMenu()->getActive()->params->get('si', false);
    }

    /**
     * Modifies document variables and adds links to external files
     *
     * @return void
     */
    private function modifyDocument()
    {
        $seeingImpaired = $this->isSeeingImpaired();

        if (empty($seeingImpaired)) {
            HTML::_('bootstrap.framework');
            HTML::_('bootstrap.tooltip');
            HTML::_('jquery.ui');
            HTML::_('behavior.calendar');
            HTML::_('formbehavior.chosen', 'select');
            $this->setLayout('default');
        } else {
            $this->setLayout('default_si');
        }

        $document = Factory::getDocument();
        $document->addScript(Uri::root() . 'components/com_thm_organizer/js/schedule_export.js');
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/schedule_export.css');
    }

    /**
     * Creates resource selection fields for the form
     *
     * @return void sets indexes in $this->fields['resouceSettings'] with html content
     */
    private function setFilterFields()
    {
        $this->fields['filterFields'] = [];

        // Departments
        $deptAttribs                     = [];
        $deptAttribs['onChange']         = 'repopulatePrograms();repopulateResources();';
        $deptAttribs['data-placeholder'] = Languages::_('THM_ORGANIZER_DEPARTMENT_SELECT_PLACEHOLDER');

        $departmentOptions = $this->model->getDepartmentOptions();
        $departmentSelect  = HTML::selectBox($departmentOptions, 'departmentIDs', $deptAttribs);

        $this->fields['filterFields']['departmentIDs'] = [
            'label'       => Languages::_('THM_ORGANIZER_DEPARTMENT'),
            'description' => Languages::_('THM_ORGANIZER_DEPARTMENTS_EXPORT_DESC'),
            'input'       => $departmentSelect
        ];

        // Programs
        $programAttribs = [
            'multiple'         => 'multiple',
            'onChange'         => 'repopulateResources();',
            'data-placeholder' => Languages::_('THM_ORGANIZER_PROGRAMS_SELECT_PLACEHOLDER')
        ];
        $programSelect  = HTML::selectBox([], 'programIDs', $programAttribs);

        $this->fields['filterFields']['programIDs'] = [
            'label'       => Languages::_('THM_ORGANIZER_PROGRAMS'),
            'description' => Languages::_('THM_ORGANIZER_PROGRAMS_EXPORT_DESC'),
            'input'       => $programSelect
        ];
    }

    /**
     * Creates format settings fields for the form
     *
     * @return void sets indexes in $this->fields['formatSettings'] with html content
     */
    private function setFormatFields()
    {
        $this->fields['formatSettings'] = [];
        $attribs                        = [];

        $seeingImpaired = $this->isSeeingImpaired();

        $formatAttribs             = $attribs;
        $formatAttribs['onChange'] = 'setFormat();';
        $fileFormats               = [];
        $fileFormats['xls.si']     = Languages::_('THM_ORGANIZER_XLS_CALENDAR_BLIND');
        $fileFormats['ics']        = Languages::_('THM_ORGANIZER_ICS_CALENDAR');
        $fileFormats['pdf.a3']     = Languages::_('THM_ORGANIZER_PDF_A3_DOCUMENT');
        $fileFormats['pdf.a4']     = Languages::_('THM_ORGANIZER_PDF_A4_DOCUMENT');

        $defaultFileFormat = $seeingImpaired ? 'xls.si' : 'pdf.a4';
        $fileFormatSelect  = HTML::selectBox($fileFormats, 'format', $formatAttribs, $defaultFileFormat);

        $this->fields['formatSettings']['format'] = [
            'label'       => Languages::_('THM_ORGANIZER_FILE_FORMAT'),
            'description' => Languages::_('THM_ORGANIZER_FILE_FORMAT_DESC'),
            'input'       => $fileFormatSelect
        ];

        $titlesOptions      = [];
        $titlesOptions['1'] = Languages::_('THM_ORGANIZER_FULL_TITLE');
        $titlesOptions['2'] = Languages::_('THM_ORGANIZER_SHORT_TITLE');
        $titlesOptions['3'] = Languages::_('THM_ORGANIZER_ABBREVIATION');
        $titlesSelect       = HTML::selectBox($titlesOptions, 'titles', $attribs, '1');

        $this->fields['formatSettings']['titles'] = [
            'label'       => Languages::_('THM_ORGANIZER_TITLES'),
            'description' => Languages::_('THM_ORGANIZER_TITLES_FORMAT_DESC'),
            'input'       => $titlesSelect
        ];

        $groupingOptions      = [];
        $groupingOptions['0'] = Languages::_('JNONE');
        $groupingOptions['1'] = Languages::_('THM_ORGANIZER_BY_RESOURCE');
        $groupingSelect       = HTML::selectBox($groupingOptions, 'grouping', $attribs, '1');

        $this->fields['formatSettings']['grouping'] = [
            'label'       => Languages::_('THM_ORGANIZER_GROUPING'),
            'description' => Languages::_('THM_ORGANIZER_GROUPING_DESC'),
            'input'       => $groupingSelect
        ];

        $grids       = $this->model->getGridOptions();
        $defaultGrid = $this->model->defaultGrid;
        $gridSelect  = HTML::selectBox($grids, 'gridID', $attribs, $defaultGrid);

        $this->fields['formatSettings']['gridID'] = [
            'label'       => Languages::_('THM_ORGANIZER_GRID'),
            'description' => Languages::_('THM_ORGANIZER_GRID_EXPORT_DESC'),
            'input'       => $gridSelect
        ];

//        $displayFormats             = [];
//        $displayFormats['list']     = Languages::_('THM_ORGANIZER_LIST');
//        $displayFormats['schedule'] = Languages::_('THM_ORGANIZER_SCHEDULE');
//        $defaultDisplayFormat       = 'schedule';
//        $displayFormatSelect        = HTML::selectBox($displayFormats, 'displayFormat', $attribs,
//            $defaultDisplayFormat);
//
//        $this->fields['formatSettings']['displayFormat'] = [
//            'label'       => Languages::_('THM_ORGANIZER_DISPLAY_FORMAT'),
//            'description' => Languages::_('THM_ORGANIZER_DISPLAY_FORMAT_DESC'),
//            'input'       => $displayFormatSelect
//        ];

        // The Joomla calendar form field demands the % character before the real date format instruction values.
        $rawDateFormat = OrganizerHelper::getParams()->get('dateFormat');
        $today         = date('Y-m-d');

        if ($seeingImpaired) {
            $dateSelect = '<input name="date" type="date" value="' . $today . '">';
        } else {
            $dateFormat = preg_replace('/([a-zA-Z])/', "%$1", $rawDateFormat);
            $dateSelect = HTML::_('calendar', $today, 'date', 'date', $dateFormat, $attribs);
        }

        $this->fields['formatSettings']['date'] = [
            'label'       => Languages::_('JDATE'),
            'description' => Languages::_('THM_ORGANIZER_DATE_DESC'),
            'input'       => $dateSelect
        ];

        $intervals             = [];
        $intervals['day']      = Languages::_('THM_ORGANIZER_DAY');
        $intervals['week']     = Languages::_('THM_ORGANIZER_WEEK');
        $intervals['month']    = Languages::_('THM_ORGANIZER_MONTH');
        $intervals['semester'] = Languages::_('THM_ORGANIZER_SEMESTER');
        $defaultInterval       = 'week';
        $intervalSelect        = HTML::selectBox($intervals, 'dateRestriction', $attribs, $defaultInterval);

        $this->fields['formatSettings']['dateRestriction'] = [
            'label'       => Languages::_('THM_ORGANIZER_DATE_RESTRICTION'),
            'description' => Languages::_('THM_ORGANIZER_DATE_RESTRICTION_DESC'),
            'input'       => $intervalSelect
        ];

//        $pdfWeekFormats          = [];
//        $pdfWeekFormats['stack'] = Languages::_('THM_ORGANIZER_STACKED_PLANS'),;
//        $pdfWeekFormats['sequence'] = Languages::_('THM_ORGANIZER_SEQUENCED_PLANS');
//        $defaultPDFWeekFormat       = 'sequence';
//
//        $pdfWeekFormatSelect = HTML::selectBox($pdfWeekFormats, 'pdfWeekFormat', $attribs, $defaultPDFWeekFormat);
//
//        $this->fields['formatSettings']['pdfWeekFormat'] = [
//            'label'       => Languages::_('THM_ORGANIZER_WEEK_FORMAT'),
//            'description' => Languages::_('THM_ORGANIZER_WEEK_FORMAT_PDF_DESC'),
//            'input'       => $pdfWeekFormatSelect
//        ];

        $xlsWeekFormats       = [];
        $xlsWeekFormats[]     = ['text' => Languages::_('THM_ORGANIZER_ONE_WORKSHEET'), 'value' => 'sequence'];
        $xlsWeekFormats[]     = ['text' => Languages::_('THM_ORGANIZER_MULTIPLE_WORKSHEETS'), 'value' => 'stack'];
        $defaultXLSWeekFormat = 'sequence';

        $xlsWeekFormatSelect = HTML::selectBox($xlsWeekFormats, 'xlsWeekFormat', $attribs, $defaultXLSWeekFormat);

        $this->fields['formatSettings']['xlsWeekFormat'] = [
            'label'       => Languages::_('THM_ORGANIZER_WEEK_FORMAT'),
            'description' => Languages::_('THM_ORGANIZER_WEEK_FORMAT_XLS_DESC'),
            'input'       => $xlsWeekFormatSelect
        ];
    }

    /**
     * Creates resource selection fields for the form
     *
     * @return void sets indexes in $this->fields['resouceSettings'] with html content
     */
    private function setResourceFields()
    {
        $this->fields['resourceFields'] = [];

        $attribs = ['multiple' => 'multiple'];

        $user = Factory::getUser();

        if (!empty($user->id)) {
            $this->fields['resourceFields']['myschedule'] = [
                'label'       => Languages::_('THM_ORGANIZER_MY_SCHEDULE'),
                'description' => Languages::_('THM_ORGANIZER_MY_SCHEDULE_EXPORT_DESC'),
                'input'       => '<input type="checkbox" id="myschedule" onclick="toggleMySchedule();">'
            ];
        }

        // Pools
        $poolAttribs                     = $attribs;
        $poolAttribs['data-placeholder'] = Languages::_('THM_ORGANIZER_SELECT_EVENT_PLANS');
        $poolSelect                      = HTML::selectBox([], 'poolIDs', $poolAttribs);

        $this->fields['resourceFields']['poolIDs'] = [
            'label'       => Languages::_('THM_ORGANIZER_POOLS'),
            'description' => Languages::_('THM_ORGANIZER_POOLS_EXPORT_DESC'),
            'input'       => $poolSelect
        ];

        $privilegedAccess = Access::allowViewAccess();
        $isTeacher        = Teachers::getIDByUserID();

        if ($privilegedAccess or !empty($isTeacher)) {
            $teacherAttribs                     = $attribs;
            $teacherAttribs['data-placeholder'] = Languages::_('THM_ORGANIZER_SELECT_TEACHERS');
            $planTeacherOptions                 = $this->model->getTeacherOptions();
            $teacherSelect                      = HTML::selectBox($planTeacherOptions, 'teacherIDs', $teacherAttribs);

            $this->fields['resourceFields']['teacherIDs'] = [
                'label'       => Languages::_('THM_ORGANIZER_TEACHERS'),
                'description' => Languages::_('THM_ORGANIZER_TEACHERS_EXPORT_DESC'),
                'input'       => $teacherSelect
            ];
        }

        // Rooms
        $roomAttribs                     = $attribs;
        $roomAttribs['data-placeholder'] = Languages::_('THM_ORGANIZER_SELECT_ROOMS');
        $roomSelect                      = HTML::selectBox([], 'roomIDs', $roomAttribs);

        $this->fields['resourceFields']['roomIDs'] = [
            'label'       => Languages::_('THM_ORGANIZER_ROOMS'),
            'description' => Languages::_('THM_ORGANIZER_ROOMS_EXPORT_DESC'),
            'input'       => $roomSelect
        ];
    }
}
