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
use Organizer\Helpers\Colors as Helper;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of colors into the display context.
 */
class Colors extends ListView
{
	protected $rowStructure = ['checkbox' => '', 'name' => 'link', 'color' => 'value'];

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_COLORS_TITLE'), 'palette');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'color.add', false);
		$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'color.edit', true);
		$toolbar->appendButton(
			'Confirm',
			Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
			'delete',
			Languages::_('THM_ORGANIZER_DELETE'),
			'color.delete',
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
		return Access::isAdmin();
	}

	/**
	 * Function to get table headers
	 *
	 * @return array including headers
	 */
	public function getHeaders()
	{
		$direction           = $this->state->get('list.direction');
		$headers             = [];
		$headers['checkbox'] = '';
		$headers['name']     = HTML::sort('NAME', 'name', $direction, 'name');
		$headers['color']    = Languages::_('THM_ORGANIZER_COLOR');

		return $headers;
	}

	/**
	 * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
	 *
	 * @return void processes the class items property
	 */
	protected function structureItems()
	{
		$index           = 0;
		$link            = 'index.php?option=com_thm_organizer&view=color_edit&id=';
		$structuredItems = [];

		foreach ($this->items as $item)
		{
			$item->color             = Helper::getListDisplay($item->color, $item->id);
			$structuredItems[$index] = $this->structureItem($index, $item, $link . $item->id);
			$index++;
		}

		$this->items = $structuredItems;
	}
}
