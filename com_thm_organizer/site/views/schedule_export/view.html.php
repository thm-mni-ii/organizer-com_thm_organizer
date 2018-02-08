<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        thm_organizerViewSchedule_Export
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/schedule.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * View class for the display of schedules
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewSchedule_Export extends JViewLegacy
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

        $this->lang = THM_OrganizerHelperLanguage::getLanguage();

        $this->model    = $this->getModel();
        $this->compiler = jimport('tcpdf.tcpdf');

        $this->setAdminFields();
        $this->setFilterFields();
        $this->setFormatFields();
        $this->setResourceFields();

        parent::display($tpl);
    }

    /**
     * Modifies document variables and adds links to external files
     *
     * @return void
     */
    private function modifyDocument()
    {
        $app            = JFactory::getApplication();
        $activeMenu     = (!empty($app->getMenu()) and !empty($app->getMenu()->getActive()));
        $seeingImpaired = (bool)($activeMenu and $app->getMenu()->getActive()->params->get('si', false));

        if (empty($seeingImpaired)) {
            JHtml::_('bootstrap.framework');
            JHtml::_('bootstrap.tooltip');
            JHtml::_('jquery.ui');
            JHtml::_('behavior.calendar');
            JHtml::_('formbehavior.chosen', 'select');
            $this->setLayout('default');
        } else {
            $this->setLayout('default_si');
        }

        $document = JFactory::getDocument();
        $document->addScript(JUri::root() . '/media/com_thm_organizer/js/schedule_export.js');
        $document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/schedule_export.css');
    }

    /**
     * Creates format settings fields for the form
     *
     * @return void sets indexes in $this->fields['formatSettings'] with html content
     */
    private function setAdminFields()
    {
        $allowedIDs = THM_OrganizerHelperComponent::getAccessibleDepartments();

        if (!empty($allowedIDs)) {
            $this->fields['adminFields']['showUnpublished'] = [
                'label'       => JText::_('COM_THM_ORGANIZER_SHOW_UNPUBLISHED'),
                'description' => JText::_('COM_THM_ORGANIZER_SHOW_UNPUBLISHED_DESC'),
                'input'       => '<input type="checkbox" id="showUnpublished" name="showUnpublished">'
            ];
        }
    }

    /**
     * Creates resource selection fields for the form
     *
     * @return void sets indexes in $this->fields['resouceSettings'] with html content
     */
    private function setFilterFields()
    {
        $this->fields['filterFields'] = [];
        $attribs                      = ['multiple' => 'multiple'];

        // Departments
        $deptAttribs                                  = $attribs;
        $deptAttribs['onChange']                      = 'repopulatePrograms();repopulateResources();';
        $deptAttribs['data-placeholder']              = JText::_('COM_THM_ORGANIZER_DEPARTMENT_SELECT_PLACEHOLDER');
        $planDepartmentOptions                        = $this->model->getDepartmentOptions();
        $departmentSelect                             = JHtml::_('select.genericlist', $planDepartmentOptions,
            'departmentIDs[]', $deptAttribs, 'value', 'text');
        $this->fields['filterFields']['departmetIDs'] = [
            'label'       => JText::_('COM_THM_ORGANIZER_DEPARTMENTS'),
            'description' => JText::_('COM_THM_ORGANIZER_DEPARTMENTS_EXPORT_DESC'),
            'input'       => $departmentSelect
        ];

        // Programs
        $programAttribs                             = $attribs;
        $programAttribs['onChange']                 = 'repopulateResources();';
        $programAttribs['data-placeholder']         = JText::_('COM_THM_ORGANIZER_PROGRAMS_SELECT_PLACEHOLDER');
        $planProgramOptions                         = $this->model->getProgramOptions();
        $programSelect                              = JHtml::_('select.genericlist', $planProgramOptions,
            'programIDs[]', $programAttribs, 'value', 'text');
        $this->fields['filterFields']['programIDs'] = [
            'label'       => JText::_('COM_THM_ORGANIZER_PROGRAMS'),
            'description' => JText::_('COM_THM_ORGANIZER_PROGRAMS_EXPORT_DESC'),
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
        $seeingImpaired                 = (bool)JFactory::getApplication()->getMenu()->getActive()->params->get('si',
            false);

        $formatAttribs             = $attribs;
        $formatAttribs['onChange'] = 'setFormat();';
        $fileFormats               = [];
        $fileFormats[]             = ['text' => JText::_('COM_THM_ORGANIZER_XLS_CALENDAR_BLIND'), 'value' => 'xls.si'];
        $fileFormats[]             = ['text' => JText::_('COM_THM_ORGANIZER_ICS_CALENDAR'), 'value' => 'ics'];

        if (!empty($this->compiler)) {
            $fileFormats[] = ['text' => JText::_('COM_THM_ORGANIZER_PDF_A3_DOCUMENT'), 'value' => 'pdf.a3'];
            $fileFormats[] = ['text' => JText::_('COM_THM_ORGANIZER_PDF_A4_DOCUMENT'), 'value' => 'pdf.a4'];
        }

        $defaultFileFormat = $seeingImpaired ? 'xls.si' : 'pdf.a4';
        $fileFormatSelect  = JHtml::_('select.genericlist', $fileFormats, 'format', $formatAttribs, 'value', 'text',
            $defaultFileFormat);

        $this->fields['formatSettings']['format'] = [
            'label'       => JText::_('COM_THM_ORGANIZER_FILE_FORMAT'),
            'description' => JText::_('COM_THM_ORGANIZER_FILE_FORMAT_DESC'),
            'input'       => $fileFormatSelect
        ];

        $titlesOptions   = [];
        $titlesOptions[] = ['text' => JText::_('COM_THM_ORGANIZER_FULL_TITLE'), 'value' => '1'];
        $titlesOptions[] = ['text' => JText::_('COM_THM_ORGANIZER_SHORT_TITLE'), 'value' => '2'];
        $titlesOptions[] = ['text' => JText::_('COM_THM_ORGANIZER_ABBREVIATION'), 'value' => '3'];
        $titlesSelect    =
            JHtml::_('select.genericlist', $titlesOptions, 'titles', $attribs, 'value', 'text', '1');

        $this->fields['formatSettings']['titles'] = [
            'label'       => JText::_('COM_THM_ORGANIZER_TITLES'),
            'description' => JText::_('COM_THM_ORGANIZER_TITLES_FORMAT_DESC'),
            'input'       => $titlesSelect
        ];

        $groupingOptions   = [];
        $groupingOptions[] = ['text' => JText::_('JNONE'), 'value' => '0'];
        $groupingOptions[] = ['text' => JText::_('COM_THM_ORGANIZER_BY_RESOURCE'), 'value' => '1'];
        $groupingSelect    =
            JHtml::_('select.genericlist', $groupingOptions, 'grouping', $attribs, 'value', 'text', '1');

        $this->fields['formatSettings']['grouping'] = [
            'label'       => JText::_('COM_THM_ORGANIZER_GROUPING'),
            'description' => JText::_('COM_THM_ORGANIZER_GROUPING_DESC'),
            'input'       => $groupingSelect
        ];

        $grids       = $this->model->getGridOptions();
        $defaultGrid = $this->model->defaultGrid;
        $gridSelect  = JHtml::_('select.genericlist', $grids, 'gridID', $attribs, 'value', 'text', $defaultGrid);

        $this->fields['formatSettings']['gridID'] = [
            'label'       => JText::_('COM_THM_ORGANIZER_GRID'),
            'description' => JText::_('COM_THM_ORGANIZER_GRID_EXPORT_DESC'),
            'input'       => $gridSelect
        ];

        $displayFormats = [];
        //$displayFormats[] = ['text' => JText::_('COM_THM_ORGANIZER_LIST'), 'value' => 'list'];
        $displayFormats[]     = ['text' => JText::_('COM_THM_ORGANIZER_SCHEDULE'), 'value' => 'schedule'];
        $defaultDisplayFormat = 'schedule';
        $displayFormatSelect  = JHtml::_('select.genericlist', $displayFormats, 'displayFormat', $attribs, 'value',
            'text', $defaultDisplayFormat);

        $this->fields['formatSettings']['displayFormat'] = [
            'label'       => JText::_('COM_THM_ORGANIZER_DISPLAY_FORMAT'),
            'description' => JText::_('COM_THM_ORGANIZER_DISPLAY_FORMAT_DESC'),
            'input'       => $displayFormatSelect
        ];

        // The Joomla calendar form field demands the % character before the real date format instruction values.
        $rawDateFormat = JFactory::getApplication()->getParams()->get('dateFormat');
        $today         = date('Y-m-d');

        if ($seeingImpaired) {
            $dateSelect = '<input name="date" type="date" value="' . $today . '">';
        } else {
            $dateFormat = preg_replace("/([a-zA-Z])/", "%$1", $rawDateFormat);
            $dateSelect = JHtml::_('calendar', $today, 'date', 'date', $dateFormat, $attribs);
        }

        $this->fields['formatSettings']['date'] = [
            'label'       => JText::_('JDATE'),
            'description' => JText::_('COM_THM_ORGANIZER_DATE_DESC'),
            'input'       => $dateSelect
        ];

        $dateRestrictions       = [];
        $dateRestrictions[]     = ['text' => JText::_('COM_THM_ORGANIZER_DAY'), 'value' => 'day'];
        $dateRestrictions[]     = ['text' => JText::_('COM_THM_ORGANIZER_WEEK'), 'value' => 'week'];
        $dateRestrictions[]     = ['text' => JText::_('COM_THM_ORGANIZER_MONTH'), 'value' => 'month'];
        $dateRestrictions[]     = ['text' => JText::_('COM_THM_ORGANIZER_SEMESTER'), 'value' => 'semester'];
        $defaultDateRestriction = 'week';
        $dateRestrictionSelect  = JHtml::_('select.genericlist', $dateRestrictions, 'dateRestriction', $attribs,
            'value', 'text', $defaultDateRestriction);

        $this->fields['formatSettings']['dateRestriction'] = [
            'label'       => JText::_('COM_THM_ORGANIZER_DATE_RESTRICTION'),
            'description' => JText::_('COM_THM_ORGANIZER_DATE_RESTRICTION_DESC'),
            'input'       => $dateRestrictionSelect
        ];

        // TODO: Add grid selection here

        $pdfWeekFormats = [];
        //$pdfWeekFormats[] = ['text' => JText::_('COM_THM_ORGANIZER_STACKED_PLANS'), 'value' => 'stack'];
        $pdfWeekFormats[]     = ['text' => JText::_('COM_THM_ORGANIZER_SEQUENCED_PLANS'), 'value' => 'sequence'];
        $defaultPDFWeekFormat = 'sequence';
        $pdfWeekFormatSelect  = JHtml::_('select.genericlist', $pdfWeekFormats, 'pdfWeekFormat', $attribs, 'value',
            'text', $defaultPDFWeekFormat);

        $this->fields['formatSettings']['pdfWeekFormat'] = [
            'label'       => JText::_('COM_THM_ORGANIZER_WEEK_FORMAT'),
            'description' => JText::_('COM_THM_ORGANIZER_WEEK_FORMAT_PDF_DESC'),
            'input'       => $pdfWeekFormatSelect
        ];

        $xlsWeekFormats       = [];
        $xlsWeekFormats[]     = ['text' => JText::_('COM_THM_ORGANIZER_ONE_WORKSHEET'), 'value' => 'sequence'];
        $xlsWeekFormats[]     = ['text' => JText::_('COM_THM_ORGANIZER_MULTIPLE_WORKSHEETS'), 'value' => 'stack'];
        $defaultXLSWeekFormat = 'sequence';
        $xlsWeekFormatSelect  = JHtml::_('select.genericlist', $xlsWeekFormats, 'xlsWeekFormat', $attribs, 'value',
            'text', $defaultXLSWeekFormat);

        $this->fields['formatSettings']['xlsWeekFormat'] = [
            'label'       => JText::_('COM_THM_ORGANIZER_WEEK_FORMAT'),
            'description' => JText::_('COM_THM_ORGANIZER_WEEK_FORMAT_XLS_DESC'),
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
        $attribs                        = ['multiple' => 'multiple'];

        $user = JFactory::getUser();

        if (!empty($user->id)) {
            $this->fields['resourceFields']['myschedule'] = [
                'label'       => JText::_('COM_THM_ORGANIZER_MY_SCHEDULE'),
                'description' => JText::_('COM_THM_ORGANIZER_MY_SCHEDULE_EXPORT_DESC'),
                'input'       => '<input type="checkbox" id="myschedule" onclick="toggleMySchedule();">'
            ];
        }

        // Pools
        $poolAttribs                               = $attribs;
        $poolAttribs['data-placeholder']           = JText::_('COM_THM_ORGANIZER_POOL_SELECT_PLACEHOLDER');
        $planPoolOptions                           = $this->model->getPoolOptions();
        $poolSelect                                = JHtml::_('select.genericlist', $planPoolOptions, 'poolIDs[]',
            $poolAttribs, 'value', 'text');
        $this->fields['resourceFields']['poolIDs'] = [
            'label'       => JText::_('COM_THM_ORGANIZER_POOLS'),
            'description' => JText::_('COM_THM_ORGANIZER_POOLS_EXPORT_DESC'),
            'input'       => $poolSelect
        ];

        // Teachers
        $teacherAttribs                               = $attribs;
        $teacherAttribs['data-placeholder']           = JText::_('COM_THM_ORGANIZER_TEACHER_SELECT_PLACEHOLDER');
        $planTeacherOptions                           = $this->model->getTeacherOptions();
        $teacherSelect                                = JHtml::_('select.genericlist', $planTeacherOptions,
            'teacherIDs[]', $teacherAttribs, 'value', 'text');
        $this->fields['resourceFields']['teacherIDs'] = [
            'label'       => JText::_('COM_THM_ORGANIZER_TEACHERS'),
            'description' => JText::_('COM_THM_ORGANIZER_TEACHERS_EXPORT_DESC'),
            'input'       => $teacherSelect
        ];

        // Rooms
        $roomAttribs                               = $attribs;
        $roomAttribs['data-placeholder']           = JText::_('COM_THM_ORGANIZER_ROOM_SELECT_PLACEHOLDER');
        $planRoomOptions                           = $this->model->getRoomOptions();
        $roomSelect                                = JHtml::_('select.genericlist', $planRoomOptions, 'roomIDs[]',
            $roomAttribs, 'value', 'text');
        $this->fields['resourceFields']['roomIDs'] = [
            'label'       => JText::_('COM_THM_ORGANIZER_ROOMS'),
            'description' => JText::_('COM_THM_ORGANIZER_ROOMS_EXPORT_DESC'),
            'input'       => $roomSelect
        ];
    }
}
