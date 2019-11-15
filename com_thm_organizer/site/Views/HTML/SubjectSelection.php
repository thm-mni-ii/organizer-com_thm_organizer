<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Access;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads subject information into the display context.
 */
class SubjectSelection extends ListView
{
	protected $_layout = 'list_modal';

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'pool.addSubject', true);
	}

	/**
	 * Function determines whether the user may access the view.
	 *
	 * @return bool true if the use may access the view, otherwise false
	 */
	protected function allowAccess()
	{
		return Access::allowDocumentAccess();
	}

	/**
	 * Function to get table headers
	 *
	 * @return array including headers
	 */
	protected function getHeaders()
	{
		$direction = $this->state->get('list.direction');
		$ordering  = $this->state->get('list.ordering');
		$headers   = [];

		$headers['checkbox'] = HTML::_('grid.checkall');
		$headers['name']     = HTML::sort('NAME', 'name', $direction, $ordering);
		$headers['program']  = Languages::_('THM_ORGANIZER_PROGRAMS');

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
		$structuredItems = [];

		foreach ($this->items as $subject)
		{
			if (!Access::allowSubjectAccess($subject->id))
			{
				continue;
			}

			$name = $subject->name;
			$name .= empty($subject->code) ? '' : " - $subject->code";

			$structuredItems[$index]             = [];
			$structuredItems[$index]['checkbox'] = HTML::_('grid.id', $index, $subject->id);
			$structuredItems[$index]['name']     = $name;
			$structuredItems[$index]['programs'] = $name;

			$index++;
		}

		$this->items = $structuredItems;
	}
}
