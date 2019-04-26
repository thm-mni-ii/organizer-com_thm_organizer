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

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/subjects.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Class loads persistent information about a subject into the display context.
 */
class THM_OrganizerViewSubject_Edit extends \Joomla\CMS\MVC\View\HtmlView
{
    public $form;

    public $item;

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
        $input           = OrganizerHelper::getInput();
        $this->subjectID = $input->getInt('id', 0);

        if (empty($this->subjectID)) {
            throw new \Exception(Languages::_('THM_ORGANIZER_400'), 400);
        }

        if (!THM_OrganizerHelperSubjects::allowEdit($this->subjectID)) {
            throw new \Exception(Languages::_('THM_ORGANIZER_401'), 401);
        }

        $this->item = $this->get('Item');
        $this->form = $this->get('Form');

        $this->lessonID    = $input->getInt('lessonID', 0);
        $this->languageTag = Languages::getShortTag();

        OrganizerHelper::addMenuParameters($this);

        $this->languageLinks  = new \JLayoutFile('language_links', JPATH_ROOT . '/components/com_thm_organizer/Layouts');
        $this->languageParams = ['id' => $this->subjectID, 'lessonID' => $this->lessonID, 'view' => 'subject_edit'];

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

        Factory::getDocument()->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/subject_edit.css');
    }
}
