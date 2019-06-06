<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

$params = $this->model->params;
$metric = 0;
?>
<script type="text/javascript">
    var timer = null;

    function auto_reload()
    {
        window.location = document.URL;
    }

    window.onload = function () {
        timer = setTimeout('auto_reload()', <?php echo $params['schedule_refresh']; ?>000);
    }
</script>
<div class='display-events'>
    <div class='head'>
        <div class='banner'>
            <div class='thm-logo'><img src="components/com_thm_organizer/images/thm_logo.png" alt="THM-Logo"/></div>
            <div class="room-name"><?php echo $params['roomName']; ?></div>
        </div>
        <div class='date-info'>
            <div class='time'><?php echo date('H:i'); ?></div>
            <div class='date'><?php echo date('d.m.Y'); ?></div>
        </div>
    </div>
    <div class="display-area">
        <div class="exp-text"><?php echo Languages::_('THM_ORGANIZER_NEXT_4'); ?></div>
        <?php
        foreach ($this->model->events as $date => $times) {
            if ($metric >= 4) {
                break;
            }
            $displayedEvents = 0; ?>
            <div class="event-date">
                <div class="event-date-head"><span><?php echo Dates::formatDate($date); ?></span>
                </div>
                <?php
                $rowNumber = 0;
                foreach ($times as $time => $lessons) {
                    if ($metric >= 4) {
                        break;
                    }
                    foreach ($lessons as $lesson) {
                        $metric++;
                        if ($metric > 4) {
                            break;
                        }
                        $rowClass = 'row' . ($rowNumber % 2);
                        $rowNumber++;
                        $paddingClass = empty($lesson['comment']) ? 'fluffy' : '';
                        ?>
                        <div class="<?php echo $rowClass; ?> ym-clearfix">
                            <div class="event-times">
                                <?php echo Dates::formatTime($lesson['startTime']); ?><br/>
                                -<br/>
                                <?php echo Dates::formatTime($lesson['endTime']); ?>
                            </div>
                            <div class="event-main">
                                <div class="event-names <?php echo $paddingClass; ?>">
                                    <?php
                                    echo implode(' / ', $lesson['titles']);
                                    if (!empty($lesson['method'])) {
                                        echo ' - ' . $lesson['method'];
                                    }
                                    ?>
                                </div>
                                <div class="event-teachers"><?php echo implode(' / ', $lesson['teachers']); ?></div>
                                <?php
                                if (!empty($lesson['comment'])) {
                                    ?>
                                    <div class="event-comment">
                                        (<?php echo $lesson['comment']; ?>)
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
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
