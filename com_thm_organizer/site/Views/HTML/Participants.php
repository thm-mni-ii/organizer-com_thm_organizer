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

use Exception;
use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Access;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Programs;

/**
 * Class loads persistent information a filtered set of course participants into the display context.
 */
class Participants extends ListView
{
	protected $rowStructure = ['checkbox' => '', 'fullName' => 'link', 'programName' => 'link'];

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_PARTICIPANTS_TITLE'), 'users');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'participant.edit', true);

		if (Access::isAdmin())
		{
			$toolbar->appendButton(
				'Standard',
				'attachment',
				Languages::_('THM_ORGANIZER_MERGE'),
				'participant.mergeView',
				true
			);
			$toolbar->appendButton(
				'Standard',
				'eye-close',
				Languages::_('THM_ORGANIZER_ANONYMIZE'),
				'participant.anonymize',
				false
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
		return Access::allowCourseAccess();
	}

	/**
	 * Method to get display
	 *
	 * @param   Object  $tpl  template  (default: null)
	 *
	 * @return void
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		// Set batch template path
		$this->batch = ['batch_group_publishing'];

		parent::display($tpl);
	}

	/**
	 * Function to get table headers
	 *
	 * @return array including headers
	 */
	protected function getHeaders()
	{
		$ordering               = $this->state->get('list.ordering');
		$direction              = $this->state->get('list.direction');
		$headers                = [];
		$headers['checkbox']    = '';
		$headers['fullName']    = HTML::sort('NAME', 'fullName', $direction, $ordering);
		$headers['programName'] = HTML::sort('PROGRAM', 'programName', $direction, $ordering);

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
		$link            = 'index.php?option=com_thm_organizer&view=participant_edit&id=';
		$structuredItems = [];

		foreach ($this->items as $item)
		{
			$item->programName       = Programs::getName($item->programID);
			$structuredItems[$index] = $this->structureItem($index, $item, $link . $item->id);
			$index++;
		}

		$this->items = $structuredItems;
	}
}
