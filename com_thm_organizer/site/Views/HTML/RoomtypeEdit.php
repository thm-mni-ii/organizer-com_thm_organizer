<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads the room type form into display context.
 */
class RoomtypeEdit extends EditView
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
			Languages::_('ORGANIZER_ROOMTYPE_NEW') : Languages::_('ORGANIZER_ROOMTYPE_EDIT');
		HTML::setTitle($title, 'cog');
		$toolbar   = Toolbar::getInstance();
		$applyText = $new ? Languages::_('ORGANIZER_CREATE') : Languages::_('ORGANIZER_APPLY');
		$toolbar->appendButton('Standard', 'apply', $applyText, 'roomtypes.apply', false);
		$toolbar->appendButton('Standard', 'save', Languages::_('ORGANIZER_SAVE'), 'roomtypes.save', false);
		$cancelText = $new ? Languages::_('ORGANIZER_CANCEL') : Languages::_('ORGANIZER_CLOSE');
		$toolbar->appendButton('Standard', 'cancel', $cancelText, 'roomtypes.cancel', false);
	}
}
