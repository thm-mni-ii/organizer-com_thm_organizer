<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        template for the next 4 events in a registered room
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

$params = $this->model->params;
$metric = 0;
?>
<script type="text/javascript">
    var timer = null;
    function auto_reload()
    {
        window.location = document.URL;
    }
    window.onload = function() {
        timer = setTimeout('auto_reload()', <?php echo $params['schedule_refresh']; ?>000);
    }
</script>
<div class='display-events'>
    <div class='head'>
        <div class='banner'>
            <div class='thm-logo'><img src="media/com_thm_organizer/images/thm_logo.png" alt="THM-Logo"/></div>
            <div class="room-name"><?php echo $params['roomName']; ?></div>
        </div>
        <div class='date-info'>
            <div class='time'><?php echo date('H:i'); ?></div>
            <div class='date'><?php echo date('d.m.Y'); ?></div>
        </div>
    </div>
    <div class="display-area">
        <div class="exp-text"><?php echo JText::_('COM_THM_ORGANIZER_NEXT_4'); ?></div>
<?php
foreach ($this->model->events as $date => $events)
{
    if ($metric >= 4)
    {
        break;
    }
    $displayedEvents = 0; ?>
    <div class="event-date">
        <div class="event-date-head"><?php echo THM_OrganizerHelperComponent::formatDate($date); ?></div>
        <?php
        $rowNumber = 0;
        foreach ($events as $event)
        {
            if ($metric >= 4)
            {
                break;
            }
            foreach ($event['blocks'] as $block)
            {
                $metric++;
                if ($metric > 4)
                {
                    break;
                }
                $rowClass = 'row' . ($rowNumber % 2);
                $rowNumber++;
                $rooms = implode(', ', $block['rooms']);
                $speakersArray = array();
                foreach ($block['speakers'] as $speaker)
                {
                    $speakersArray[] = implode(', ', array_filter($speaker));
                }
                $speakers = implode(' / ', $speakersArray);
                $mainExtraClass = empty($event['comment'])? 'main-wide' : '';
                ?>
                <div class="<?php echo $rowClass; ?> ym-clearfix">
                    <div class="event-times">
                        <?php echo THM_OrganizerHelperComponent::formatTime($block['starttime']); ?><br/>
                        -<br/>
                        <?php echo THM_OrganizerHelperComponent::formatTime($block['endtime']); ?>
                    </div>
                    <div class="event-main <?php echo $mainExtraClass; ?>">
                        <div class="event-names"><?php echo $event['name']; ?></div>
                        <div class="event-speakers"><?php echo $speakers; ?></div>
                    </div>
<?php
                if (!empty($event['comment']))
                {
?>
                    <div class="event-comment">
                        <span class="comment-head">Kommentar:</span><br/>
                        <?php echo $event['comment']; ?>
                    </div>
<?php
                }
?>
                </div>
                <?php
            }
        }
        ?>
    </div>
<?php
}
?>
    </div>
</div>
