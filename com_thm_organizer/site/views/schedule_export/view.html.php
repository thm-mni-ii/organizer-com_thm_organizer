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

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/schedule.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/teachers.php';

use THM_OrganizerHelperHTML as HTML;
use THM_OrganizerHelperLanguages as Languages;
use Joomla\CMS\Uri\Uri;

/**
 * Class loads the schedule export filter form into the display context.
 */
class THM_OrganizerViewSchedule_Export extends \Joomla\CMS\MVC\View\HtmlView
{
    public $compiler;

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

        $this->lang = Languages::getLanguage();

        $this->model    = $this->getModel();
        $this->compiler = jimport('tcpdf.tcpdf');

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
        $app = THM_OrganizerHelperComponent::getApplication();
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

        $document = \JFactory::getDocument();
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
        $deptAttribs['data-placeholder'] = \JText::_('COM_THM_ORGANIZER_DEPARTMENT_SELECT_PLACEHOLDER');

        $departmentOptions = $this->model->getDepartmentOptions();
        $departmentSelect  = HTML::selectBox($departmentOptions, 'departmentIDs', $deptAttribs);

        $this->fields['filterFields']['departmentIDs'] = [
            'label'       => \JText::_('COM_THM_ORGANIZER_DEPARTMENT'),
            'description' => \JText::_('COM_THM_ORGANIZER_DEPARTMENTS_EXPORT_DESC'),
            'input'       => $departmentSelect
        ];

        // Programs
        $programAttribs = [
            'multiple'         => 'multiple',
            'onChange'         => 'repopulateResources();',
            'data-placeholder' => \JText::_('COM_THM_ORGANIZER_PROGRAMS_SELECT_PLACEHOLDER')
        ];
        $programSelect  = HTML::selectBox([], 'programIDs', $programAttribs);

