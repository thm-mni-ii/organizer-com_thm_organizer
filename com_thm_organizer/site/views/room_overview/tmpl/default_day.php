<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

$colNo      = count($this->model->grid['periods']);
$labelIndex = 'label_' . Languages::getShortTag();
$startDate  = $this->model->startDate;
$blocks     = $this->model->data[$startDate];
?>
<table>
    <thead>
    <tr>
        <th class="room-column block-row"></th>
        <?php
        foreach ($this->model->grid['periods'] as $times) {
            $startTime  = THM_OrganizerHelperDate::formatTime($times['startTime']);
            $endTime    = THM_OrganizerHelperDate::formatTime($times['endTime']);
            $columnText = empty($times[$labelIndex]) ? "$startTime - $endTime" : $times[$labelIndex];
            echo '<th class="block-column block-row columns-' . $colNo . '">' . $columnText . '</th>';
        }
        ?>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($this->model->rooms as $roomID => $room) {
        echo '<tr>';
        $roomTip = $this->getRoomTip($room);
        echo '<th class="room-column room-row hasTip" title="' . $roomTip . '">' . $room['name'] . '</th>';
        $blockNo = 0;
        foreach ($blocks as $blockKey => $rooms) {
            if (empty($this->model->grid['periods'][$blockKey][$labelIndex])) {
                $blockNo++;
                $blockName = $blockNo;
            } else {
                $blockName = $this->model->grid['periods'][$blockKey][$labelIndex];
            }
            $blockTip = $this->getBlockTip($startDate, $blockKey, $blockName, $room['longname']);
            if (empty($rooms[$roomID])) {
                echo '<td class="block-column room-row columns-' . $colNo . ' hasTip" title="' . $blockTip . '"></td>';
            } else {
                $iconClass = count($rooms[$roomID]) > 1 ? 'grid' : 'square';
                $blockTip  .= $this->getEventTips($rooms[$roomID]);
                echo '<td class="block-column room-row columns-' . $colNo . ' hasTip" title="' . $blockTip . '">';
                echo '<span class="icon-' . $iconClass . '"></span>';
                echo '</td>';
            }
        }
        echo '</tr>';
    }
    ?>
    </tbody>
</table>
