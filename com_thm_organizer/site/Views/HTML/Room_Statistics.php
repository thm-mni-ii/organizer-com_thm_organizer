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
use Organizer\Helpers\Terms;

/**
 * Class loads room statistic information into the display context.
 */
class Room_Statistics extends BaseHTMLView
{
    public $fields = [];

    public $date;

    public $timePeriods;

    public $terms;

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

        $document = Factory::getDocument();
        $document->addScript(Uri::root() . 'components/com_thm_organizer/js/room_statistics.js');
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/room_statistics.css');
    }

    private function setBaseFields()
    {
        $attribs                      = [];
        $this->fields['baseSettings'] = [];

        $intervals             = [];
        $intervalAttribs       = ['onChange' => 'handleDateRestriction();'];
        $intervals['week']     = Languages::_('THM_ORGANIZER_WEEK');
        $intervals['month']    = Languages::_('THM_ORGANIZER_MONTH');
        $intervals['semester'] = Languages::_('THM_ORGANIZER_SEMESTER');
        $intervalSelect        = HTML::selectBox($intervals, 'dateRestriction', $intervalAttribs, 'semester');

        $this->fields['baseSettings']['dateRestriction'] = [
            'label'       => Languages::_('THM_ORGANIZER_DATE_RESTRICTION'),
            'description' => Languages::_('THM_ORGANIZER_DATE_RESTRICTION_DESC'),
            'input'       => $intervalSelect
        ];

        // The Joomla calendar form field demands the % character before the real date format instruction values.
        $rawDateFormat = OrganizerHelper::getParams()->get('dateFormat');
        $dateFormat    = preg_replace('/([a-zA-Z])/', "%$1", $rawDateFormat);
        $dateSelect    = HTML::_('calendar', date('Y-m-d'), 'date', 'date', $dateFormat, $attribs);

        $this->fields['baseSettings']['date'] = [
            'label'       => Languages::_('JDATE'),
            'description' => Languages::_('THM_ORGANIZER_DATE_DESC'),
            'input'       => $dateSelect
        ];

        $termAttribs = $attribs;
        $termOptions = $this->model->getTermOptions();
        $defaultTerm = Terms::getCurrentID();
        $termSelect  = HTML::selectBox($termOptions, 'termIDs', $termAttribs, $defaultTerm);

        $this->fields['baseSettings']['termIDs'] = [
            'label'       => Languages::_('THM_ORGANIZER_TERM'),
            'description' => Languages::_('THM_ORGANIZER_ROOMS_EXPORT_DESC'),
            'input'       => $termSelect
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
        $roomAttribs['data-placeholder'] = Languages::_('THM_ORGANIZER_SELECT_ROOMS');
        $planRoomOptions                 = $this->model->getRoomOptions();
        $roomSelect                      = HTML::selectBox($planRoomOptions, 'roomIDs', $roomAttribs);

        $this->fields['filterFields']['roomIDs'] = [
            'label'       => Languages::_('THM_ORGANIZER_ROOMS'),
            'description' => Languages::_('THM_ORGANIZER_ROOMS_EXPORT_DESC'),
            'input'       => $roomSelect
        ];

        $roomTypeAttribs                     = $attribs;
        $roomTypeAttribs['onChange']         = 'repopulateRooms();';
        $roomTypeAttribs['data-placeholder'] = Languages::_('THM_ORGANIZER_SELECT_ROOM_TYPES');
        $typeOptions                         = $this->model->getRoomTypeOptions();
        $roomTypeSelect                      = HTML::selectBox($typeOptions, 'typeIDs', $roomTypeAttribs);

        $this->fields['filterFields']['typeIDs'] = [
            'label'       => Languages::_('THM_ORGANIZER_ROOM_TYPES'),
            'description' => Languages::_('THM_ORGANIZER_ROOM_TYPES_EXPORT_DESC'),
            'input'       => $roomTypeSelect
        ];

        $deptAttribs                     = $attribs;
        $deptAttribs['onChange']         = 'repopulateTerms();repopulateCategories();repopulateRooms();';
        $deptAttribs['data-placeholder'] = Languages::_('THM_ORGANIZER_SELECT_DEPARTMENT');
        $departmentOptions               = $this->model->getDepartmentOptions();
        $departmentSelect                = HTML::selectBox($departmentOptions, 'departmentIDs', $deptAttribs);

        $this->fields['filterFields']['departmetIDs'] = [
            'label'       => Languages::_('THM_ORGANIZER_DEPARTMENTS'),
            'description' => Languages::_('THM_ORGANIZER_DEPARTMENTS_EXPORT_DESC'),
            'input'       => $departmentSelect
        ];

        $categoryAttribs                     = $attribs;
        $categoryAttribs['onChange']         = 'repopulateTerms();repopulateRooms();';
        $categoryAttribs['data-placeholder'] = Languages::_('THM_ORGANIZER_SELECT_CATEGORY');
        $categoryOptions                     = $this->model->getCategoryOptions();
        $categorySelect                      = HTML::selectBox($categoryOptions, 'categoryIDs', $categoryAttribs);

        $this->fields['filterFields']['categoryIDs'] = [
            'label'       => Languages::_('THM_ORGANIZER_CATEGORIES'),
            'description' => Languages::_('THM_ORGANIZER_CATEGORIES_TITLE'),
            'input'       => $categorySelect
        ];
    }
}
