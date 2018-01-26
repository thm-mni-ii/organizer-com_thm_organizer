<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewSubject_Details
 * @author      Wolf Rost,  <Wolf.Rost@mni.thm.de>
 * @author      James Antrim,  <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class loads information about a subject into the view context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewSubject_Details extends JViewLegacy
{
    const PENDING = 0;
    const REGISTERED = 1;

    public $color = 'blue';

    public $courses = [];

    public $disclaimer;

    public $disclaimerData;

    public $languageSwitches = [];

    public $lang;

    public $langTag = 'de';

    public $menu;

    public $showRegistration = false;

    public $subjectID;

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
        $this->lang = THM_OrganizerHelperLanguage::getLanguage();
        $this->item = $this->get('Item');

        if (!empty($this->item->id)) {
            $this->subjectID        = $this->item->id;
            $params                 = ['view' => 'subject_details', 'id' => $this->subjectID];
            $this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($params);
        }

        $courses = THM_OrganizerHelperCourses::getLatestCourses($this->subjectID);

        if (!empty($courses)) {
            $this->showRegistration = true;
            $isCoordinator = THM_OrganizerHelperSubjects::isCoordinator($this->subjectID);

            foreach ($courses AS $key => &$course) {
                $courseID                     = $course['id'];
                $course['dateText']           = THM_OrganizerHelperCourses::getDateDisplay($courseID);
                $course['expired']            = !THM_OrganizerHelperCourses::isRegistrationOpen($courseID);
                $course['registrationButton'] = THM_OrganizerHelperCourses::getActionButton('subject', $courseID);
                $regState                     = THM_OrganizerHelperCourses::getParticipantState($courseID);
                $course['status']             = empty($regState) ? null : (int)$regState["status"];
                $course['statusDisplay']      = THM_OrganizerHelperCourses::getStatusDisplay($courseID);

                // Course administrators are green
                $isTeacher = THM_OrganizerHelperCourses::isTeacher($courseID);
                if ($isCoordinator OR $isTeacher) {
                    $this->color = 'green';
                    continue;
                }

                // Make no change if the course has no status information or if the status color has already been set to green.
                if ($course['status'] === null OR $this->status === 'green') {
                    continue;
                }

                $this->color = $course['status'] === self::REGISTERED ? 'green' : 'yellow';
            }

            $this->courses = $courses;
        }

        THM_OrganizerHelperComponent::addMenuParameters($this);

        $this->disclaimer     = new JLayoutFile('disclaimer',
            $basePath = JPATH_ROOT . '/media/com_thm_organizer/layouts');
        $this->disclaimerData = ['language' => $this->lang];

        parent::display($tpl);
    }

    /**
     * Creates a basic output for text or numeric values
     *
     * @param string $index    the object property name
     * @param string $constant the language constant for the label
     *
     * @return void outputs HTML
     */
    public function displayAttribute($index, $constant = '')
    {
        if (empty($this->item->$index)) {
            return;
        }

        $label = 'COM_THM_ORGANIZER_';
        $label .= empty($constant) ? strtoupper($index) : strtoupper($constant);

        ?>
        <div class="subject-item">
            <div class="subject-label"><?php echo $this->lang->_($label); ?></div>
            <div class="subject-content"><?php echo $this->item->$index; ?></div>
        </div>
        <?php
    }

    /**
     * Creates a basic output for processed values
     *
     * @param string $index    the attribute propery name
     * @param mixed  $constant the language constant fragment
     * @param mixed  $default  the default value to display
     *
     * @return void outputs HTML
     */
    public function displayTeacherAttribute($index, $constant, $default = '')
    {
        if (empty($this->item->$index) AND empty($default)) {
            return;
        }

        $label = 'COM_THM_ORGANIZER_' . strtoupper($constant);
        $label .= ((empty($this->item->$index) AND !empty($default)) OR count($this->item->$index)) ? 'S' : '';

        $value = '';

        if (empty($this->item->$index)) {
            $value .= $default;
        } elseif (count($this->item->$index) > 1) {
            foreach ($this->item->$index as $teacher) {
                $value .= '<li>' . $this->getTeacherOutput($teacher) . '</li>';
            }
        } else {
            $value .= $this->getTeacherOutput(array_values($this->item->$index)[0]);
        }
        ?>

        <div class="subject-item">
            <div class="subject-label"><?php echo $this->lang->_($label); ?></div>
            <div class="subject-content">
                <?php echo $value; ?>
            </div>
        </div>
        <?php
    }


    /**
     * Determines whether or not the attribute should be displayed based on its value and outputs that value
     *
     * @param string $index the object property name
     *
     * @return void outputs HTML
     */
    public function displayStarAttribute($index)
    {
        if (!isset($this->item->$index) OR $this->item->$index === null OR $this->item->$index === '') {
            return;
        }

        $allowedValues = [
            0 => JHtml::image(JUri::root() . '/media/com_thm_organizer/images/0stars.png',
                'COM_THM_ORGANIZER_ZERO_STARS'),
            1 => JHtml::image(JUri::root() . '/media/com_thm_organizer/images/1stars.png',
                'COM_THM_ORGANIZER_ONE_STAR'),
            2 => JHtml::image(JUri::root() . '/media/com_thm_organizer/images/2stars.png',
                'COM_THM_ORGANIZER_TWO_STARS'),
            3 => JHtml::image(JUri::root() . '/media/com_thm_organizer/images/3stars.png',
                'COM_THM_ORGANIZER_THREE_STARS')
        ];
        $value         = (int)$this->item->$index;

        if (!in_array($value, array_keys($allowedValues))) {
            return;
        }

        $constant = 'COM_THM_ORGANIZER_' . strtoupper($index);

        ?>
        <div class="subject-item">
            <div class="subject-label"><?php echo $this->lang->_($constant); ?></div>
            <div class="subject-content"><?php echo $allowedValues[$value]; ?></div>
        </div>
        <?php
        return;
    }

    /**
     * Creates a basic output for processed values
     *
     * @param string $constant the language constant for the label
     * @param mixed  $value    the value to be displayed, usually a html string
     *
     * @return void outputs HTML
     */
    public function displayValue($constant, $value)
    {
        if (empty($value)) {
            return;
        }

        $label = 'COM_THM_ORGANIZER_' . strtoupper($constant);

        ?>
        <div class="subject-item">
            <div class="subject-label"><?php echo $this->lang->_($label); ?></div>
            <div class="subject-content"><?php echo $value; ?></div>
        </div>
        <?php
    }

    /**
     * Modifies document variables and adds links to external files
     *
     * @return  void
     */
    private function modifyDocument()
    {
        JHtml::_('bootstrap.tooltip');
        JHtml::_('behavior.framework', true);

        $document = JFactory::getDocument();
        $document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/subject_details.css');
    }

    /**
     * Creates a list of depencencies dependent on the type (pre|post)
     *
     * @param string $type the type of dependency
     *
     * @return string the HTML for the depencency output
     */
    public function getDependencies($type)
    {
        $dependencies = [];
        switch ($type) {
            case 'pre':

                if (empty($this->item->preSubjects)) {
                    return '';
                }

                $dependencies = $this->item->preSubjects;

                break;

            case 'post':

                if (empty($this->item->postSubjects)) {
                    return '';
                }

                $dependencies = $this->item->postSubjects;

                break;

        }

        if (empty($dependencies)) {
            return '';
        }

        $menuID        = JFactory::getApplication()->input->getInt('Itemid', 0);
        $this->langTag = THM_OrganizerHelperLanguage::getShortTag();
        $link          = "index.php?option=com_thm_organizer&view=subject_details&languageTag={$this->langTag}&Itemid={$menuID}&id=";

        $html = '<ul>';
        foreach ($dependencies as $programID => $programData) {
            $html .= "<li>{$programData['name']}<ul>";
            foreach ($programData['subjects'] AS $subjectID => $subjectName) {
                $subjectLink = JHtml::_('link', $link . $subjectID, $subjectName);
                $html        .= "<li>$subjectLink</li>";
            }
            $html .= "</ul></li>";
        }
        $html .= "</ul>";

        return $html;
    }

    /**
     * Creates teacher output
     *
     * @param array $teacher the teacher item
     *
     * @return  void  creates HTML output
     */
    public function getTeacherOutput($teacher)
    {
        if (!empty($teacher['link'])) {
            return '<a href="' . $teacher['link'] . '">' . $teacher['name'] . '</a>';
        } else {
            return $teacher['name'];
        }
    }
}
