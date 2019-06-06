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

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class loads the resource form into display context. Specific resource determined by extending class.
 */
abstract class MergeView extends FormView
{
    /**
     * Concrete classes are supposed to use this method to add a toolbar.
     *
     * @return void  adds toolbar items to the view
     */
    protected function addToolBar()
    {
        $name = OrganizerHelper::getClass($this);
        HTML::setTitle(Languages::_('THM_ORGANIZER_' . strtoupper($name)));
        $dataModel = str_replace('_merge', '', strtolower($name));
        $toolbar   = Toolbar::getInstance();
        $toolbar->appendButton(
            'Standard', 'attachment', Languages::_('THM_ORGANIZER_MERGE'), $dataModel . '.merge', false
        );
        $toolbar->appendButton('Standard', 'cancel', Languages::_('THM_ORGANIZER_CANCEL'), $dataModel . '.cancel',
            false);
    }
}
