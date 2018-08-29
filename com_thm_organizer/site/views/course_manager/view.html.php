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
defined('_JEXEC') or die;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/campuses.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/component.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/courses.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class loads persistent information about a course into the display context.
 */
class THM_OrganizerViewCourse_Manager extends JViewLegacy
{
    public $course;

    public $courseAuth = false;

    public $form;

    public $lang;

    public $languageSwitches;

    public $menu;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $this->lang = THM_OrganizerHelperLanguage::getLanguage();
        $lessonID   = JFactory::getApplication()->input->getInt("lessonID", 0);

        if (empty($lessonID) or !THM_OrganizerHelperCourses::authorized($lessonID)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_401'), 401);
        }

        $this->course                 = THM_OrganizerHelperCourses::getCourse();
        $courseID                     = empty($this->course) ? 0 : $this->course["id"];
        $this->course['campus']       = THM_OrganizerHelperCourses::getCampus($this->course);
        $this->course['participants'] = THM_OrganizerHelperCourses::getParticipants($courseID);
        $this->course['dateText']     = THM_OrganizerHelperCourses::getDateDisplay();

        $maxParticipants              = (!empty($this->course["lessonP"]) ? $this->course["lessonP"] : $this->course["subjectP"]);
        $accepted                     = count(THM_OrganizerHelperCourses::getParticipants($courseID, 1));
        $waiting                      = count(THM_OrganizerHelperCourses::getParticipants($courseID, 0));
        $capacityText                 = $this->lang->_('COM_THM_ORGANIZER_CURRENT_CAPACITY');
        $this->course['capacityText'] = sprintf($capacityText, $accepted, $maxParticipants, $waiting);

        $this->form = $this->get('Form');
        $this->form->setValue('id', null, $courseID);

        $this->prepareLabel('campusID', 'location');
        $this->form->setValue('campusID', null, $this->course['campus']['id']);
        $this->prepareLabel('max_participants', 'users');
        $this->form->setValue('max_participants', null, $maxParticipants);
        $this->prepareLabel('deadline', 'signup');
        $this->form->setValue('deadline', null, $this->course['deadline']);
        $this->prepareLabel('fee', 'info-euro');
        $this->form->setValue('fee', null, $this->course['fee']);
        $this->translateOptions('includeWaitList');
        $this->prepareLabel('subject');
        $this->prepareLabel('text');

        $params                 = ['view' => 'course_manager', 'lessonID' => $courseID];
        $this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($params);
        $this->modifyDocument();
        THM_OrganizerHelperComponent::addMenuParameters($this);

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

        $document = JFactory::getDocument();
        $document->addScriptDeclaration("var chooseParticipants = '" . $this->lang->_('COM_THM_ORGANIZER_CHOOSE_PARTICIPANTS') . "'");
        $document->addScript(JUri::root() . '/media/com_thm_organizer/js/course_manager.js');
        $document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/course_manager.css');
    }

    /**
     * Translates form fields, and adds icons to them as requested.
     *
     * @param string $field the name of the form field
     * @param string $icon  the specific icon name to add to the text output
     *
     * @return void modifies attributes subordinate to the form variable
     */
    private function prepareLabel($field, $icon = '')
    {
        $title = $this->lang->_($this->form->getFieldAttribute($field, 'label'));

        if (empty($icon)) {
            $this->form->setFieldAttribute($field, 'label', $title);
        } else {
            $iconHTML  = '<span class="icon-' . $icon . '"></span>';
            $titleHTML = '<span class="si-title">' . $title . '</span>';
            $this->form->setFieldAttribute($field, 'label', $iconHTML . $titleHTML);
        }
        $description = $this->lang->_($this->form->getFieldAttribute($field, 'description'));
        $this->form->setFieldAttribute($field, 'description', $description);

    }

    /**
     * Translates the field options for the given field according to the language selected by the user.
     *
     * @param string $fieldName the name of the field
     *
     * @return void modifies the form
     */
    private function translateOptions($fieldName)
    {
        $field = $this->form->getFieldXML($fieldName);
        $index = 0;
        foreach ($field->option as $option) {
            $field->option[$index] = $this->lang->_($option[0]);
            $index++;
        }
        $this->form->setField($field, null, true);
    }
}