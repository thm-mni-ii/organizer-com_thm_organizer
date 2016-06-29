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
		foreach ($this->model->grid as $times)
		{
			$startTime = THM_OrganizerHelperComponent::formatTime($times['starttime']);
			$endTime   = THM_OrganizerHelperComponent::formatTime($times['endtime']);
			echo '<th class="block-column block-row day-width ' . $columnClass . '">' . $startTime . ' - ' . $endTime . '</th>';
		}
		?>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($this->model->selectedRooms as $room)
	{
		echo '<tr>';
		echo '<th class="room-column room-row">' . $room . '</th>';
		foreach ($blocks as $blockNo => $block)
		{
			$blockTip = '';
			$blockTip .= $this->getBlockTip($startDate, $blockNo, $room);
			if (empty($block[$room]))
			{
				echo '<td class="block-column room-row day-width hasTip" title="' . $blockTip . '"></td>';
			}
			else
			{
				$iconClass = count($block[$room]) > 1 ? 'grid' : 'square';
				$blockTip .= $this->getEventTips($block[$room]);
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
