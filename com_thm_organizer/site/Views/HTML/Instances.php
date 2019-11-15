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
use Organizer\Helpers\Access;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of instances into the display context.
 */
class Instances extends ListView
{
	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_INSTANCES_TITLE'), 'contract-2');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'instance.add', false);
		$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'instance.edit', true);
		$toolbar->appendButton(
			'Confirm',
			Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
			'delete',
			Languages::_('THM_ORGANIZER_DELETE'),
			'instance.delete',
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
		$ordering  = $this->state->get('list.ordering');
		$direction = $this->state->get('list.direction');
		$headers   = [];

		$headers['checkbox'] = '';
		$headers['name']     = HTML::sort('NAME', 'name', $direction, $ordering);
		$headers['term']     = Languages::_('THM_ORGANIZER_TERM');
		$headers['status']   = Languages::_('THM_ORGANIZER_STATE');

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
		$link            = 'index.php?option=com_thm_organizer&view=instance_edit&id=';
		$structuredItems = [];

		foreach ($this->items as $item)
		{

			$today = date('Y-m-d');
			if ($item->date < $today)
			{
				$status = Languages::_('THM_ORGANIZER_EXPIRED');
			}
			elseif ($item->date > $today)
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
			$structuredItems[$index]['term']     = HTML::_('link', $thisLink, $item->term);
			$structuredItems[$index]['status']   = HTML::_('link', $thisLink, $status);

			$index++;
		}

		$this->items = $structuredItems;
	}
}