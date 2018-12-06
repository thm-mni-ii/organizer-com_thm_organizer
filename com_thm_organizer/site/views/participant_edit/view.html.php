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

use \THM_OrganizerHelperHTML as HTML;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/courses.php';

/**
 * Class loads participant information into the display context.
 */
class THM_OrganizerViewParticipant_Edit extends \Joomla\CMS\MVC\View\HtmlView
{
    public $lang;

    public $languageLinks;

    public $languageParams;

    public $item;

    public $form;

    public $course;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     * @throws Exception => unauthorized access
     */
    public function display($tpl = null)
    {
        if (empty(JFactory::getUser()->id)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_401'), 401);
        }

        $this->item   = $this->get('Item');
        $this->form   = $this->get('Form');
        $this->course = THM_OrganizerHelperCourses::getCourse();

        if (!empty($this->course)) {
            $dates                     = THM_OrganizerHelperCourses::getDates();
            $this->course['startDate'] = THM_OrganizerHelperDate::formatDate($dates[0]);
            $this->course['endDate']   = THM_OrganizerHelperDate::formatDate(end($dates));
            $this->course['open']      = THM_OrganizerHelperCourses::isRegistrationOpen();
        }

        $this->lang             = THM_OrganizerHelperLanguage::getLanguage();
        $this->languageLinks    = new JLayoutFile('language_links', JPATH_COMPONENT . '/layouts');
        $courseID = empty($this->course) ? 0 : $this->course['id'];
        $this->languageParams   = ['lessonID' => $courseID, 'view' => 'participant_edit'];

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
        HTML::_('bootstrap.tooltip');

        JFactory::getDocument()->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/participant_edit.css');
    }
}