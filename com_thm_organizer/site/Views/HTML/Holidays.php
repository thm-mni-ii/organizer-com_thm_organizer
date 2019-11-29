<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
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
 * Class loads persistent information a filtered set of holidays into the display context.
 */
class Holidays extends ListView
{
	const OPTIONAL = 1, PARTIAL = 2, BLOCKING = 3;

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_HOLIDAYS'), 'calendar');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'holiday.add', false);
		$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'holiday.edit', true);
		$toolbar->appendButton(
			'Confirm',
			Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
			'delete',
			Languages::_('THM_ORGANIZER_DELETE'),
			'holiday.delete',
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
		$ordering  = $this->state->get('list.ordering');
		$direction = $this->state->get('list.direction');
		$headers   = [
			'checkbox'  => '',
			'name'      => HTML::sort('NAME', 'name', $direction, $ordering),
			'startDate' => HTML::sort('DATE', 'startDate', $direction, $ordering),
			'type'      => HTML::sort('TYPE', 'type', $direction, $ordering),
			'status'    => Languages::_('THM_ORGANIZER_STATE')
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
		$link            = 'index.php?option=com_thm_organizer&view=holiday_edit&id=';
		$structuredItems = [];

		foreach ($this->items as $item)
		{

			$dateString = Dates::getDisplay($item->startDate, $item->endDate);
			$today      = Dates::formatDate();
			$startDate  = Dates::formatDate($item->startDate);
			$endDate    = Dates::formatDate($item->endDate);
			$year       = date('Y', strtotime($item->startDate));

			if ($endDate < $today)
			{
				$status = Languages::_('THM_ORGANIZER_EXPIRED');
			}
			elseif ($startDate > $today)
			{
				$status = Languages::_('THM_ORGANIZER_PENDING');
			}
			else
			{
				$status = Languages::_('THM_ORGANIZER_CURRENT');
			}

			$thisLink                             = $link . $item->id;
			$structuredItems[$index]              = [];
			$structuredItems[$index]['checkbox']  = HTML::_('grid.id', $index, $item->id);
			$structuredItems[$index]['name']      = HTML::_('link', $thisLink, $item->name) . ' (' . HTML::_('link',
					$thisLink, $year) . ')';
			$structuredItems[$index]['startDate'] = HTML::_('link', $thisLink, $dateString);
			$structuredItems[$index]['type']      = HTML::_('link', $thisLink,
				($item->type == self::OPTIONAL ? Languages::_('THM_ORGANIZER_PLANNING_OPTIONAL') : ($item->type == self::PARTIAL ? Languages::_('THM_ORGANIZER_PLANNING_MANUAL')
					: Languages::_('THM_ORGANIZER_PLANNING_BLOCKED'))));
			$structuredItems[$index]['status']    = HTML::_('link', $thisLink, $status);

			$index++;
		}

		$this->items = $structuredItems;
	}
}