        $this->fields['filterFields']['programIDs'] = [
            'label'       => \JText::_('COM_THM_ORGANIZER_PROGRAMS'),
            'description' => \JText::_('COM_THM_ORGANIZER_PROGRAMS_EXPORT_DESC'),
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
        $fileFormats['xls.si']     = \JText::_('COM_THM_ORGANIZER_XLS_CALENDAR_BLIND');
        $fileFormats['ics']        = \JText::_('COM_THM_ORGANIZER_ICS_CALENDAR');

        if (!empty($this->compiler)) {
            $fileFormats['pdf.a3'] = \JText::_('COM_THM_ORGANIZER_PDF_A3_DOCUMENT');
            $fileFormats['pdf.a4'] = \JText::_('COM_THM_ORGANIZER_PDF_A4_DOCUMENT');
        }

        $defaultFileFormat = $seeingImpaired ? 'xls.si' : 'pdf.a4';
        $fileFormatSelect  = HTML::selectBox($fileFormats, 'format', $formatAttribs, $defaultFileFormat);

        $this->fields['formatSettings']['format'] = [
            'label'       => \JText::_('COM_THM_ORGANIZER_FILE_FORMAT'),
            'description' => \JText::_('COM_THM_ORGANIZER_FILE_FORMAT_DESC'),
            'input'       => $fileFormatSelect
        ];

        $titlesOptions      = [];
        $titlesOptions['1'] = \JText::_('COM_THM_ORGANIZER_FULL_TITLE');
        $titlesOptions['2'] = \JText::_('COM_THM_ORGANIZER_SHORT_TITLE');
        $titlesOptions['3'] = \JText::_('COM_THM_ORGANIZER_ABBREVIATION');
        $titlesSelect       = HTML::selectBox($titlesOptions, 'titles', $attribs, '1');

        $this->fields['formatSettings']['titles'] = [
            'label'       => \JText::_('COM_THM_ORGANIZER_TITLES'),
            'description' => \JText::_('COM_THM_ORGANIZER_TITLES_FORMAT_DESC'),
            'input'       => $titlesSelect
        ];

        $groupingOptions      = [];
        $groupingOptions['0'] = \JText::_('JNONE');
        $groupingOptions['1'] = \JText::_('COM_THM_ORGANIZER_BY_RESOURCE');
        $groupingSelect       = HTML::selectBox($groupingOptions, 'grouping', $attribs, '1');

        $this->fields['formatSettings']['grouping'] = [
            'label'       => \JText::_('COM_THM_ORGANIZER_GROUPING'),
            'description' => \JText::_('COM_THM_ORGANIZER_GROUPING_DESC'),
            'input'       => $groupingSelect
        ];

        $grids       = $this->model->getGridOptions();
        $defaultGrid = $this->model->defaultGrid;
        $gridSelect  = HTML::selectBox($grids, 'gridID', $attribs, $defaultGrid);

        $this->fields['formatSettings']['gridID'] = [
            'label'       => \JText::_('COM_THM_ORGANIZER_GRID'),
            'description' => \JText::_('COM_THM_ORGANIZER_GRID_EXPORT_DESC'),
            'input'       => $gridSelect
        ];

//        $displayFormats             = [];
//        $displayFormats['list']     = \JText::_('COM_THM_ORGANIZER_LIST');
//        $displayFormats['schedule'] = \JText::_('COM_THM_ORGANIZER_SCHEDULE');
//        $defaultDisplayFormat       = 'schedule';
//        $displayFormatSelect        = HTML::selectBox($displayFormats, 'displayFormat', $attribs,
//            $defaultDisplayFormat);
//
//        $this->fields['formatSettings']['displayFormat'] = [
//            'label'       => \JText::_('COM_THM_ORGANIZER_DISPLAY_FORMAT'),
//            'description' => \JText::_('COM_THM_ORGANIZER_DISPLAY_FORMAT_DESC'),
//            'input'       => $displayFormatSelect
//        ];

        // The Joomla calendar form field demands the % character before the real date format instruction values.
        $rawDateFormat = THM_OrganizerHelperComponent::getParams()->get('dateFormat');
        $today         = date('Y-m-d');

        if ($seeingImpaired) {
            $dateSelect = '<input name="date" type="date" value="' . $today . '">';
        } else {
            $dateFormat = preg_replace('/([a-zA-Z])/', "%$1", $rawDateFormat);
            $dateSelect = HTML::_('calendar', $today, 'date', 'date', $dateFormat, $attribs);
        }

        $this->fields['formatSettings']['date'] = [
            'label'       => \JText::_('JDATE'),
            'description' => \JText::_('COM_THM_ORGANIZER_DATE_DESC'),
            'input'       => $dateSelect
        ];

        $intervals             = [];
        $intervals['day']      = \JText::_('COM_THM_ORGANIZER_DAY');
        $intervals['week']     = \JText::_('COM_THM_ORGANIZER_WEEK');
        $intervals['month']    = \JText::_('COM_THM_ORGANIZER_MONTH');
        $intervals['semester'] = \JText::_('COM_THM_ORGANIZER_SEMESTER');
        $defaultInterval       = 'week';
        $intervalSelect        = HTML::selectBox($intervals, 'dateRestriction', $attribs, $defaultInterval);

        $this->fields['formatSettings']['dateRestriction'] = [
            'label'       => \JText::_('COM_THM_ORGANIZER_DATE_RESTRICTION'),
            'description' => \JText::_('COM_THM_ORGANIZER_DATE_RESTRICTION_DESC'),
            'input'       => $intervalSelect
        ];

//        $pdfWeekFormats          = [];
//        $pdfWeekFormats['stack'] = \JText::_('COM_THM_ORGANIZER_STACKED_PLANS'),;
//        $pdfWeekFormats['sequence'] = \JText::_('COM_THM_ORGANIZER_SEQUENCED_PLANS');
//        $defaultPDFWeekFormat       = 'sequence';
//
//        $pdfWeekFormatSelect = HTML::selectBox($pdfWeekFormats, 'pdfWeekFormat', $attribs, $defaultPDFWeekFormat);
//
//        $this->fields['formatSettings']['pdfWeekFormat'] = [
//            'label'       => \JText::_('COM_THM_ORGANIZER_WEEK_FORMAT'),
//            'description' => \JText::_('COM_THM_ORGANIZER_WEEK_FORMAT_PDF_DESC'),
//            'input'       => $pdfWeekFormatSelect
//        ];

        $xlsWeekFormats       = [];
        $xlsWeekFormats[]     = ['text' => \JText::_('COM_THM_ORGANIZER_ONE_WORKSHEET'), 'value' => 'sequence'];
        $xlsWeekFormats[]     = ['text' => \JText::_('COM_THM_ORGANIZER_MULTIPLE_WORKSHEETS'), 'value' => 'stack'];
        $defaultXLSWeekFormat = 'sequence';

        $xlsWeekFormatSelect = HTML::selectBox($xlsWeekFormats, 'xlsWeekFormat', $attribs, $defaultXLSWeekFormat);

        $this->fields['formatSettings']['xlsWeekFormat'] = [
            'label'       => \JText::_('COM_THM_ORGANIZER_WEEK_FORMAT'),
            'description' => \JText::_('COM_THM_ORGANIZER_WEEK_FORMAT_XLS_DESC'),
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

        $user = \JFactory::getUser();

        if (!empty($user->id)) {
            $this->fields['resourceFields']['myschedule'] = [
                'label'       => \JText::_('COM_THM_ORGANIZER_MY_SCHEDULE'),
                'description' => \JText::_('COM_THM_ORGANIZER_MY_SCHEDULE_EXPORT_DESC'),
                'input'       => '<input type="checkbox" id="myschedule" onclick="toggleMySchedule();">'
            ];
        }

        // Pools
        $poolAttribs                     = $attribs;
        $poolAttribs['data-placeholder'] = \JText::_('COM_THM_ORGANIZER_POOL_SELECT_PLACEHOLDER');
        $poolSelect                      = HTML::selectBox([], 'poolIDs', $poolAttribs);

        $this->fields['resourceFields']['poolIDs'] = [
            'label'       => \JText::_('COM_THM_ORGANIZER_POOLS'),
            'description' => \JText::_('COM_THM_ORGANIZER_POOLS_EXPORT_DESC'),
            'input'       => $poolSelect
        ];

        $privilegedAccess = THM_OrganizerHelperAccess::allowViewAccess();
        $isTeacher        = THM_OrganizerHelperTeachers::getIDFromUserData();

        if ($privilegedAccess or !empty($isTeacher)) {
            $teacherAttribs                     = $attribs;
            $teacherAttribs['data-placeholder'] = \JText::_('COM_THM_ORGANIZER_TEACHER_SELECT_PLACEHOLDER');
            $planTeacherOptions                 = $this->model->getTeacherOptions();
            $teacherSelect                      = HTML::selectBox($planTeacherOptions, 'teacherIDs', $teacherAttribs);

            $this->fields['resourceFields']['teacherIDs'] = [
                'label'       => \JText::_('COM_THM_ORGANIZER_TEACHERS'),
                'description' => \JText::_('COM_THM_ORGANIZER_TEACHERS_EXPORT_DESC'),
                'input'       => $teacherSelect
            ];
        }

        // Rooms
        $roomAttribs                     = $attribs;
        $roomAttribs['data-placeholder'] = \JText::_('COM_THM_ORGANIZER_ROOM_SELECT_PLACEHOLDER');
        $roomSelect                      = HTML::selectBox([], 'roomIDs', $roomAttribs);

        $this->fields['resourceFields']['roomIDs'] = [
            'label'       => \JText::_('COM_THM_ORGANIZER_ROOMS'),
            'description' => \JText::_('COM_THM_ORGANIZER_ROOMS_EXPORT_DESC'),
            'input'       => $roomSelect
        ];
    }
}
