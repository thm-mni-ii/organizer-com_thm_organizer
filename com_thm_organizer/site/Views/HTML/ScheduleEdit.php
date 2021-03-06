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
 * Class loads the schedule upload form into display context.
 */
class ScheduleEdit extends EditView
{
	/**
	 * creates the joomla adminstrative toolbar
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('ORGANIZER_SCHEDULE_EDIT'), 'calendars');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton(
			'Standard',
			'upload',
			Languages::_('ORGANIZER_UPLOAD'),
			'schedules.upload',
			false
		);
		$toolbar->appendButton('Standard', 'cancel', Languages::_('ORGANIZER_CANCEL'), 'schedules.cancel', false);
	}
}
