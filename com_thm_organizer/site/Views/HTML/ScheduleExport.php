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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers as Helpers;

/**
 * Class loads the schedule export filter form into the display context.
 */
class ScheduleExport extends SelectionView
{
    /**
     * Modifies document variables and adds links to external files
     *
     * @return void
     */
    protected function modifyDocument()
    {
        parent::modifyDocument();

        $document = Factory::getDocument();
        $user     = Factory::getUser();

        if ($user->id) {
            $auth = urlencode(password_hash($user->email . $user->registerDate, PASSWORD_BCRYPT));
            $document->addScriptDeclaration("const username = '$user->username', auth = '$auth';");
        }

        // ToDo: make this default/chosen format dependent, not seeing impaired
        if ($this->isSeeingImpaired()) {
            //$this->hiddenFields = ['format', 'pdfWeekFormat', 'displayFormat'];
        } else {
            //$this->hiddenFields = ['xlsWeekFormat', 'grouping'];
        }

        $document->addScript(Uri::root() . 'components/com_thm_organizer/js/schedule_export.js');
    }

    /**
     * Sets form fields used to define the content of the exported schedule.
     *
     * @return void modifies the sets property
     */
    private function setContentFields()
    {
        $this->sets['content'] = ['label' => 'THM_ORGANIZER_CONTENT_SETTINGS'];

        $attribs = ['multiple' => 'multiple'];

        $this->setResourceField('group', 'content', $attribs, false);

        $user = Factory::getUser();
        $showTeachers = ($user->id and (Helpers\Access::allowViewAccess() or Helpers\Teachers::getIDByUserID()));
        if ($showTeachers) {
            $this->setResourceField('teacher', 'content', $attribs, false);
        }

        $this->setResourceField('room', 'content', $attribs, false);

    }

    /**
     * Sets form fields used to define the way in which the schedule is displayed.
     *
     * @return void modifies the sets property
     */
    private function setDisplayFields()
    {
        $this->sets['display'] = ['label' => 'THM_ORGANIZER_DISPLAY_SETTINGS'];

        $formatAttributes = ['onChange' => 'setFormat();'];

        $titlesFormats = [
            'full'        => 'THM_ORGANIZER_FULL_NAMES',
            'short'       => 'THM_ORGANIZER_SHORT_NAMES',
            'abbreviated' => 'THM_ORGANIZER_ABBREVIATIONS'
        ];

        $this->setListField('titles', 'display', $titlesFormats, [], 'full');
        $this->setResourceField('grid', 'display', [], true);

        $date = '<input name="date" type="date" value="' . date('Y-m-d') . '">';
        $this->setField('date', 'display', 'THM_ORGANIZER_DATE', $date);

        $intervals = [
            'day'      => 'THM_ORGANIZER_DAY',
            'week'     => 'THM_ORGANIZER_WEEK',
            'month'    => 'THM_ORGANIZER_MONTH',
            'semester' => 'THM_ORGANIZER_SEMESTER'
        ];

        $this->setListField('interval', 'display', $intervals, $formatAttributes, 'week');
    }

    /**
     * Sets form fields used to filter the resources available for selection.
     *
     * @return void modifies the sets property
     */
    private function setFilterFields()
    {
        $this->sets['filters'] = ['label' => 'THM_ORGANIZER_FILTERS'];

        $deptAttribs = ['onChange' => 'repopulateCategories();repopulateResources();'];
        $this->setResourceField('department', 'filters', $deptAttribs, true);

        $categoryAttribs = ['multiple' => 'multiple', 'onChange' => 'repopulateResources();'];
        $this->setResourceField('category', 'filters', $categoryAttribs);
    }

    /**
     * Sets form fields used to define the format used for the export.
     *
     * @return void modifies the sets property
     */
    private function setFormatFields()
    {
        $this->sets['format'] = ['label' => 'THM_ORGANIZER_FORMAT_SETTINGS'];
        $formatAttributes     = ['onChange' => 'setFormat();'];

        $fileTypes   = [
            'ics' => 'THM_ORGANIZER_ICS_CALENDAR',
            'pdf' => 'THM_ORGANIZER_PDF_DOCUMENT',
            'xls' => 'THM_ORGANIZER_XLS_WORKBOOK'
        ];
        $defaultType = $this->isSeeingImpaired() ? 'xls' : 'pdf';

        $this->setListField('format', 'format', $fileTypes, $formatAttributes, $defaultType);

        $pdfFormats = [
            'a3' => 'THM_ORGANIZER_ICS_CALENDAR',
            'a4' => 'THM_ORGANIZER_PDF_DOCUMENT'
        ];

        $this->setListField('pdfFormat', 'format', $pdfFormats, $formatAttributes, 'a4');

        $grouping = [
            'none'       => 'THM_ORGANIZER_NO_GROUPING',
            'byresource' => 'THM_ORGANIZER_GROUPED_BY_RESOURCE'
        ];

        $this->setListField('grouping', 'format', $grouping, [], 'none');

        $sheets = [
            'collected'  => 'THM_ORGANIZER_ON_ONE_WORKSHEET',
            'individual' => 'THM_ORGANIZER_ON_INDIVIDUAL_WORKSHEETS'
        ];

        $this->setListField('xlsFormat', 'format', $sheets, [], 'collected');
    }

    /**
     * Sets form fields used to define the content of the exported schedule.
     *
     * @return void modifies the sets property
     */
    private function setPersonalFields()
    {
        $this->sets['personal'] = ['label' => 'THM_ORGANIZER_MY_PLANS'];

        $myScheduleField = '<input type="checkbox" id="myschedule" onclick="toggleMySchedule();">';
        $this->setField('myschedule', 'personal', 'MY_SCHEDULE', $myScheduleField);

        if (Helpers\Teachers::getIDByUserID()) {
            $teacherField = '<input type="checkbox" id="myschedule" onclick="toggleMySchedule();">';
            $this->setField('myteachingschedule', 'personal', 'MY_TEACHING_SCHEDULE', $teacherField);
        }
    }

    /**
     * Function to define field sets and fill sets with fields
     *
     * @return void sets the fields property
     */
    protected function setSets()
    {
        $user = Factory::getUser();

        if (!empty($user->id)) {
            $this->setPersonalFields();
        }
        $this->setFilterFields();
        $this->setContentFields();
        $this->setDisplayFields();
        $this->setFormatFields();
    }
}
