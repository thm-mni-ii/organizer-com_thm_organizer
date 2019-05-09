<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Languages;

/**
 * Class loads the teacher merge form into display context.
 */
class Teacher_Merge extends MergeView
{
    public $form = null;

    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_TEACHER_MERGE'));
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'attachment', Languages::_('THM_ORGANIZER_MERGE'), 'teacher.merge', true);
        $toolbar->appendButton('Standard', 'cancel', Languages::_('THM_ORGANIZER_CANCEL'), 'teacher.cancel', false);
    }
}
