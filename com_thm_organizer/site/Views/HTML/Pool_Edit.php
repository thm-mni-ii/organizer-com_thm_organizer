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
 * Class loads the (subject) pool form into display context.
 */
class Pool_Edit extends EditView
{
    protected $_layout = 'tabs';

    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        $new   = empty($this->item->id);
        $title = $new ?
            Languages::_('THM_ORGANIZER_POOL_NEW') : Languages::_('THM_ORGANIZER_POOL_EDIT');
        HTML::setTitle($title, 'list-2');
        $toolbar   = Toolbar::getInstance();
        $applyText = $new ? Languages::_('THM_ORGANIZER_CREATE') : Languages::_('THM_ORGANIZER_APPLY');
        $toolbar->appendButton('Standard', 'apply', $applyText, 'pool.apply', false);
        $toolbar->appendButton('Standard', 'save', Languages::_('THM_ORGANIZER_SAVE'), 'pool.save', false);
        $toolbar->appendButton('Standard', 'save-new', Languages::_('THM_ORGANIZER_SAVE2NEW'), 'pool.save2new', false);
        if (!$new) {
            $toolbar->appendButton(
                'Standard', 'save-copy', Languages::_('THM_ORGANIZER_SAVE2COPY'), 'pool.save2copy', false
            );

            $baseURL = "index.php?option=com_thm_organizer&tmpl=component&type=pool&id={$this->item->id}&view=";

            $poolLink = $baseURL . 'pool_selection';
            $toolbar->appendButton('Popup', 'list', Languages::_('THM_ORGANIZER_ADD_POOL'), $poolLink);

            $subjectLink = $baseURL . 'subject_selection';
            $toolbar->appendButton('Popup', 'book', Languages::_('THM_ORGANIZER_ADD_SUBJECT'), $subjectLink);
        }
        $cancelText = $new ? Languages::_('THM_ORGANIZER_CANCEL') : Languages::_('THM_ORGANIZER_CLOSE');
        $toolbar->appendButton('Standard', 'cancel', $cancelText, 'pool.cancel', false);
    }
}
