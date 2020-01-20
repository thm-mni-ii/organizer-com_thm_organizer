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
use Organizer\Helpers\OrganizerHelper;

/**
 * Class loads the resource form into display context. Specific resource determined by extending class.
 */
abstract class MergeView extends FormView
{
	/**
	 * Adds a toolbar and title to the view.
	 *
	 * @return void  adds toolbar items to the view
	 */
	protected function addToolBar()
	{
		$name = OrganizerHelper::getClass($this);
		HTML::setTitle(Languages::_(Languages::getConstant($name)));
		$resource   = str_replace('merge', '', strtolower($name));
		$controller = OrganizerHelper::getPlural($resource);
		$toolbar    = Toolbar::getInstance();
		$toolbar->appendButton(
			'Standard',
			'attachment',
			Languages::_('ORGANIZER_MERGE'),
			$controller . '.merge',
			false
		);
		$toolbar->appendButton(
			'Standard',
			'cancel',
			Languages::_('ORGANIZER_CANCEL'),
			$controller . '.cancel',
			false
		);
	}
}
