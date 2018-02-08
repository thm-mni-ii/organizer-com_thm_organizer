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

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/campuses.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
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

    public $capacityText;

    public $dateText = "";

    public $form;

    public $lang;

    public $languageSwitches;

    public $menu;

    public $participants;

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

        $this->course       = THM_OrganizerHelperCourses::getCourse();
        $courseID           = empty($this->course) ? 0 : $this->course["id"];
        $this->participants = THM_OrganizerHelperCourses::getParticipants($courseID);
        $this->form         = $this->get('Form');
        $this->form->setValue('id', null, $this->course['id']);
        $this->dateText = THM_OrganizerHelperCourses::getDateDisplay();

        $allowedParticipants = (!empty($this->course["lessonP"]) ? $this->course["lessonP"] : $this->course["subjectP"]);
        $this->form->setValue('max_participants', null, $allowedParticipants);
        $accepted           = count(THM_OrganizerHelperCourses::getParticipants($courseID, 1));
        $waiting            = count(THM_OrganizerHelperCourses::getParticipants($courseID, 0));
        $capacityText       = $this->lang->_('COM_THM_ORGANIZER_CURRENT_CAPACITY');
        $this->capacityText = sprintf($capacityText, $accepted, $allowedParticipants, $waiting);

        $this->course['campus'] = THM_OrganizerHelperCourses::getCampus($this->course);
        $this->form->setValue('campusID', null, $this->course['campus']['id']);
        THM_OrganizerHelperComponent::addMenuParameters($this);

        $params                 = ['view' => 'course_manager', 'id' => $courseID];
        $this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($params);
        $this->modifyDocument();

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
}