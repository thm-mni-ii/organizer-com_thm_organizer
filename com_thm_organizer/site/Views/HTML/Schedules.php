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
use Organizer\Helpers\Can;
use Organizer\Helpers\Dates;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of schedules into the display context.
 */
class Schedules extends ListView
{
	protected $rowStructure = [
		'checkbox'       => '',
		'departmentName' => 'value',
		'termName'       => 'value',
		'active'         => 'value',
		'userName'       => 'value',
		'created'        => 'value'
	];

	/**
	 * creates a joomla administrative tool bar
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_SCHEDULES'), 'calendars');
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
	}

	/**
	 * Function determines whether the user may access the view.
	 *
	 * @return bool true if the use may access the view, otherwise false
	 */
	protected function allowAccess()
	{
		return (bool) Can::scheduleTheseDepartments();
	}

	/**
	 * Function to set the object's headers property
	 *
	 * @return void sets the object headers property
	 */
	public function setHeaders()
	{
		$ordering  = $this->state->get('list.ordering');
		$direction = $this->state->get('list.direction');
		$headers   = [
			'checkbox'       => '',
			'departmentName' => HTML::sort('DEPARTMENT', 'departmentName', $direction, $ordering),
			'termName'       => HTML::sort('TERM', 'termName', $direction, $ordering),
			'active'         => HTML::sort('STATE', 'active', $direction, $ordering),
			'userName'       => HTML::sort('USERNAME', 'userName', $direction, $ordering),
			'created'        => HTML::sort('CREATION_DATE', 'created', $direction, $ordering)
		];

		$this->headers = $headers;
	}

	/**
	 * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
	 *
	 * @return void processes the class items property
	 */
	protected function structureItems()
	{
		$index           = 0;
		$structuredItems = [];

		foreach ($this->items as $item)
		{
			$item->active  =
				$this->getToggle('schedule', $item->id, $item->active, Languages::_('THM_ORGANIZER_TOGGLE_ACTIVE'));
			$item->created = Dates::formatDate($item->creationDate) . ' / ' . Dates::formatTime($item->creationTime);

			$structuredItems[$index] = $this->structureItem($index, $item);
			$index++;
		}

		$this->items = $structuredItems;
	}
}
