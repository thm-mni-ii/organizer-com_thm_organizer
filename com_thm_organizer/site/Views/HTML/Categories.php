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
use Organizer\Helpers\Categories as CategoriesHelper;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of event categories into the display context.
 */
class Categories extends ListView
{
	protected $rowStructure = ['checkbox' => '', 'untisID' => 'link', 'name' => 'link', 'program' => 'link'];

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_CATEGORIES_TITLE'), 'list');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'category.edit', true);
		if (Access::isAdmin())
		{
			$toolbar->appendButton(
				'Standard',
				'attachment',
				Languages::_('THM_ORGANIZER_MERGE'),
				'category.mergeView',
				true
			);
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

		$headers['checkbox'] = '';
		$headers['untisID']  = HTML::sort('UNTIS_ID', 'ppr.untisID', $direction, $ordering);
		$headers['name']     = HTML::sort('DISPLAY_NAME', 'ppr.name', $direction, $ordering);
		$headers['program']  = Languages::_('THM_ORGANIZER_PROGRAM');

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
		$link            = 'index.php?option=com_thm_organizer&view=category_edit&id=';
		$structuredItems = [];

		foreach ($this->items as $item)
		{
			$item->program           = CategoriesHelper::getName($item->id);
			$structuredItems[$index] = $this->structureItem($index, $item, $link . $item->id);
			$index++;
		}

		$this->items = $structuredItems;
	}
}
