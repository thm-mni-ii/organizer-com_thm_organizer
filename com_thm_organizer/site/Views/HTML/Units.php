<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class which loads data into the view output context
 */
class Units extends ListView
{

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_UNITS'), 'contract-2');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'unit.add', false);
		$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'unit.edit', true);
		$toolbar->appendButton(
			'Confirm',
			Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
			'delete',
			Languages::_('THM_ORGANIZER_DELETE'),
			'unit.delete',
			true
		);
		$url = 'index.php?option=com_thm_organizer&view=instances';
		$toolbar->appendButton('Link', 'Instances', 'Instances', $url);

	}

	/**
	 * Function determines whether the user may access the view.
	 *
	 * @return bool true if the use may access the view, otherwise false
	 */
	protected function allowAccess()
	{
		return true;
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
			'checkbox' => HTML::_('grid.checkall'),
			'name'     => HTML::sort('NAME', 'name', $direction, $ordering),
			'grid'     => Languages::_('THM_ORGANIZER_GRID'),
			'run'      => Languages::_('THM_ORGANIZER_RUN'),
			'status'   => Languages::_('THM_ORGANIZER_STATE')
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
		$index = 0;
		$link  = "index.php?option=com_thm_organizer&view=unit_edit&id=";

		$structuredItems = [];

		foreach ($this->items as $item)
		{

			$today = date('Y-m-d');
			if ($item->end < $today)
			{
				$status = Languages::_('THM_ORGANIZER_EXPIRED');
			}
			elseif ($item->start > $today)
			{
				$status = Languages::_('THM_ORGANIZER_PENDING');
			}
			else
			{
				$status = Languages::_('THM_ORGANIZER_CURRENT');
			}

			$thisLink                            = $link . $item->id;
			$structuredItems[$index]             = [];
			$structuredItems[$index]['checkbox'] = HTML::_('grid.id', $index, $item->id);
			$structuredItems[$index]['name']     = HTML::_('link', $thisLink, $item->name);
			$structuredItems[$index]['grid']     = HTML::_('link', $thisLink, $item->grid);
			$structuredItems[$index]['run']      = HTML::_('link', $thisLink, $item->run);
			$structuredItems[$index]['status']   = HTML::_('link', $thisLink, $status);

			$index++;
		}

		$this->items = $structuredItems;
	}
}
