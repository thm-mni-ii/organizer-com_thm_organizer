<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads the event form into display context.
 */
class EventEdit extends EditView
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
			Languages::_('ORGANIZER_EVENT_NEW') : Languages::_('ORGANIZER_EVENT_EDIT');
		HTML::setTitle($title, 'contract-2');
		$toolbar   = Toolbar::getInstance();
		$applyText = $new ? Languages::_('ORGANIZER_CREATE') : Languages::_('ORGANIZER_APPLY');
		$toolbar->appendButton('Standard', 'apply', $applyText, 'events.apply', false);
		$toolbar->appendButton('Standard', 'save', Languages::_('ORGANIZER_SAVE'), 'events.save', false);
		$cancelText = $new ? Languages::_('ORGANIZER_CANCEL') : Languages::_('ORGANIZER_CLOSE');
		$toolbar->appendButton('Standard', 'cancel', $cancelText, 'events.cancel', false);
	}
}