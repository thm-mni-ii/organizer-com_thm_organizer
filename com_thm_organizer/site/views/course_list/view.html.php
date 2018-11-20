<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/courses.php';

/**
 * Class which loads data into the view output context
 */
class THM_OrganizerViewCourse_List extends JViewLegacy
{
    public $filters = [];

    public $items;

    public $lang;

    public $languageSwitches;

    public $model = null;

    public $shortTag;

    public $showFilters;

    public $state = null;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();
        $this->model = $this->getModel();
        $this->state = $this->model->getState();

        $this->lang  = THM_OrganizerHelperLanguage::getLanguage();
        $this->items = $this->get('Items');

        // alphabetically sort by course name and campus name
        uasort($this->items, function ($courseOne, $courseTwo) {
            if ($courseOne->name == $courseTwo->name) {
                if ($courseOne->campus['name'] == $courseTwo->campus['name']) {
                    return $courseOne->start < $courseTwo->start;
                }

                return $courseOne->campus['name'] > $courseTwo->campus['name'];
            }

            return $courseOne->name > $courseTwo->name;
        });

        $params                 = ['view' => 'course_list'];
        $this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($params);
        $this->shortTag         = THM_OrganizerHelperLanguage::getShortTag();

        $this->setFilters();

        parent::display($tpl);
    }

    /**
     * Adds resource files to the document
     *
     * @return void
     */
    private function modifyDocument()
    {
        JHtml::_('bootstrap.tooltip');
        JHTML::_('behavior.modal');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/course_list.css');
    }

    /**
     * Sets various filter elements
     *
     * @return void  sets the filter object variables
     */
    private function setFilters()
    {
        $lang    = THM_OrganizerHelperLanguage::getLanguage();
        $attribs = ['onchange' => 'form.submit();'];

        $default       = [0 => $lang->_('COM_THM_ORGANIZER_ALL_CAMPUSES')];
        $campusOptions = THM_OrganizerHelperCampuses::getOptions(true);
        unset($campusOptions[0]);

        if (!empty($this->state->get('campusID')) and !isset($campusOptions[$this->state->get('campusID')])) {
            $campusOptions[$this->state->get('campusID')] = THM_OrganizerHelperCampuses::getName($this->state->get('campusID'));
        }

        $this->filters['campusID'] = THM_OrganizerHelperComponent::selectBox(
            $campusOptions,
            'campusID',
            $attribs,
            $this->state->get('campusID'),
            $default
        );

        if (THM_OrganizerHelperCourses::authorized()) {
            $activeOptions = [
                'pending' => $lang->_('COM_THM_ORGANIZER_PENDING_COURSES'),
                'current' => $lang->_('COM_THM_ORGANIZER_CURRENT_COURSES'),
                'all'     => $lang->_('COM_THM_ORGANIZER_ALL_COURSES'),
                'expired' => $lang->_('COM_THM_ORGANIZER_EXPIRED_COURSES')
            ];

            $this->filters['status'] = THM_OrganizerHelperComponent::selectBox(
                $activeOptions,
                'status',
                $attribs,
                $this->state->get('status')
            );

            $subjectOptions = THM_OrganizerHelperCourses::prepCourseList();
            $default        = [0 => $lang->_('COM_THM_ORGANIZER_ALL_COURSES')];

            $this->filters['subjectID'] = THM_OrganizerHelperComponent::selectBox(
                $subjectOptions,
                'subjectID',
                $attribs,
                $this->state->get('subjectID'),
                $default
            );
        }

    }
}
