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

use Organizer\Helpers;

/**
 * Loads lesson and event data for a filtered set of rooms into the view context.
 */
class RoomOverview extends TableView
{
	const DAY = 1, TEACHER = 1, WEEK = 2, SPEAKER = 4, LAB = 14, UNKNOWN = 49;

	private $grid = null;

	private $gridID = null;

	/**
	 * Adds a toolbar and title to the view.
	 *
	 * @return void  sets context variables
	 */
	protected function addToolBar()
	{
		$resourceName = Helpers\Languages::_('THM_ORGANIZER_ROOM_OVERVIEW');
		if ($this->clientContext == self::FRONTEND)
		{
			if ($campusID = Helpers\Input::getInt('campusID'))
			{
				$resourceName .= ': ' . Helpers\Languages::_('THM_ORGANIZER_CAMPUS');
				$resourceName .= ' ' . Helpers\Campuses::getName($campusID);
			}
		}

		Helpers\HTML::setMenuTitle('THM_ORGANIZER_ROOM_OVERVIEW', $resourceName);

		return;
	}

	/**
	 * Builds the array of conditions used for instance retrieval.
	 *
	 * @param   int     $roomID  the id of the room being iterated
	 * @param   string  $date    the Y-m-d date to be requested
	 *
	 * @return array the conditions used to retrieve instances.
	 */
	private function getConditions($roomID, $date)
	{
		$conditions = [
			'date'            => $date,
			'delta'           => false,
			'endDate'         => $date,
			'interval'        => 'day',
			'mySchedule'      => false,
			'roomIDs'         => [$roomID],
			'showUnpublished' => Helpers\Can::administrate(),
			'startDate'       => $date,
			'userID'          => Helpers\Users::getID()
		];

		return $conditions;
	}

	/**
	 * Gets the cells for an individual day.
	 *
	 * @param   object  $room  the room to retrieve the cells for
	 * @param   string  $date  the Y-m-d date to retrieve the cells for
	 *
	 * @return array the cells for the specific day
	 */
	private function getDailyCells($room, $date)
	{
		$cells      = [];
		$conditions = $this->getConditions($room->id, $date);

		$instances = Helpers\Instances::getItems($conditions);
		if (isset($instances['futureDate']) or isset($instances['pastDate']))
		{
			$instances = [];
		}

		$data = ['date' => $date, 'instances' => $instances, 'name' => $room->name];

		if (empty($this->grid['periods']))
		{
			if (empty($data['instances']))
			{
				$cells[] = ['text' => ''];
			}
			else
			{
				$cells[] = $this->getDataCell($data);
			}
		}
		else
		{
			foreach (array_keys($this->grid['periods']) as $blockNo)
			{
				if (empty($data['instances']))
				{
					$cells[] = ['text' => ''];
					continue;
				}

				$data['blockNo'] = $blockNo;
				$cells[]         = $this->getDataCell($data);
			}
		}

		return $cells;
	}

	/**
	 * Creates an array of blocks.
	 *
	 * @param   bool  $short  true if the block labels should be abbreviated
	 *
	 * @return array the blocks of the time grid
	 */
	private function getHeaderBlocks($short = false)
	{
		$blocks = [];
		if (empty($this->grid['periods']))
		{
			$blocks[] = ['text' => ''];

			return $blocks;
		}

		$blocks     = [];
		$labelIndex = 'label_' . Helpers\Languages::getTag();

		foreach ($this->grid['periods'] as $number => $data)
		{
			$endTime   = Helpers\Dates::formatTime($data['endTime']);
			$startTime = Helpers\Dates::formatTime($data['startTime']);
			$timeText  = "$startTime - $endTime";

			if (!empty($data[$labelIndex]))
			{
				$alias = $data[$labelIndex];
				$text  = $short ? mb_substr($alias, 0, 1) : $alias;
				$tip   = $short ? "<div class=\"cellTip\">$alias ($timeText)</div>" : '';
			}
			else
			{
				$text = $short ? $number : $timeText;
				$tip  = $short ? "<div class=\"cellTip\">$timeText</div>" : '';
			}

			if ($tip)
			{
				$tip  = htmlentities($tip);
				$html = "<span class=\"hasTooltip\" title=\"$tip\">$text</span>";
			}
			else
			{
				$html = $text;
			}

			$block = ['text' => $html];

			$blocks[$number] = $block;
		}

		return $blocks;
	}

