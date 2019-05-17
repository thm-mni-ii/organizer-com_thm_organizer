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

namespace Organizer\Views\HTML;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Access;
use Organizer\Helpers\Courses;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class loads persistent information about a course into the display context.
 */
class Course_Edit extends EditView
{
    public $course;

    public $courseAuth = false;

    public $form;

    public $languageLinks;

    public $languageParams;

    public $menu;/**
 * Concrete classes are supposed to use this method to add a toolbar.
 *
 * @return void  adds toolbar items to the view
 */

    protected function addToolBar() {
        return;
    }

    /**
     * Method to get display
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return void
     * @throws Exception => unauthorized access
     */
    public function display($tpl = null)
    {
        $lessonID   = OrganizerHelper::getInput()->getInt('id', 0);

        if (empty($lessonID) or !Access::allowCourseAccess($lessonID)) {
            throw new Exception(Languages::_('THM_ORGANIZER_401'), 401);
        }

        $this->course                 = Courses::getCourse();
        $courseID                     = empty($this->course) ? 0 : $this->course['id'];
        $this->course['campus']       = Courses::getCampus($this->course);
        $this->course['participants'] = Courses::getParticipants($courseID);
        $this->course['dateText']     = Courses::getDateDisplay();

        $maxParticipants              = (!empty($this->course['lessonP']) ? $this->course['lessonP'] : $this->course['subjectP']);
        $accepted                     = count(Courses::getParticipants($courseID, 1));
        $waiting                      = count(Courses::getParticipants($courseID, 0));
        $capacityText                 = Languages::_('THM_ORGANIZER_CURRENT_CAPACITY');
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

        /*$this->languageLinks  = new LayoutFile('language_links', JPATH_ROOT . '/components/com_thm_organizer/Layouts');
        $this->languageParams = ['lessonID' => $courseID, 'view' => 'courses'];*/
        $this->modifyDocument();
        OrganizerHelper::addMenuParameters($this);

        parent::display($tpl);
    }

    /**
     * Adds resource files to the document
     *
     * @return void
     */
    protected function modifyDocument()
    {
        HTML::_('bootstrap.tooltip');

        $document = Factory::getDocument();
        $document->addScriptDeclaration(
            "var chooseParticipants = '" . Languages::_('THM_ORGANIZER_CHOOSE_PARTICIPANTS') . "'"
        );
        $document->addScript(Uri::root() . 'components/com_thm_organizer/js/courses.js');
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/courses.css');
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
        $title = Languages::_($this->form->getFieldAttribute($field, 'label'));

        if (empty($icon)) {
            $this->form->setFieldAttribute($field, 'label', $title);
        } else {
            $iconHTML  = '<span class="icon-' . $icon . '"></span>';
            $titleHTML = '<span class="si-title">' . $title . '</span>';
            $this->form->setFieldAttribute($field, 'label', $iconHTML . $titleHTML);
        }
        $description = Languages::_($this->form->getFieldAttribute($field, 'description'));
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
            $field->option[$index] = Languages::_($option[0]);
            $index++;
        }
        $this->form->setField($field, null, true);
    }
}
