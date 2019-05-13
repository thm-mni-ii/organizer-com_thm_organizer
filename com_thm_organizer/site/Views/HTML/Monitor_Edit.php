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
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads the monitor form into display context.
 */
class Monitor_Edit extends EditView
{
    /**
     * Adds joomla toolbar elements to the view context
     *
     * @return void
     */
    protected function addToolBar()
    {
        $new   = empty($this->item->id);
        $title = $new ?
            Languages::_('THM_ORGANIZER_MONITOR_NEW') : Languages::_('THM_ORGANIZER_MONITOR_EDIT');
        HTML::setTitle($title, 'screen');
        $toolbar   = Toolbar::getInstance();
        $applyText = $new ? Languages::_('THM_ORGANIZER_CREATE') : Languages::_('THM_ORGANIZER_APPLY');
        $toolbar->appendButton('Standard', 'apply', $applyText, 'monitor.apply', false);
        $toolbar->appendButton('Standard', 'save', Languages::_('THM_ORGANIZER_SAVE'), 'monitor.save', false);
        $toolbar->appendButton(
            'Standard', 'save-new', Languages::_('THM_ORGANIZER_SAVE2NEW'), 'monitor.save2new', false
        );
        $cancelText = $new ? Languages::_('THM_ORGANIZER_CANCEL') : Languages::_('THM_ORGANIZER_CLOSE');
        $toolbar->appendButton('Standard', 'cancel', $cancelText, 'monitor.cancel', false);
    }
}
