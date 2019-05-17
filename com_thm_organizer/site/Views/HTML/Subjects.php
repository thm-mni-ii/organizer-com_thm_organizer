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

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Access;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class loads persistent information a filtered set of subjects into the display context.
 */
class Subjects extends ListView
{
    const ALPHA = 0;

    const COORDINATES = 1;

    const NUMBER = 1;

    const POOL = 2;

    const TEACHER = 3;

    const TEACHES = 2;

    private $administration = true;

    private $documentAccess = false;

    private $params = null;

    /**
     * Constructor
     *
     * @param array $config A named configuration array for object construction.
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->params = OrganizerHelper::getParams();
    }

    /**
     * Sets Joomla view title and action buttons
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_SUBJECTS_TITLE'), 'book');
        if ($this->documentAccess) {
            $toolbar = Toolbar::getInstance();
            $toolbar->appendButton('Standard', 'new', 'THM_ORGANIZER_ADD', 'subject.add', false);
            $toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'subject.edit', true);
            $toolbar->appendButton(
                'Standard', 'upload', Languages::_('THM_ORGANIZER_IMPORT_LSF'), 'subject.importLSFData', true
            );
            $toolbar->appendButton(
                'Confirm', Languages::_('THM_ORGANIZER_DELETE_CONFIRM'), 'delete',
                Languages::_('THM_ORGANIZER_DELETE'), 'subject.delete', true
            );

            if (OrganizerHelper::getApplication()->isClient('administrator') and Access::isAdmin()) {
                HTML::setPreferencesButton();
            }
        }
    }

    /**
     * Function determines whether the user may access the view.
     *
     * @return bool true if the use may access the view, otherwise false
     */
    protected function allowAccess()
    {
        $this->administration = OrganizerHelper::getApplication()->isClient('administrator');
        $this->documentAccess = Access::allowDocumentAccess();

        return $this->administration ? $this->documentAccess : true;
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $direction = $this->state->get('list.direction');
        $ordering  = $this->state->get('list.ordering');
        $grouping  = $this->state->get('list.grouping');
        $headers   = [];

        $headers['checkbox'] = '';
        if ($this->administration) {
            $headers['name'] = HTML::sort('NAME', 'name', $direction, $ordering);
        } else {
            $headers['name'] = Languages::_('THM_ORGANIZER_NAME');
        }

        if ($grouping == self::TEACHER) {
            $headers['responsibility'] = Languages::_('THM_ORGANIZER_RESPONSIBILITY');
        } else {
            $headers['teachers'] = Languages::_('THM_ORGANIZER_TEACHERS');
        }
        $headers['creditpoints'] = Languages::_('THM_ORGANIZER_CREDIT_POINTS');

        return $headers;
    }

    /**
     * Retrieves the teacher texts and formats them according to their responisibilites for the subject being iterated
     *
     * @param object $subject the subject being iterated
     *
     * @return string
     */
    private function getTeacherDisplay($subject)
    {
        $names = [];
        foreach ($subject->teachers as $teacherID => $teacher) {
            $name = $this->getTeacherText($teacher);

            $responsibilities = [];
            if (isset($teacher['teacherResp'][self::COORDINATES])) {
                $responsibilities[] = Languages::_('THM_ORGANIZER_COORDINATOR_ABBR');
            }
            if (isset($teacher['teacherResp'][self::TEACHES])) {
                $responsibilities[] = Languages::_('THM_ORGANIZER_TEACHER_ABBR');
            }

            $name .= ' (' . implode(', ', $responsibilities) . ')';
            $names[] = $name;
        }

        return implode('<br>', $names);
    }

    /**
     * Generates the teacher text (surname(, forename)?( title)?) for the given teacher
     *
     * @param array $teacher the subject teacher
     *
     * @return string
     */
    public function getTeacherText($teacher)
    {
        $showTitle = (bool)$this->params->get('showTitle');

        $text = $teacher['surname'];

        if (!empty($teacher['forename'])) {
            $text .= ", {$teacher['forename']}";
        }

        if ($showTitle and !empty($teacher['title'])) {
            $text .= " {$teacher['title']}";
        }

        return $text;
    }

    /**
     * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
     *
     * @return void processes the class items property
     */
    protected function preProcessItems()
    {
        if (empty($this->items)) {
            return;
        }

        $editIcon       = '<span class="icon-edit"></span>';
        $grouping       = $this->administration ? '0' : $this->state->get('list.grouping');
        $index          = 0;
        $processedItems = [];

        foreach ($this->items as $subject) {
            $access   = Access::allowSubjectAccess($subject->id);
            $editLink = $subject->url . '&view=subject_edit';
            if ($grouping == '1') {
                $name = empty($subject->externalID) ? '' : "$subject->externalID - ";
                $name .= $subject->name;
            } else {
                $name = $subject->name;
                $name .= empty($subject->externalID) ? '' : " ($subject->externalID)";
            }
            $itemLink               = HTML::_('link', $subject->url . '&view=subject_details', $name);
            $processedItems[$index] = [];

            if ($access) {
                $processedItems[$index]['checkbox'] = HTML::_('grid.id', $index, $subject->id);
                $processedItems[$index]['name']     = $this->administration ?
                    HTML::_('link', $editLink, $name) : $itemLink . HTML::_('link', $editLink, $editIcon);
            } else {
                $processedItems[$index]['checkbox'] = '';
                $processedItems[$index]['name']     = $itemLink;
            }

            if ($grouping == self::TEACHER) {
                $processedItems[$index]['responsibility'] = 'responsibility display';
            } else {
                $processedItems[$index]['teachers'] = $this->getTeacherDisplay($subject);
            }
            $processedItems[$index]['creditpoints'] = empty($subject->creditpoints) ? '' : $subject->creditpoints;

            $index++;
        }

        $this->items = $processedItems;
    }
}
