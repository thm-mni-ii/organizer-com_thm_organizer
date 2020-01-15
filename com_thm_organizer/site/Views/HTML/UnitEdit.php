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

use Organizer\Helpers\Languages;
use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\HTML;

/**
 * Class loads persistent information about a unit into the display context.
 */
class UnitEdit extends EditView
{

	/**
	 * Adds a toolbar and title to the view.
	 *
	 * @return void  adds toolbar items to the view
	 */

	protected function addToolBar()
	{
		$new   = empty($this->item->id);
		$title = $new ?
			Languages::_('THM_ORGANIZER_UNIT_NEW') : Languages::_('THM_ORGANIZER_UNIT_EDIT');
		HTML::setTitle($title, 'contract-2');
		$toolbar   = Toolbar::getInstance();
		$applyText = $new ? Languages::_('THM_ORGANIZER_CREATE') : Languages::_('THM_ORGANIZER_APPLY');
		$toolbar->appendButton('Standard', 'apply', $applyText, 'units.apply', false);
		$toolbar->appendButton('Standard', 'save', Languages::_('THM_ORGANIZER_SAVE'), 'units.save', false);
		$cancelText = $new ? Languages::_('THM_ORGANIZER_CANCEL') : Languages::_('THM_ORGANIZER_CLOSE');
		$toolbar->appendButton('Standard', 'cancel', $cancelText, 'units.cancel', false);
	}
}