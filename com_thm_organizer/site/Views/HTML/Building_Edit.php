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

/**
 * Class loads the building form into display context.
 */
class Building_Edit extends EditView
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
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
}
