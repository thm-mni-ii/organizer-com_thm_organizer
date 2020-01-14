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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Class loads room statistic information into the display context.
 */
class RoomStatistics extends SelectionView
{
	/**
	 * Modifies document variables and adds links to external files
	 *
	 * @return void
	 */
	protected function modifyDocument()
	{
		parent::modifyDocument();

		//$this->hiddenFields = ['date'];

		Factory::getDocument()->addScript(Uri::root() . 'components/com_thm_organizer/js/room_statistics.js');
	}

	private function setBaseFields()
	{
		$this->sets['basic'] = ['label' => 'THM_ORGANIZER_BASIC_SETTINGS'];

		$intervals = [
			'week'     => 'THM_ORGANIZER_WEEK',
			'month'    => 'THM_ORGANIZER_MONTH',
			'semester' => 'THM_ORGANIZER_SEMESTER'
		];
		$this->setListField('interval', 'basic', $intervals, ['onChange' => 'handleInterval();'], 'week');

		$date = '<input name="date" type="date" value="' . date('Y-m-d') . '">';
		$this->setField('date', 'basic', 'THM_ORGANIZER_DATE', $date);
	}

	/**
	 * Sets form fields used to filter the resources available for selection.
	 *
	 * @return void modifies the sets property
	 */
	private function setFilterFields()
	{
		$this->sets['filters'] = ['label' => 'THM_ORGANIZER_FILTERS'];

		$deptAttribs = [
			'multiple' => 'multiple',
			'onChange' => 'repopulateTerms();repopulateCategories();repopulateRooms();'
		];
		$this->setResourceField('department', 'filters', $deptAttribs, true);

		$categoryAttribs = ['multiple' => 'multiple', 'onChange' => 'repopulateRooms();'];
		$this->setResourceField('category', 'filters', $categoryAttribs);

		$roomtypeAttribs = ['multiple' => 'multiple', 'onChange' => 'repopulateRooms();'];
		$this->setResourceField('roomtype', 'content', $roomtypeAttribs);

		$roomAttribs = ['multiple' => 'multiple'];
		$this->setResourceField('room', 'content', $roomAttribs);
	}

	/**
	 * Function to define field sets and fill sets with fields
	 *
	 * @return void sets the fields property
	 */
	protected function setSets()
	{
		$this->setBaseFields();
		$this->setFilterFields();
	}
}
