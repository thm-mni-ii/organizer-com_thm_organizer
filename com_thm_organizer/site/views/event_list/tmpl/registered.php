<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        template for display of scheduled lessons on registered monitors
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Daniel Kirsten, <daniel.kirsten@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

$params = $this->model->params;
$dayName = strtoupper(date('l'));
$time = date('H:i');
$blockNo = 0;
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
  }
</script>
<div class='display-events'>
    <div class='head'>
        <div class='banner'>
            <div class='thm-logo'><img src="media/com_thm_organizer/images/thm_logo.png" alt="THM-Logo"/><!--Dummy Text for THM-LOGO--></div>
            <div class="room-name"><?php echo $params['roomName']; ?></div>
   <!--         <div class="thm-text"><img src="media/com_thm_organizer/images/thm_text_dinpro_compact.png" alt="THM-Logo"/><!--Dummy Text for TECHNISCHE&nbsp;HOCHSCHULE&nbsp;MITTELHESSEN</div> -->
        </div>
        <div class='date-info'>
            <div class='time'><?php echo $time; ?></div>
            <div class='date'><?php echo date('d.m.Y'); ?></div>
        </div>
    </div>
    <div class="display-area">
    <?php foreach ($this->model->events as $date => $events):
        $displayedEvents = 0; ?>
        <div class="event-date">
            <div class="event-date-head"><?php echo THM_OrganizerHelperComponent::formatDate($date); ?></div>
<?php
$rowNumber = 0;
foreach ($events as $event):
    foreach ($event['blocks'] as $block):
        $rowClass = 'row' . ($rowNumber % 2);
        $rowNumber++;
        $rooms = implode(', ', $block['rooms']);
        $speakersArray = array();
        foreach ($block['speakers'] as $speaker)
        {
            $speakersArray[] = implode(', ', array_filter($speaker));
        }
        $speakers = implode(' / ', $speakersArray); ?>
            <div class="<?php echo $rowClass; ?>">
                <div class="event-times">
                    <?php echo THM_OrganizerHelperComponent::formatTime($block['starttime']); ?><br/>
                    -<br/>
                    <?php echo THM_OrganizerHelperComponent::formatTime($block['endtime']); ?>
                </div>
                <div class="event-names"><?php echo $event['name']; ?></div>
                <div class="event-speakers"><?php echo $speakers; ?></div>
                <div class="event-comment"><?php echo $event['comment']; ?></div>
            </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    </div>
</div>
