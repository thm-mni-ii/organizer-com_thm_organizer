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

use Organizer\Helpers\Languages;

$dates       = $this->model->data;
$dayCount    = count($dates);
$blockCount  = count($this->model->grid['periods']);
$totalBlocks = $dayCount * $blockCount;
$labelIndex  = 'label_' . Languages::getShortTag();
$dayCount    = count($dates);
?>
<table>
    <thead>
    <tr>
        <th class="room-column day-row"></th>
        <?php
        foreach ($dates as $date => $blocks) {
            $dayConstant = strtoupper(date('l', strtotime($date)));
            $dayClass    = "day-row day-column columns-$dayCount-$blockCount";
            echo '<th class="' . $dayClass . '" colspan="' . $blockCount . '">';
            echo Languages::_($dayConstant) . '<br />' . Dates::formatDate($date);
            echo '</th>';
        }
        ?>
    </tr>
    <tr>
        <th class="room-column block-row"></th>
        <?php
        foreach ($dates as $date => $blocks) {
            $blockNo = 0;
            foreach ($this->model->grid['periods'] as $blockKey => $times) {
                if (empty($times[$labelIndex])) {
                    $blockNo++;
                    $shown = $blockNo;
                } else {
                    $shown = substr($times[$labelIndex], 0, 1);
                }
                echo '<th class="block-column block-row columns-' . $totalBlocks . '">' . $shown . '</th>';
            }
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
        foreach ($dates as $date => $blocks) {
            $blockNo = 0;
            foreach ($blocks as $blockKey => $rooms) {
                if (empty($this->model->grid['periods'][$blockKey][$labelIndex])) {
                    $blockNo++;
                    $blockName = $blockNo;
                } else {
                    $blockName = $this->model->grid['periods'][$blockKey][$labelIndex];
                }
                $blockTip = $this->getBlockTip($date, $blockKey, $blockName, $room['name']);
                if (empty($rooms[$roomID])) {
                    echo '<td class="block-column room-row columns-' . $totalBlocks . ' hasTip" title="' . $blockTip . '"></td>';
                } else {
                    $iconClass = count($rooms[$roomID]) > 1 ? 'grid' : 'square';
                    $blockTip  .= $this->getEventTips($rooms[$roomID]);
                    echo '<td class="block-column room-row columns-' . $totalBlocks . ' hasTip" title="' . $blockTip . '">';
                    echo '<span class="icon-' . $iconClass . '"></span>';
                    echo '</td>';
                }
            }
        }
        echo '</tr>';
    }
    ?>
    </tbody>
</table>
