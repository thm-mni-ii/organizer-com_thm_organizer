<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Access;
use Organizer\Helpers\Dates;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of runs into the display context.
 */
class Runs extends ListView
{
	protected $rowStructure = ['checkbox' => '', 'name' => 'link', 'startDate' => 'link', 'endDate' => 'link'];

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_RUNS_TITLE'), 'list');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'run.add', false);
		$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'run.edit', true);
		$toolbar->appendButton(
			'Confirm',
			Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
			'delete',
			Languages::_('THM_ORGANIZER_DELETE'),
			'run.delete',
			true
		);
		HTML::setPreferencesButton();
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
		$ordering             = $this->state->get('list.ordering');
		$direction            = $this->state->get('list.direction');
		$headers              = [];
		$headers['checkbox']  = '';
		$headers['name']      = HTML::sort('NAME', 'name', $direction, $ordering);
		$headers['startDate'] = Languages::_('THM_ORGANIZER_START_DATE');
		$headers['endDate']   = Languages::_('THM_ORGANIZER_END_DATE');

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

		foreach ($this->items as $item)
		{
			$item->name = "$item->name - $item->term";
			$run        = json_decode($item->run, true);

			if (empty($run) or empty($run['runs']))
			{
				$item->startDate = '';
				$item->endDate   = '';
			}
			else
			{
				$item->startDate = Dates::formatDate(reset($run['runs'])['startDate']);
				$item->endDate   = Dates::formatDate(end($run['runs'])['endDate']);
			}

			$structuredItems[$index] = $this->structureItem($index, $item, $item->link);
			$index++;
		}

		$this->items = $structuredItems;
	}
}
