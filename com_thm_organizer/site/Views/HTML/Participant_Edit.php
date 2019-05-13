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
use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Dates;
use Organizer\Helpers\Courses;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads participant information into the display context.
 */
class Participant_Edit extends EditView
{
    public $languageLinks;

    public $languageParams;

    public $item;

    public $form;

    public $course;

    protected function addToolBar()
    {
        $new   = empty($this->item->id);
        $title = $new ?
            Languages::_('THM_ORGANIZER_BUILDING_NEW') : Languages::_('THM_ORGANIZER_BUILDING_EDIT');
        HTML::setTitle($title, 'home-2');
        $toolbar   = Toolbar::getInstance();
        $applyText = $new ? Languages::_('THM_ORGANIZER_CREATE') : Languages::_('THM_ORGANIZER_APPLY');
        $toolbar->appendButton('Standard', 'apply', $applyText, 'building.apply', false);
        $toolbar->appendButton('Standard', 'save', Languages::_('THM_ORGANIZER_SAVE'), 'building.save', false);
        $cancelText = $new ?
            Languages::_('THM_ORGANIZER_CANCEL') : Languages::_('THM_ORGANIZER_CLOSE');
        $toolbar->appendButton('Standard', 'cancel', $cancelText, 'building.cancel', false);
    }

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
        if (empty(Factory::getUser()->id)) {
            throw new Exception(Languages::_('THM_ORGANIZER_401'), 401);
        }

        $this->item   = $this->get('Item');
        $this->form   = $this->get('Form');
        $this->course = Courses::getCourse();

        if (!empty($this->course)) {
            $dates                     = Courses::getDates();
            $this->course['startDate'] = Dates::formatDate($dates[0]);
            $this->course['endDate']   = Dates::formatDate(end($dates));
            $this->course['open']      = Courses::isRegistrationOpen();
        }

        /*$this->languageLinks  = new LayoutFile('language_links', JPATH_ROOT . '/components/com_thm_organizer/Layouts');
        $courseID             = empty($this->course) ? 0 : $this->course['id'];
        $this->languageParams = ['lessonID' => $courseID, 'view' => 'participant_edit'];*/

        parent::display($tpl);
    }
}
// $bar->appendButton( 'Link', 'custom', 'Custom', '../index.php?option=com_mycomponent&format=raw' );