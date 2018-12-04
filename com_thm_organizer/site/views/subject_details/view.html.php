<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost,  <Wolf.Rost@mni.thm.de>
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use \THM_OrganizerHelperHTML as HTML;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class loads the subject into the display context.
 */
class THM_OrganizerViewSubject_Details extends \Joomla\CMS\MVC\View\HtmlView
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
            $isCoordinator          = THM_OrganizerHelperSubjects::allowEdit($this->subjectID);

            foreach ($courses as &$course) {
                $courseID                     = $course['id'];
                $course['dateText']           = THM_OrganizerHelperCourses::getDateDisplay($courseID);
                $course['expired']            = !THM_OrganizerHelperCourses::isRegistrationOpen($courseID);
                $course['registrationButton'] = THM_OrganizerHelperCourses::getActionButton('subject', $courseID);
                $regState                     = THM_OrganizerHelperCourses::getParticipantState($courseID);
                $course['status']             = empty($regState) ? null : (int)$regState['status'];
                $course['statusDisplay']      = THM_OrganizerHelperCourses::getStatusDisplay($courseID);

                // Course administrators are green
                $isTeacher = THM_OrganizerHelperCourses::authorized($courseID);
                if ($isCoordinator or $isTeacher) {
                    $this->color = 'green';
                    continue;
                }

                // No change if: course has no status information, the status color has already been set to green
                if ($course['status'] === null or $this->color === 'green') {
                    continue;
                }

                $this->color = $course['status'] === self::REGISTERED ? 'green' : 'yellow';
            }

            $this->courses = $courses;
        }

        THM_OrganizerHelperComponent::addMenuParameters($this);

        $this->disclaimer = new JLayoutFile('disclaimer', JPATH_COMPONENT . '/layouts');
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
        if (empty($this->item->$index) and empty($default)) {
            return;
        }

        $label = 'COM_THM_ORGANIZER_' . strtoupper($constant);
        $label .= ((empty($this->item->$index) and !empty($default)) or count($this->item->$index)) ? 'S' : '';

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
        if (!isset($this->item->$index) or $this->item->$index === null or $this->item->$index === '') {
            return;
        }

        $imageFolder   = '/media/com_thm_organizer/images/';
        $allowedValues = [
            0 => HTML::image(JUri::root() . $imageFolder . '0stars.png', 'COM_THM_ORGANIZER_ZERO_STARS'),
            1 => HTML::image(JUri::root() . $imageFolder . '1stars.png', 'COM_THM_ORGANIZER_ONE_STAR'),
            2 => HTML::image(JUri::root() . $imageFolder . '2stars.png', 'COM_THM_ORGANIZER_TWO_STARS'),
            3 => HTML::image(JUri::root() . $imageFolder . '3stars.png', 'COM_THM_ORGANIZER_THREE_STARS')
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
     * @return void
     */
    private function modifyDocument()
    {
        HTML::_('bootstrap.tooltip');
        HTML::_('behavior.framework', true);

        $document = JFactory::getDocument();
        $document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/subject_details.css');
    }

    /**
     * Creates a list of dependencies dependent on the type (pre|post)
     *
     * @param string $type the type of dependency
     *
     * @return string the HTML for the dependency output
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

        $menuID        = THM_OrganizerHelperComponent::getInput()->getInt('Itemid', 0);
        $this->langTag = THM_OrganizerHelperLanguage::getShortTag();
        $link          = 'index.php?option=com_thm_organizer&view=subject_details';
        $link          .= "&languageTag={$this->langTag}&Itemid={$menuID}&id=";

        $html = '<ul>';
        foreach ($dependencies as $program) {
            $html .= "<li>{$program['name']}<ul>";
            foreach ($program['subjects'] as $subjectID => $subjectName) {
                $subjectLink = HTML::_('link', $link . $subjectID, $subjectName);
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
     * @return string the HTML output for the teacher
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
