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

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
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
		$isSite             = $this->clientContext !== self::BACKEND;
		$courseID           = Helpers\Input::getFilterID('course');
		$courseParticipants = ($courseID and $isSite);

		$title = Helpers\Languages::_('THM_ORGANIZER_PARTICIPANTS');
		if ($courseParticipants)
		{
			$teaches = Helpers\Courses::teaches($courseID);
			$title   .= ': ' . Helpers\Courses::getName($courseID);
		}
		else
		{
			$teaches = false;
		}

		Helpers\HTML::setTitle($title, 'users');

		$admin       = Helpers\Can::administrate();
		$coordinates = $courseID ? Helpers\Courses::coordinates($courseID) : false;
		$toolbar     = Toolbar::getInstance();

		if ($admin or $coordinates)
		{
			$toolbar->appendButton(
				'Standard',
				'edit',
				Helpers\Languages::_('THM_ORGANIZER_EDIT'),
				'participants.edit',
				true
			);
		}

		if ($courseParticipants)
		{
			$toolbar->appendButton(
				'Standard',
				'signup',
				Helpers\Languages::_('THM_ORGANIZER_ACCEPT'),
				'participants.accept',
				true
			);

			$toolbar->appendButton(
				'Standard',
				'list-3',
				Helpers\Languages::_('THM_ORGANIZER_PLACE_WAIT_LIST'),
				'participants.wait',
				true
			);

			if ($admin or $coordinates or $teaches)
			{
				$toolbar->appendButton(
					'Confirm',
					Helpers\Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
					'delete',
					Helpers\Languages::_('THM_ORGANIZER_DELETE'),
					'participants.delete',
					true
				);
			}

			$if          = "alert('" . Helpers\Languages::_('THM_ORGANIZER_LIST_SELECTION_WARNING') . "');";
			$else        = "jQuery('#modal-circular').modal('show'); return true;";
			$script      = 'onclick="if(document.adminForm.boxchecked.value==0){' . $if . '}else{' . $else . '}"';
			$batchButton = '<button id="group-publishing" data-toggle="modal" class="btn btn-small" ' . $script . '>';

			$title       = Helpers\Languages::_('THM_ORGANIZER_MAIL');
			$batchButton .= '<span class="icon-envelope" title="' . $title . '"></span>' . " $title";

			$batchButton .= '</button>';

			$toolbar->appendButton('Custom', $batchButton, 'batch');

			$toolbar->appendButton(
				'Standard',
				'tags',
				Helpers\Languages::_('THM_ORGANIZER_BADGE_SHEETS'),
				'participants.printBadges',
				true
			);
		}

		if ($admin)
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
		if ($courseID = Helpers\Input::getFilterID('course'))
		{
			return Helpers\Can::manage('course', $courseID);
		}

		return Helpers\Can::administrate();
	}

	/**
	 * Method to create a list output
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		// Set batch template path
		$this->batch = ['batch_circular'];

		parent::display($tpl);
	}

	/**
	 * Modifies document variables and adds links to external files
	 *
	 * @return void
	 */
	protected function modifyDocument()
	{
		parent::modifyDocument();

		Factory::getDocument()->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/modal.css');
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
