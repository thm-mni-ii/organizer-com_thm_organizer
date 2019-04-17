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

use \THM_OrganizerHelperHTML as HTML;

define('K_PATH_IMAGES', JPATH_ROOT . '/components/com_thm_organizer/images/');
jimport('tcpdf.tcpdf');

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/schedule.php';

/**
 * Class loads room statistic information into the display context.
 */
class THM_OrganizerViewRoom_Statistics extends \Joomla\CMS\MVC\View\HtmlView
{
    public $fields = [];

    public $date;

    public $timePeriods;

    public $planningPeriods;

    public $departments;

    public $programs;

    public $roomIDs;

    /**
     * Sets context variables and renders the view.
     *
     * @param string $tpl template
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();

        $this->lang = THM_OrganizerHelperLanguage::getLanguage();

        $this->model = $this->getModel();

        $this->setBaseFields();
        $this->setFilterFields();

        parent::display($tpl);
    }

    /**
     * Modifies document variables and adds links to external files
     *
     * @return void
     */
    private function modifyDocument()
    {
        HTML::_('bootstrap.framework');
        HTML::_('bootstrap.tooltip');
        HTML::_('jquery.ui');
        HTML::_('behavior.calendar');
        HTML::_('formbehavior.chosen', 'select');

        $document = \JFactory::getDocument();
        $document->addScript(\JUri::root() . '/components/com_thm_organizer/js/room_statistics.js');
        $document->addStyleSheet(\JUri::root() . '/components/com_thm_organizer/css/room_statistics.css');
    }

    private function setBaseFields()
    {
        $attribs                      = [];
        $this->fields['baseSettings'] = [];

        $intervals             = [];
        $intervalAttribs       = ['onChange' => 'handleDateRestriction();'];
        $intervals['week']     = \JText::_('COM_THM_ORGANIZER_WEEK');
        $intervals['month']    = \JText::_('COM_THM_ORGANIZER_MONTH');
        $intervals['semester'] = \JText::_('COM_THM_ORGANIZER_SEMESTER');
        $intervalSelect        = HTML::selectBox($intervals, 'dateRestriction', $intervalAttribs, 'semester');

        $this->fields['baseSettings']['dateRestriction'] = [
            'label'       => \JText::_('COM_THM_ORGANIZER_DATE_RESTRICTION'),
            'description' => \JText::_('COM_THM_ORGANIZER_DATE_RESTRICTION_DESC'),
            'input'       => $intervalSelect
        ];

        // The Joomla calendar form field demands the % character before the real date format instruction values.
        $rawDateFormat = THM_OrganizerHelperComponent::getParams()->get('dateFormat');
        $dateFormat    = preg_replace('/([a-zA-Z])/', "%$1", $rawDateFormat);
        $dateSelect    = HTML::_('calendar', date('Y-m-d'), 'date', 'date', $dateFormat, $attribs);

        $this->fields['baseSettings']['date'] = [
            'label'       => \JText::_('JDATE'),
            'description' => \JText::_('COM_THM_ORGANIZER_DATE_DESC'),
            'input'       => $dateSelect
        ];

        $ppAttribs = $attribs;
        $ppOptions = $this->model->getPlanningPeriodOptions();
        $ppDefault = THM_OrganizerHelperPlanning_Periods::getCurrentID();
        $ppSelect  = HTML::selectBox($ppOptions, 'planningPeriodIDs', $ppAttribs, $ppDefault);

        $this->fields['baseSettings']['planningPeriodIDs'] = [
            'label'       => \JText::_('COM_THM_ORGANIZER_PLANNING_PERIOD'),
            'description' => \JText::_('COM_THM_ORGANIZER_ROOMS_EXPORT_DESC'),
            'input'       => $ppSelect
        ];
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

        $roomAttribs                     = $attribs;
        $roomAttribs['data-placeholder'] = \JText::_('COM_THM_ORGANIZER_ROOM_SELECT_PLACEHOLDER');
        $planRoomOptions                 = $this->model->getRoomOptions();
        $roomSelect                      = HTML::selectBox($planRoomOptions, 'roomIDs', $roomAttribs);

        $this->fields['filterFields']['roomIDs'] = [
            'label'       => \JText::_('COM_THM_ORGANIZER_ROOMS'),
            'description' => \JText::_('COM_THM_ORGANIZER_ROOMS_EXPORT_DESC'),
            'input'       => $roomSelect
        ];

        $roomTypeAttribs                     = $attribs;
        $roomTypeAttribs['onChange']         = 'repopulateRooms();';
        $roomTypeAttribs['data-placeholder'] = \JText::_('COM_THM_ORGANIZER_ROOM_TYPE_SELECT_PLACEHOLDER');
        $typeOptions                         = $this->model->getRoomTypeOptions();
        $roomTypeSelect                      = HTML::selectBox($typeOptions, 'typeIDs', $roomTypeAttribs);

        $this->fields['filterFields']['typeIDs'] = [
            'label'       => \JText::_('COM_THM_ORGANIZER_ROOM_TYPES'),
            'description' => \JText::_('COM_THM_ORGANIZER_ROOM_TYPES_EXPORT_DESC'),
            'input'       => $roomTypeSelect
        ];

        $deptAttribs                     = $attribs;
        $deptAttribs['onChange']         = 'repopulatePlanningPeriods();repopulatePrograms();repopulateRooms();';
        $deptAttribs['data-placeholder'] = \JText::_('COM_THM_ORGANIZER_DEPARTMENT_SELECT_PLACEHOLDER');
        $departmentOptions               = $this->model->getDepartmentOptions();
        $departmentSelect                = HTML::selectBox($departmentOptions, 'departmentIDs', $deptAttribs);

        $this->fields['filterFields']['departmetIDs'] = [
            'label'       => \JText::_('COM_THM_ORGANIZER_DEPARTMENTS'),
            'description' => \JText::_('COM_THM_ORGANIZER_DEPARTMENTS_EXPORT_DESC'),
            'input'       => $departmentSelect
        ];

        $programAttribs                     = $attribs;
        $programAttribs['onChange']         = 'repopulatePlanningPeriods();repopulateRooms();';
        $programAttribs['data-placeholder'] = \JText::_('COM_THM_ORGANIZER_PROGRAMS_SELECT_PLACEHOLDER');
        $planProgramOptions                 = $this->model->getProgramOptions();
        $programSelect                      = HTML::selectBox($planProgramOptions, 'programIDs', $programAttribs);

        $this->fields['filterFields']['programIDs'] = [
            'label'       => \JText::_('COM_THM_ORGANIZER_PROGRAMS'),
            'description' => \JText::_('COM_THM_ORGANIZER_PROGRAMS_EXPORT_DESC'),
            'input'       => $programSelect
        ];
    }
}
