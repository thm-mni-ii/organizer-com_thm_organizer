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

use Organizer\Helpers\Can;
use Organizer\Helpers\Fields;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Mappings;

/**
 * Class loads persistent information a filtered set of (subject) pools into the display context.
 */
abstract class PoolsView extends ListView
{
	protected $rowStructure = ['checkbox' => '', 'name' => 'link', 'programID' => 'link', 'fieldID' => 'value'];

	/**
	 * Function determines whether the user may access the view.
	 *
	 * @return bool true if the use may access the view, otherwise false
	 */
	protected function allowAccess()
	{
		return (bool) Can::documentTheseDepartments();
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
			'programID' => Languages::_('ORGANIZER_PROGRAM'),
			'fieldID'   => HTML::sort('FIELD', 'field', $direction, $ordering)
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
		$link            = 'index.php?option=com_thm_organizer&view=pool_edit&id=';
		$structuredItems = [];

		foreach ($this->items as $item)
		{
			$item->fieldID           = Fields::getListDisplay($item->fieldID);
			$item->programID         = Mappings::getProgramName('pool', $item->id);
			$structuredItems[$index] = $this->structureItem($index, $item, $link . $item->id);
			$index++;
		}

		$this->items = $structuredItems;
	}
}
