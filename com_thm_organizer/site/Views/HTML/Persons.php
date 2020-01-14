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
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Persons as PersonsHelper;

/**
 * Class loads persistent information a filtered set of persons into the display context.
 */
class Persons extends ListView
{
	protected $rowStructure = [
		'checkbox'     => '',
		'surname'      => 'link',
		'forename'     => 'link',
		'username'     => 'link',
		'untisID'      => 'link',
		'departmentID' => 'link'
	];

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_TEACHERS'), 'users');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'persons.add', false);
		$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'persons.edit', true);
		if (Can::administrate())
		{
			$toolbar->appendButton(
				'Standard',
				'attachment',
				Languages::_('THM_ORGANIZER_MERGE'),
				'persons.mergeView',
				true
			);
		}
	}

	/**
	 * Function determines whether the user may access the view.
	 *
	 * @return bool true if the use may access the view, otherwise false
	 */
	protected function allowAccess()
	{
		return Can::manage('persons');
	}

	/**
	 * Function to set the object's headers property
	 *
	 * @return void sets the object headers property
	 */
	public function setHeaders()
	{
		$headers = [
			'checkbox'     => '',
			'surname'      => Languages::_('THM_ORGANIZER_SURNAME'),
			'forename'     => Languages::_('THM_ORGANIZER_FORENAME'),
			'username'     => Languages::_('THM_ORGANIZER_USERNAME'),
			't.untisID'    => Languages::_('THM_ORGANIZER_UNTIS_ID'),
			'departmentID' => Languages::_('THM_ORGANIZER_DEPARTMENT')
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
			$item->forename = empty($item->forename) ? '' : $item->forename;
			$item->username = empty($item->username) ? '' : $item->username;
			$item->untisID  = empty($item->untisID) ? '' : $item->untisID;

			if (!$departments = PersonsHelper::getDepartmentNames($item->id))
			{
				$item->departmentID = Languages::_('JNONE');
			}
			elseif (count($departments) === 1)
			{
				$item->departmentID = $departments[0];
			}
			else
			{
				$item->departmentID = Languages::_('THM_ORGANIZER_MULTIPLE_DEPARTMENTS');
			}

			$structuredItems[$index] = $this->structureItem($index, $item, $item->link);
			$index++;
		}

		$this->items = $structuredItems;
	}
}
