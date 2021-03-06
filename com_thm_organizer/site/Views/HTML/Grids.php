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
use Organizer\Helpers\Can;
use Organizer\Helpers\Dates;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of schedule grids into the display context.
 */
class Grids extends ListView
{
	protected $rowStructure = [
		'checkbox'    => '',
		'name'        => 'link',
		'startDay'    => 'value',
		'endDay'      => 'value',
		'startTime'   => 'value',
		'endTime'     => 'value',
		'defaultGrid' => 'value'
	];

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('ORGANIZER_GRIDS'), 'grid-2');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('ORGANIZER_ADD'), 'grids.add', false);
		$toolbar->appendButton('Standard', 'edit', Languages::_('ORGANIZER_EDIT'), 'grids.edit', true);
		$toolbar->appendButton(
			'Confirm',
			Languages::_('ORGANIZER_DELETE_CONFIRM'),
			'delete',
			Languages::_('ORGANIZER_DELETE'),
			'grids.delete',
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
		return Can::administrate();
	}

	/**
	 * Function to set the object's headers property
	 *
	 * @return void sets the object headers property
	 */
	public function setHeaders()
	{
		$headers = [
			'checkbox'    => '',
			'name'        => Languages::_('ORGANIZER_NAME'),
			'startDay'    => Languages::_('ORGANIZER_START_DAY'),
			'endDay'      => Languages::_('ORGANIZER_END_DAY'),
			'startTime'   => Languages::_('ORGANIZER_START_TIME'),
			'endTime'     => Languages::_('ORGANIZER_END_TIME'),
			'defaultGrid' => Languages::_('ORGANIZER_DEFAULT')
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
			$grid = json_decode($item->grid, true);

			if (!empty($grid['periods']))
			{
				// 'l' (lowercase L) in date function for full textual day of the week.
				$startDayConstant = strtoupper(date('l', strtotime("Sunday + {$grid['startDay']} days")));
				$endDayConstant   = strtoupper(date('l', strtotime("Sunday + {$grid['endDay']} days")));

				$item->startDay  = Languages::_($startDayConstant);
				$item->endDay    = Languages::_($endDayConstant);
				$item->startTime = Dates::formatTime(reset($grid['periods'])['startTime']);
				$item->endTime   = Dates::formatTime(end($grid['periods'])['endTime']);
			}
			else
			{
				$item->startDay  = '';
				$item->endDay    = '';
				$item->startTime = '';
				$item->endTime   = '';
			}

			$tip                     = Languages::_('ORGANIZER_GRID_DEFAULT_DESC');
			$item->defaultGrid       = $this->getToggle('grid', $item->id, $item->defaultGrid, $tip);
			$structuredItems[$index] = $this->structureItem($index, $item, $item->link);
			$index++;
		}

		$this->items = $structuredItems;
	}
}
