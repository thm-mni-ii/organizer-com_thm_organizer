<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        template for display of scheduled lessons on registered monitors
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

$blockCount = count($this->model->grid);
$dates = $this->model->data;
$dayCount = count($dates);
?>
<table>
    <thead>
    <tr>
        <th class="room-column day-row"></th>
<?php
foreach ($dates as $date => $blocks)
{
    $dayConstant = strtoupper(date('l', strtotime($date)));
    echo '<th class="date-span day-row day-width" colspan="' . $blockCount . '">';
    echo JText::_($dayConstant) . '<br />' . THM_OrganizerHelperComponent::formatDate($date);
    echo '</th>';
}
?>
    </tr>
    <tr>
        <th class="room-column block-row"></th>
<?php
foreach ($dates as $date => $blocks)
{
    foreach ($this->model->grid as $blockNo => $times)
    {
        echo '<th class="block-column block-row block-width">' . $blockNo . '</th>';
    }
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
    foreach ($dates as $date => $blocks)
    {
        foreach ($blocks as $blockNo => $block)
        {
            $blockTip = '';
            $blockTip .= $this->getBlockTip($date, $blockNo, $room);
            if (empty($block[$room]))
            {
                echo '<td class="block-column room-row block-width hasTip" title="' . $blockTip . '"></td>';
            }
            else
            {
                $iconClass = count($block[$room]) > 1? 'grid' : 'square';
                $blockTip .= $this->getEventTips($block[$room]);
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
