<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/course.php';

/**
 * Class which loads data into the view output context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewParticipant_Edit extends JViewLegacy
{
    public $lang;

    public $languageSwitches;

    public $item;

    public $form;

    public $course;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        if (empty(JFactory::getUser()->id)) {
            JError::raiseError(401, JText::_('COM_THM_ORGANIZER_MESSAGE_NO_ACCESS_VIEW'));
        }

        $this->item   = $this->get('Item');
        $this->form   = $this->get('Form');
        $this->course = THM_OrganizerHelperCourse::getCourse();

        if (!empty($this->course)) {
            $dates                     = THM_OrganizerHelperCourse::getDates();
            $this->course['startDate'] = THM_OrganizerHelperComponent::formatDate($dates[0]['schedule_date']);
            $this->course['endDate']   = THM_OrganizerHelperComponent::formatDate(end($dates)['schedule_date']);
            $this->course['open']      = THM_OrganizerHelperCourse::isRegistrationOpen();
        }

        $this->lang             = THM_OrganizerHelperLanguage::getLanguage();
        $params                 = [
            'view'     => 'participant_edit',
            'lessonID' => empty($this->course) ? 0 : $this->course["id"]
        ];
        $this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($params);

        $this->modifyDocument();

        parent::display($tpl);
    }

    /**
     * Adds resource files to the document
     *
     * @return  void
     */
    private function modifyDocument()
    {
        JHtml::_('bootstrap.tooltip');

        JFactory::getDocument()->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/participant_edit.css');
    }
}