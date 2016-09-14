<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
$columnClass = 'days-' . count($this->model->grid);
$startDate   = $this->model->startDate;
$blocks      = $this->model->data[$startDate];
?>
<table>
	<thead>
	<tr>
		<th class="room-column block-row"></th>
		<?php
		foreach ($this->model->grid['periods'] as $times)
		{
			$startTime = THM_OrganizerHelperComponent::formatTime($times['startTime']);
			$endTime   = THM_OrganizerHelperComponent::formatTime($times['endTime']);
			echo '<th class="block-column block-row day-width ' . $columnClass . '">' . $startTime . ' - ' . $endTime . '</th>';
		}
		?>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($this->model->selectedRooms as $roomID => $room)
	{
		echo '<tr>';
		echo '<th class="room-column room-row">' . $room . '</th>';
		foreach ($blocks as $blockNo => $rooms)
		{
			$blockTip = '';
			$blockTip .= $this->getBlockTip($startDate, $blockNo, $room);
			if (empty($rooms[$roomID]))
			{
				echo '<td class="block-column room-row day-width hasTip" title="' . $blockTip . '"></td>';
			}
			else
			{
				$iconClass = count($rooms[$roomID]) > 1 ? 'grid' : 'square';
				$blockTip .= $this->getEventTips($rooms[$roomID]);
				echo '<td class="block-column room-row day-width hasTip" title="' . $blockTip . '">';
				echo '<span class="icon-' . $iconClass . '"></span>';
				echo '</td>';
			}
		}
		echo '</tr>';
	}
	?>
	</tbody>
</table>