	/**
	 * Processes an individual list item resolving it to an array of table data values.
	 *
	 * @param   object  $room  the resource whose information is displayed in the row
	 *
	 * @return array an array of property columns with their values
	 */
	protected function getRow($room)
	{
		$date = $this->state->get('list.date');

		if ((int) $this->state->get('list.template') === self::WEEK)
		{
			$row         = [];
			$dates       = Helpers\Dates::getWeek($date, $this->grid['startDay'], $this->grid['endDay']);
			$currentDate = $dates['startDate'];
			while ($currentDate <= $dates['endDate'])
			{
				$dailyCells  = $this->getDailyCells($room, $currentDate);
				$row         = array_merge($row, $dailyCells);
				$currentDate = date('Y-m-d', strtotime("$currentDate + 1 days"));
			}
		}
		else
		{
			$row = $this->getDailyCells($room, $date);
		}

		$label = $this->getRowLabel($room);
		array_unshift($row, $label);

		return $row;
	}

	/**
	 * Creates a label with tooltip for the resource row.
	 *
	 * @param   object  $room  the resource to be displayed in the row
	 *
	 * @return array  the label inclusive tooltip to be displayed
	 */
	protected function getRowLabel($room)
	{
		$tip = "<div class=\"cellTip\"><span class=\"cellTitle\">$room->name</span>";
		$tip .= ($room->typeName or $room->capacity) ? "<div class=\"labelTip\">" : '';

		if ($room->typeName)
		{
			$tip .= $room->typeName;
			if ((int) $room->roomtypeID === self::LAB)
			{
				if (!empty($room->roomDesc))
				{
					$tip .= ":<br>$room->roomDesc";
				}
			}
			elseif ((int) $room->roomtypeID !== self::UNKNOWN and !empty($room->typeDesc))
			{
				$tip .= ":<br>$room->typeDesc";
			}
			$tip .= $room->capacity ? '<br>' : '';
		}

		if ($room->capacity)
		{
			$tip .= Helpers\Languages::_('THM_ORGANIZER_CAPACITY');
			$tip .= ": $room->capacity";
		}

		$tip  .= ($room->typeName or $room->capacity) ? '</div></div>' : '</div>';
		$tip  = htmlentities($tip);
		$text = "<span class=\"hasTooltip\" title=\"$tip\">$room->name</span>";

		$label          = [];
		$label['label'] = $text;

		return $label;
	}

