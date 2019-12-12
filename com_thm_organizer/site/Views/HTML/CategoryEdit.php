<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads the plan (degree) program / organizational grouping form into display context.
 */
class CategoryEdit extends EditView
{
	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_CATEGORY_EDIT'), 'list');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'save', Languages::_('THM_ORGANIZER_SAVE'), 'categories.save', false);
		$toolbar->appendButton('Standard', 'cancel', Languages::_('THM_ORGANIZER_CANCEL'), 'categories.cancel', false);
	}
}
