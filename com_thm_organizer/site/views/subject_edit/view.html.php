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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/subjects.php';

/**
 * Class loads persistent information about a subject into the display context.
 */
class THM_OrganizerViewSubject_Edit extends JViewLegacy
{
    public $form;

    public $item;

    public $lang;

    public $languageSwitches;

    public $lessonID;

    public $menu;

    public $subjectID;

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
        $input           = JFactory::getApplication()->input;
        $this->subjectID = $input->getInt('id', 0);

        if (empty($this->subjectID)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_404'), 404);
        }

        $authorized = THM_OrganizerHelperSubjects::allowEdit($this->subjectID);

        if (!$authorized) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_401'), 401);
        }

        $this->item = $this->get('Item');
        $this->form = $this->get('Form');

        $this->lessonID    = $input->getInt('lessonID', 0);
        $this->languageTag = THM_OrganizerHelperLanguage::getShortTag();


        $this->lang = THM_OrganizerHelperLanguage::getLanguage();

        THM_OrganizerHelperComponent::addMenuParameters($this);

        $params = ['view' => 'subject_edit', 'id' => $this->subjectID, 'lessonID' => $this->lessonID];

        $this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($params);

        $this->modifyDocument();

        parent::display($tpl);
    }

    /**
     * Adds resource files to the document
     *
     * @return void
     */
    protected function modifyDocument()
    {
        JHtml::_('bootstrap.tooltip');
        JHtml::_('behavior.framework', true);

        JFactory::getDocument()->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/subject_edit.css');
    }
}
