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
use Organizer\Helpers\Can;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Programs;

/**
 * Class loads persistent information a filtered set of course participants into the display context.
 */
class Participants extends ListView
{
	protected $rowStructure = [
		'checkbox'    => '',
		'fullName'    => 'value',
		'email'       => 'value',
		'programName' => 'value',
		'status'      => 'value',
		'paid'        => 'value',
		'attended'    => 'value'
	];

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_PARTICIPANTS'), 'users');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'participants.edit', true);

		if (Can::administrate())
		{
			$toolbar->appendButton(
				'Standard',
				'attachment',
				Languages::_('THM_ORGANIZER_MERGE'),
				'participants.mergeView',
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
		$courseID = Input::getFilterID('course', 0);

		return Can::manage('course', $courseID);
	}

	/**
	 * Function to set the object's headers property
	 *
	 * @return void sets the object headers property
	 */
	protected function setHeaders()
	{
		$ordering  = $this->state->get('list.ordering');
		$direction = $this->state->get('list.direction');
		$headers   = [
			'checkbox'    => HTML::_('grid.checkall'),
			'fullName'    => HTML::sort('NAME', 'fullName', $direction, $ordering),
			'email'       => HTML::sort('EMAIL', 'email', $direction, $ordering),
			'programName' => HTML::sort('PROGRAM', 'programName', $direction, $ordering),
		];

		if ($courseID = Input::getFilterID('course') and $courseID !== -1)
		{
			$headers['status']   = HTML::sort('STATE', 'status', $direction, $ordering);
			$headers['paid']     = HTML::sort('PAID', 'paid', $direction, $ordering);
			$headers['attended'] = HTML::sort('ATTENDED', 'attended', $direction, $ordering);
		}

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
		$link            = 'index.php?option=com_thm_organizer&view=participant_edit&id=';
		$statusTemplate  = '<span class="icon-ICONCLASS hasTooltip" title="STATUSTIP"></span>';
		$structuredItems = [];

		$setCourseToggles = ($courseID = Input::getFilterID('course') and $courseID !== -1) ? true : false;
		foreach ($this->items as $item)
		{
			$item->programName = Programs::getName($item->programID);

			if ($setCourseToggles)
			{
				if ($item->status)
				{
					$iconClass = 'checkbox-checked';
					$statusTip = Languages::_('THM_ORGANIZER_ACCEPTED');
				}
				else
				{
					$iconClass = 'checkbox-partial';
					$statusTip = Languages::_('THM_ORGANIZER_WAIT_LIST');
				}

				$status       = str_replace('ICONCLASS', $iconClass, $statusTemplate);
				$status       = str_replace('STATUSTIP', $statusTip, $status);
				$item->status = $status;

				$item->attended = $this->getAssocToggle(
					'courses',
					'courseID',
					$courseID,
					'participantID',
					$item->id,
					$item->attended,
					Languages::_('THM_ORGANIZER_TOGGLE_ATTENDED'),
					'attended'
				);

				$item->paid = $this->getAssocToggle(
					'courses',
					'courseID',
					$courseID,
					'participantID',
					$item->id,
					$item->paid,
					Languages::_('THM_ORGANIZER_TOGGLE_PAID'),
					'paid'
				);
			}

			$structuredItems[$index] = $this->structureItem($index, $item, $link . $item->id);
			$index++;
		}

		$this->items = $structuredItems;
	}
}