	/**
	 * Processes an individual list item resolving it to an array of table data values.
	 *
	 * @param   array  $data  the data to be used to generate the cell contents
	 *
	 * @return array an array of property columns with their values
	 */
	protected function getDataCell($data)
	{
		if (empty($data['blockNo']))
		{
			$noGrid    = true;
			$endTime   = '';
			$startTime = '';
		}
		else
		{
			$blockNo   = $data['blockNo'];
			$endTime   = Helpers\Dates::formatTime($this->grid['periods'][$blockNo]['endTime']);
			$noGrid    = false;
			$startTime = Helpers\Dates::formatTime($this->grid['periods'][$blockNo]['startTime']);
		}

		$instances = $data['instances'];

		$relevantInstances = 0;
		$tips              = [];

		foreach ($instances as $instance)
		{
			if (!$noGrid)
			{
				if ($instance['endTime'] <= $startTime or $instance['startTime'] >= $endTime)
				{
					continue;
				}
			}

			$times = "{$instance['startTime']} - {$instance['endTime']}";
			$tip   = '<div class="cellTip">';
			if ($noGrid or $instance['endTime'] !== $endTime or $instance['startTime'] !== $startTime)
			{
				$tip .= "($times)<br>";
			}

			$tip .= '<span class="cellTitle">' . $instance['name'];
			$tip .= $instance['method'] ? " - {$instance['method']}" : '';
			$tip .= '</span><br>';

			$tip .= Helpers\Languages::_('THM_ORGANIZER_DEPT_ORG') . ":";
			$tip .= strlen($instance['department']) > 20 ? '<br>' : ' ';
			$tip .= "{$instance['department']}<br>";

			$persons = [];
			foreach ($instance['resources'] as $personID => $personAssoc)
			{
				if ((int) $personAssoc['roleID'] === self::TEACHER or (int) $personAssoc['roleID'] === self::SPEAKER)
				{
					$persons[$personID] = $personAssoc['person'];
				}
			}

			if ($persons)
			{
				$tip     .= Helpers\Languages::_('THM_ORGANIZER_PERSONS') . ":";
				$persons = implode(', ', $persons);
				$tip     .= strlen($persons) > 20 ? '<br>' : ' ';
				$tip     .= "$persons<br>";
			}

			if ($instance['comment'])
			{
				$tip .= Helpers\Languages::_('THM_ORGANIZER_EXTRA_INFORMATION') . ":";
				$tip .= strlen($instance['comment']) > 20 ? '<br>' : ' ';
				$tip .= "{$instance['comment']}<br>";
			}

			$index = "$times {$instance['departmentID']} {$instance['name']} {$instance['method']}";

			$tip          .= '</div>';
			$tips[$index] = $tip;
			$relevantInstances++;
		}

		$cell['text'] = '';

		if ($tips)
		{
			if ($noGrid)
			{
				$icons = [];
				foreach ($tips as $tip)
				{
					$tip     = htmlentities($tip);
					$icons[] = "<span class=\"icon-square hasTooltip\" title=\"$tip\"'></span>";
				}

				$cell['text'] = implode(' ', $icons);
			}
			else
			{
				$iconClass    = count($tips) > 1 ? 'grid' : 'square';
				$date         = Helpers\Dates::formatDate($data['date'], true);
				$cellTip      = '<div class="cellTip">';
				$cellTip      .= "<span class=\"cellTitle\">$date<br>$startTime - $endTime</span>";
				$cellTip      .= implode('', $tips);
				$cellTip      .= '<div>';
				$cellTip      = htmlentities($cellTip);
				$cell['text'] = "<span class=\"icon-$iconClass hasTooltip\" title=\"$cellTip\"></span>";
			}
		}

		return $cell;
	}

	/**
	 * Sets the table header information
	 *
	 * @return void sets the headers property
	 */
	protected function setHeaders()
	{
		$date     = $this->state->get('list.date');
		$headers  = [];
		$template = $this->state->get('list.template');

		if ((int) $template === self::WEEK)
		{
			$blocks    = $this->getHeaderBlocks(true);
			$headers[] = ['text' => '', 'columns' => []];
			$dates     = Helpers\Dates::getWeek($date, $this->grid['startDay'], $this->grid['endDay']);

			$currentDate = $dates['startDate'];
			while ($currentDate <= $dates['endDate'])
			{
				$formattedDate           = Helpers\Dates::formatDate($currentDate);
				$headers[$formattedDate] = ['text' => $formattedDate, 'columns' => $blocks];
				$currentDate             = date('Y-m-d', strtotime("$currentDate + 1 days"));
			}
		}
		elseif (empty($this->grid['periods']))
		{
			$headers = [['text' => ''], ['text' => Helpers\Dates::formatDate($date)]];
		}
		else
		{
			$blocks  = $this->getHeaderBlocks();
			$headers = $blocks;
			array_unshift($headers, ['text' => '']);
		}

		$this->headers = $headers;
	}

	/**
	 * Function to set attributes unique to individual tables.
	 *
	 * @return void sets attributes specific to individual tables
	 */
	protected function setInheritingProperties()
	{
		if (!$gridID = $this->state->get('list.gridID') and $campusID = Helpers\Input::getParams()->get('campusID'))
		{
			$gridID = Helpers\Campuses::getGridID($campusID);
		}

		if (empty($gridID))
		{
			$gridID = Helpers\Grids::getDefault();
		}

		$this->grid   = json_decode(Helpers\Grids::getGrid($gridID), true);
		$this->gridID = $gridID;
	}
}
