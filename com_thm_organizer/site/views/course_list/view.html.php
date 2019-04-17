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

use HTML;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/courses.php';

/**
 * Class which loads data into the view output context
 */
class THM_OrganizerViewCourse_List extends \Joomla\CMS\MVC\View\HtmlView
{
    public $filters = [];

    public $items;

    public $lang;

    public $languageLinks;

    public $languageParams;

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

        $this->languageLinks  = new \JLayoutFile('language_links', JPATH_COMPONENT . '/layouts');
        $this->languageParams = ['view' => 'course_list'];
        $this->shortTag       = THM_OrganizerHelperLanguage::getShortTag();

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
        HTML::_('bootstrap.tooltip');
        HTML::_('behavior.modal');

        $document = \JFactory::getDocument();
        $document->addStyleSheet(\JUri::root() . '/components/com_thm_organizer/css/course_list.css');
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

        $defaultOptions = [0 => $lang->_('COM_THM_ORGANIZER_ALL_CAMPUSES')];
        $campusOptions  = $defaultOptions + THM_OrganizerHelperCampuses::getOptions(true);

        $selectCampus = $this->state->get('campusID');
        if (!empty($selectCampus) and !isset($campusOptions[$selectCampus])) {
            $campusOptions[$selectCampus] = THM_OrganizerHelperCampuses::getName($selectCampus);
        }

        $this->filters['campusID'] = HTML::selectBox($campusOptions, 'campusID', $attribs, $selectCampus, true);

        if (THM_OrganizerHelperCourses::authorized()) {
            $activeOptions = [
                'pending' => $lang->_('COM_THM_ORGANIZER_PENDING_COURSES'),
                'current' => $lang->_('COM_THM_ORGANIZER_CURRENT_COURSES'),
                'all'     => $lang->_('COM_THM_ORGANIZER_ALL_COURSES'),
                'expired' => $lang->_('COM_THM_ORGANIZER_EXPIRED_COURSES')
            ];

            $selectStatus            = $this->state->get('status');
            $this->filters['status'] = HTML::selectBox($activeOptions, 'status', $attribs, $selectStatus, true);

            $defaultOptions = [0 => $lang->_('COM_THM_ORGANIZER_ALL_COURSES')];
            $subjectOptions = $defaultOptions + THM_OrganizerHelperCourses::prepCourseList();

            $selectSubject              = $this->state->get('subjectID');
            $this->filters['subjectID'] = HTML::selectBox($subjectOptions, 'subjectID', $attribs, $selectSubject, true);
        }

    }
}
