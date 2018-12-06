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

use \THM_OrganizerHelperHTML as HTML;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/subjects.php';

/**
 * Class loads persistent information about a subject into the display context.
 */
class THM_OrganizerViewSubject_Edit extends \Joomla\CMS\MVC\View\HtmlView
{
    public $form;

    public $item;

    public $lang;

    public $languageLinks;

    public $languageParams;

    public $lessonID;

    public $menu;

    public $subjectID;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     * @throws Exception => invalid request / unauthorized access
     */
    public function display($tpl = null)
    {
        $input           = THM_OrganizerHelperComponent::getInput();
        $this->subjectID = $input->getInt('id', 0);

        if (empty($this->subjectID)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_400'), 400);
        }

        if (!THM_OrganizerHelperSubjects::allowEdit($this->subjectID)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_401'), 401);
        }

        $this->item = $this->get('Item');
        $this->form = $this->get('Form');

        $this->lessonID    = $input->getInt('lessonID', 0);
        $this->languageTag = THM_OrganizerHelperLanguage::getShortTag();


        $this->lang = THM_OrganizerHelperLanguage::getLanguage();

        THM_OrganizerHelperComponent::addMenuParameters($this);

        $this->languageLinks    = new JLayoutFile('language_links', JPATH_COMPONENT . '/layouts');
        $this->languageParams   = ['id' => $this->subjectID, 'lessonID' => $this->lessonID, 'view' => 'subject_edit'];

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
        HTML::_('bootstrap.tooltip');
        HTML::_('behavior.framework', true);

        JFactory::getDocument()->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/subject_edit.css');
    }
}
