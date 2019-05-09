<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/views/edit.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Languages;

/**
 * Class loads the subject form into display context.
 */
class Subject_Edit extends EditView
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        $new = empty($this->item->id);
        $title = $new ?
            Languages::_('THM_ORGANIZER_SUBJECT_NEW') : Languages::_('THM_ORGANIZER_SUBJECT_EDIT');
        HTML::setTitle($title, 'book');
        $toolbar = Toolbar::getInstance();
        $applyText = $new ? Languages::_('THM_ORGANIZER_CREATE') : Languages::_('THM_ORGANIZER_APPLY');
        $toolbar->appendButton('Standard', 'apply', $applyText, 'subject.apply', false);
        $toolbar->appendButton('Standard', 'save', Languages::_('THM_ORGANIZER_SAVE'), 'subject.save', false);
        $toolbar->appendButton(
            'Standard', 'save-new', Languages::_('THM_ORGANIZER_SAVE2NEW'), 'subject.save2new', false
        );
        $cancelText = $new ? Languages::_('THM_ORGANIZER_CANCEL') : Languages::_('THM_ORGANIZER_CLOSE');
        $toolbar->appendButton('Standard', 'cancel', $cancelText, 'subject.cancel', false);
    }

    /**
     * Adds resource files to the document
     *
     * @return void
     */
    protected function modifyDocument()
    {
        parent::modifyDocument();
        Factory::getDocument()->addScript(Uri::root() . 'components/com_thm_organizer/js/subject_prep_course.js');
    }
}
