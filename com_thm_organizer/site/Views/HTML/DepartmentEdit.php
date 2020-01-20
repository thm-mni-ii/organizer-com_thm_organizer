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
 * Class loads the department form into display context.
 */
class DepartmentEdit extends EditView
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
			Languages::_('ORGANIZER_DEPARTMENT_NEW') : Languages::_('ORGANIZER_DEPARTMENT_EDIT');
		HTML::setTitle($title, 'tree-2');
		$toolbar   = Toolbar::getInstance();
		$applyText = $new ? Languages::_('ORGANIZER_CREATE') : Languages::_('ORGANIZER_APPLY');
		$toolbar->appendButton('Standard', 'apply', $applyText, 'departments.apply', false);
		$toolbar->appendButton('Standard', 'save', Languages::_('ORGANIZER_SAVE'), 'departments.save', false);
		$toolbar->appendButton(
			'Standard',
			'save-new',
			Languages::_('ORGANIZER_SAVE2NEW'),
			'departments.save2new',
			false
		);
		$cancelText = $new ? Languages::_('ORGANIZER_CANCEL') : Languages::_('ORGANIZER_CLOSE');
		$toolbar->appendButton('Standard', 'cancel', $cancelText, 'departments.cancel', false);
	}
}
