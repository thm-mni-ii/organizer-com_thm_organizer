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
require_once './schedule_events.php';

$params = $this->model->params;
$blocks = $this->model->blocks;
$appointments = $this->model->appointments;
$notices = $this->model->notices;
$information = $this->model->information;
$upcoming = $this->model->upcoming;
$eventsExist = (!empty($appointments) OR !empty($notices) OR !empty($information) OR !empty($upcoming));
$widthClass = $eventsExist? 'schedule-narrow' : 'schedule-wide';
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
    window.onload = function(){
        timer = setTimeout('auto_reload()', <?php echo $params['schedule_refresh']; ?>000);
    }
</script>
<div class='display-schedule'>
    <div class='head'>
        <div class='banner'>
            <div class='thm-logo'>Dummy Text for THM-LOGO</div>
            <div class="room-name"><?php echo $params['roomName']; ?></div>
            <div class="thm-text">Dummy Text for TECHNISCHE&nbsp;HOCHSCHULE&nbsp;MITTELHESSEN</div>
        </div>
        <div class='date-info'>
            <div class='weekday'><?php echo JText::_($dayName); ?></div>
            <div class='date'><?php echo date('d.m.Y'); ?></div>
            <div class='time'><?php echo $time; ?></div>
        </div>
    </div>
    <div class="schedule-area <?php echo $widthClass; ?>">
<?php
        if (!empty($blocks))
        {
            foreach ($blocks as $blockKey => $block)
            {
                $blockClass = ($blockNo % 2)? 'block-odd' : 'block-even';
                $activeClass = ($time >= $block['starttime'] and $time <= $block['endtime'])? 'active' : 'inactive';
?>
                <div class="schedule-block <?php echo $blockClass . ' ' . $activeClass; ?>">
                    <div class="block-time">
                        <?php echo $block['starttime'] . ' - ' . $block['endtime']; ?>
                    </div>
                    <div class="block-data">
                        <div class="block-title"><?php echo $block['title']; ?></div>
<?php
                if (!empty($block['extraInformation']))
                {
?>
                        <div class="block-extra"><?php echo $block['extraInformation']; ?></div>
<?php
                }
?>
                    </div>
                </div>
<?php
                $blockNo++;
            }
        }
        else
        {
?>
            <div class="schedule-block block-even incactive"><?php echo JText::_('COM_THM_ORGANIZER_NO_LESSONS'); ?></h2>
<?php
        }
?>
    </div>
<?php if ($eventsExist): ?>
    <div class="appointments-area">
        <?php THM_OrganizerTemplateSchedule_Events::render($appointments, JText::_('COM_THM_ORGANIZER_APPOINTMENTS_TITLE'), $metric, 7); ?>
        <?php THM_OrganizerTemplateSchedule_Events::render($notices, JText::_('COM_THM_ORGANIZER_NOTICES_TITLE'), $metric, 7); ?>
        <?php THM_OrganizerTemplateSchedule_Events::render($notices, JText::_('COM_THM_ORGANIZER_INFORMATION_TITLE'), $metric, 7); ?>
        <?php THM_OrganizerTemplateSchedule_Events::render($notices, JText::_('COM_THM_ORGANIZER_UPCOMING_TITLE'), $metric, 7); ?>
    </div>
<?php endif; ?>
</div>
