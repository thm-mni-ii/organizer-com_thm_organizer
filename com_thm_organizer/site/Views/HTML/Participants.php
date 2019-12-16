<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers as Helpers;

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
		$title = Helpers\Languages::_('THM_ORGANIZER_PARTICIPANTS');
		if ($this->clientContext !== self::BACKEND)
		{
			if ($courseID = Helpers\Input::getFilterID('course'))
			{
				$title .= ': ' . Helpers\Courses::getName($courseID);
			}
			elseif ($instanceID = Helpers\Input::getFilterID('course'))
			{
				$title .= ': ' . Helpers\Instances::getName($courseID);
			}
		}

		Helpers\HTML::setTitle($title, 'users');

		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton(
			'Standard',
			'edit',
			Helpers\Languages::_('THM_ORGANIZER_EDIT'),
			'participants.edit',
			true
		);

		if (Helpers\Can::administrate())
		{
			$toolbar->appendButton(
				'Standard',
				'attachment',
				Helpers\Languages::_('THM_ORGANIZER_MERGE'),
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
		$courseID = Helpers\Input::getFilterID('course', 0);

		return Helpers\Can::manage('course', $courseID);
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
			'checkbox'    => Helpers\HTML::_('grid.checkall'),
			'fullName'    => Helpers\HTML::sort('NAME', 'fullName', $direction, $ordering),
			'email'       => Helpers\HTML::sort('EMAIL', 'email', $direction, $ordering),
			'programName' => Helpers\HTML::sort('PROGRAM', 'programName', $direction, $ordering),
		];

		if ($courseID = Helpers\Input::getFilterID('course') and $courseID !== -1)
		{
			$headers['status']   = Helpers\HTML::sort('STATUS', 'status', $direction, $ordering);
			$headers['paid']     = Helpers\HTML::sort('PAID', 'paid', $direction, $ordering);
			$headers['attended'] = Helpers\HTML::sort('ATTENDED', 'attended', $direction, $ordering);
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

		$setCourseToggles = ($courseID = Helpers\Input::getFilterID('course') and $courseID !== -1) ? true : false;
		foreach ($this->items as $item)
		{
			$item->programName = Helpers\Programs::getName($item->programID);

			if ($setCourseToggles)
			{
				if ($item->status)
				{
					$iconClass = 'checkbox-checked';
					$statusTip = Helpers\Languages::_('THM_ORGANIZER_ACCEPTED');
				}
				else
				{
					$iconClass = 'checkbox-partial';
					$statusTip = Helpers\Languages::_('THM_ORGANIZER_WAIT_LIST');
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
					Helpers\Languages::_('THM_ORGANIZER_TOGGLE_ATTENDED'),
					'attended'
				);

				$item->paid = $this->getAssocToggle(
					'courses',
					'courseID',
					$courseID,
					'participantID',
					$item->id,
					$item->paid,
					Helpers\Languages::_('THM_ORGANIZER_TOGGLE_PAID'),
					'paid'
				);
			}

			$structuredItems[$index] = $this->structureItem($index, $item, $link . $item->id);
			$index++;
		}

		$this->items = $structuredItems;
	}
}
