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
use Organizer\Helpers\Campuses;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads a filtered set of buildings into the display context.
 */
class Buildings extends ListView
{
	const OWNED = 1, RENTED = 2, USED = 3;

	protected $rowStructure = [
		'checkbox'     => '',
		'name'         => 'link',
		'campusID'     => 'link',
		'propertyType' => 'link',
		'address'      => 'link'
	];

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('ORGANIZER_BUILDINGS'), 'home-2');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('ORGANIZER_ADD'), 'buildings.add', false);
		$toolbar->appendButton('Standard', 'edit', Languages::_('ORGANIZER_EDIT'), 'buildings.edit', true);
		$toolbar->appendButton(
			'Confirm',
			Languages::_('ORGANIZER_DELETE_CONFIRM'),
			'delete',
			Languages::_('ORGANIZER_DELETE'),
			'building.delete',
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
		return Can::manage('facilities');
	}

	/**
	 * Function to set the object's headers property
	 *
	 * @return void sets the object headers property
	 */
	public function setHeaders()
	{
		$direction = $this->state->get('list.direction');
		$headers   = [
			'checkbox'     => '',
			'name'         => HTML::sort('NAME', 'name', $direction, 'name'),
			'campusID'     => Languages::_('ORGANIZER_CAMPUS'),
			'propertyType' => Languages::_('ORGANIZER_PROPERTY_TYPE'),
			'address'      => Languages::_('ORGANIZER_ADDRESS')
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
			$item->campusID = Campuses::getName($item->campusID);

			switch ($item->propertyType)
			{
				case self::OWNED:
					$item->propertyType = Languages::_('ORGANIZER_OWNED');
					break;

				case self::RENTED:
					$item->propertyType = Languages::_('ORGANIZER_RENTED');
					break;

				case self::USED:
					$item->propertyType = Languages::_('ORGANIZER_USED');
					break;

				default:
					$item->propertyType = Languages::_('ORGANIZER_UNKNOWN');
					break;
			}

			$structuredItems[$index] = $this->structureItem($index, $item, $item->link);
			$index++;
		}

		$this->items = $structuredItems;
	}
}
