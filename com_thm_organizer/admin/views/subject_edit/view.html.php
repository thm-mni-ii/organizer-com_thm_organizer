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

use Joomla\CMS\Uri\Uri;

/**
 * Class loads the subject form into display context.
 */
class THM_OrganizerViewSubject_Edit extends THM_OrganizerViewEdit
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        \JToolbarHelper::save('subject.save');
        \JToolbarHelper::save2new('subject.save2new');
        if (empty($this->item->id)) {
            \JToolbarHelper::title(\JText::_('THM_ORGANIZER_SUBJECT_EDIT_NEW_TITLE'), 'organizer_subjects');
            \JToolbarHelper::apply('subject.apply', \JText::_('THM_ORGANIZER_CREATE'));
            \JToolbarHelper::cancel('subject.cancel', 'JTOOLBAR_CANCEL');
        } else {
            \JToolbarHelper::title(\JText::_('THM_ORGANIZER_SUBJECT_EDIT_EDIT_TITLE'), 'organizer_subjects');
            \JToolbarHelper::apply('subject.apply', \JText::_('THM_ORGANIZER_APPLY'));
            \JToolbarHelper::cancel('subject.cancel', 'JTOOLBAR_CLOSE');
        }
    }

    /**
     * Adds resource files to the document
     *
     * @return void
     */
    protected function modifyDocument()
    {
        parent::modifyDocument();
        \JFactory::getDocument()->addScript(Uri::root() . 'components/com_thm_organizer/js/subject_prep_course.js');
    }
}
