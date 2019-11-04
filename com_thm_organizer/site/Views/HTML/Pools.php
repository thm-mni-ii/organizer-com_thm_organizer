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
use Organizer\Helpers\Access;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of (subject) pools into the display context.
 */
class Pools extends PoolsView
{
	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_POOLS_TITLE'), 'list-2');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'pool.add', false);
		$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'pool.edit', true);
		$toolbar->appendButton(
			'Confirm',
			Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
			'delete',
			Languages::_('THM_ORGANIZER_DELETE'),
			'pool.delete',
			true
		);
	}
}
