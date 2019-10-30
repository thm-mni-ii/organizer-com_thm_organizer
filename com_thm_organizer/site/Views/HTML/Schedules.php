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
use Organizer\Helpers\Dates;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of schedules into the display context.
 */
class Schedules extends ListView
{
	/**
	 * creates a joomla administrative tool bar
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_SCHEDULES_TITLE'), 'calendars');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'schedule.add', false);
		$toolbar->appendButton(
			'Standard',
			'default',
			Languages::_('THM_ORGANIZER_ACTIVATE'),
			'schedule.activate',
			true
		);
		$toolbar->appendButton(
			'Standard',
			'tree',
			Languages::_('THM_ORGANIZER_CALCULATE_DELTA'),
			'schedule.setReference',
			true
		);
		$toolbar->appendButton(
			'Confirm',
			Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
			'delete',
			Languages::_('THM_ORGANIZER_DELETE'),
			'schedule.delete',
			true
		);

		if (Access::isAdmin())
		{
			HTML::setPreferencesButton();
		}
	}

	/**
	 * Function determines whether the user may access the view.
	 *
	 * @return bool true if the use may access the view, otherwise false
	 */
	protected function allowAccess()
	{
		return Access::allowSchedulingAccess();
	}

	/**
	 * Function to get table headers
	 *
	 * @return array including headers
	 */
	public function getHeaders()
	{
		$ordering  = $this->state->get('list.ordering');
		$direction = $this->state->get('list.direction');
		$headers   = [];

		$headers['checkbox']     = '';
		$headers['departmentID'] = Languages::_('THM_ORGANIZER_DEPARTMENT');
		$headers['termID']       = Languages::_('THM_ORGANIZER_TERM');
		$headers['active']       = Languages::_('THM_ORGANIZER_STATE');
		$headers['userName']     = HTML::sort('USERNAME', 'userName', $direction, $ordering);
		$headers['created']      = HTML::sort('CREATION_DATE', 'created', $direction, $ordering);

		return $headers;
	}

	/**
	 * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
	 *
	 * @return void processes the class items property
	 */
	protected function preProcessItems()
	{
		if (empty($this->items))
		{
			return;
		}

		$index          = 0;
		$processedItems = [];

		foreach ($this->items as $item)
		{
			$processedItems[$index] = [];

			$processedItems[$index]['checkbox']     = HTML::_('grid.id', $index, $item->id);
			$processedItems[$index]['departmentID'] = $item->departmentName;
			$processedItems[$index]['termID']       = $item->termName;

			$processedItems[$index]['active']
				= $this->getToggle($item->id, $item->active, 'schedule', Languages::_('THM_ORGANIZER_TOGGLE_ACTIVE'));

			$processedItems[$index]['userName'] = $item->userName;

			$created = Dates::formatDate($item->creationDate) . ' / ' . Dates::formatTime($item->creationTime);

			$processedItems[$index]['created'] = $created;

			$index++;
		}

		$this->items = $processedItems;
	}
}
