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

$blockCount = count($this->model->grid);
$dates      = $this->model->data;
$dayCount   = count($dates);
?>
<table>
    <thead>
    <tr>
        <th class="room-column day-row"></th>
        <?php
        foreach ($dates as $date => $blocks) {
            $dayConstant = strtoupper(date('l', strtotime($date)));
            echo '<th class="date-span day-row day-width" colspan="' . $blockCount . '">';
            echo $this->lang->_($dayConstant) . '<br />' . THM_OrganizerHelperComponent::formatDate($date);
            echo '</th>';
        }
        ?>
    </tr>
    <tr>
        <th class="room-column block-row"></th>
        <?php
        foreach ($dates as $date => $blocks) {
            foreach ($this->model->grid['periods'] as $blockNo => $times) {
                echo '<th class="block-column block-row block-width">' . $blockNo . '</th>';
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
            foreach ($blocks as $blockNo => $rooms) {
                $blockTip = $this->getBlockTip($date, $blockNo, $room['longname']);
                if (empty($rooms[$roomID])) {
                    echo '<td class="block-column room-row block-width hasTip" title="' . $blockTip . '"></td>';
                } else {
                    $iconClass = count($rooms[$roomID]) > 1 ? 'grid' : 'square';
                    $blockTip  .= $this->getEventTips($rooms[$roomID]);
                    echo '<td class="block-column room-row block-width hasTip" title="' . $blockTip . '">';
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
