<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        appointments layout for thm organizer's room display view
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Daniel Kirsten, <daniel.kirsten@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

header('meta http-equiv="refresh" content="5"');
$params = $this->model->params;
$appointments = $this->model->appointments;
$upcoming = $this->model->upcoming;
$dayName = strtoupper(date('l'));
$time = date('H:i');
?>
<script type="text/javascript">
    var timer = null;
    function auto_reload()
    {
        window.location = document.URL;
    }
    window.onload = function(){
        timer = setTimeout('auto_reload()', 6000);
    }
</script>
<div class='display-appointments'>
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
$appointmentsNo = 0;
if (count($appointments))
{
    $time = date('H:i');
?>
    <div class="thm_organizer_date_title"><?php echo  JText::_('COM_THM_ORGANIZER_RD_APPOINTMENTS'); ?></div>
<?php
    foreach ($this->appointments as $appointmentsKey => $appointments)
    {
        if ($appointmentsNo >= 10)
        {
            break;
        }
        $appointmentsClass = ($appointmentsNo % 2 == 0)? 'thm_organizer_es_even' : 'thm_organizer_es_odd';
        $activeClass = ($time >= $appointments['starttime'] AND $time <= $appointments['endtime'] AND count($this->appointments) > 1)?
                'thm_organizer_is_active' : '';
        $contentClass = ($appointments['title'] != JText::_('COM_THM_ORGANIZER_NO_LESSON'))? 'thm_organizer_is_full' : 'thm_organizer_is_empty';
?>
        <div class="thm_organizer_es_block <?php echo $appointmentsClass . " " . $activeClass; ?>">
            <div class="thm_organizer_es_data <?php  echo $contentClass; ?>">
                <span class="thm_organizer_is_title_span"><?php  echo $appointments['title']; ?></span>
<?php
        if (!empty($appointments['extraInformation']))
        {
?>
                <br />
                <span class="thm_organizer_is_extrainfo_span">
                    <?php  echo $appointments['extraInformation']; ?>
                </span>
<?php
        }
?>
            </div>
            <div class="thm_organizer_es_display_dates">
                <?php echo $appointments['displayDates']; ?>
            </div>
        </div>
<?php
        $appointmentsNo++;
    }
}
$upcomingNo = 0;
if (count($upcoming) && $appointmentsNo < 8)
{
    $time = date('H:i');
?>
        <div class="thm_organizer_date_title"><?php echo  JText::_('COM_THM_ORGANIZER_RD_UPCOMING'); ?></div>
<?php
    foreach ($this->upcoming as $upcomingKey => $upcoming)
    {
        if ((count($this->appointments)) ? ($appointmentsNo + $upcomingNo >= 9) : ($upcomingNo >= 10))
        {
            break;
        }
        $upcomingClass = ($upcomingNo % 2 == 0)? 'thm_organizer_es_even' : 'thm_organizer_es_odd';
        $contentClass = ($upcoming['title'] != JText::_('COM_THM_ORGANIZER_NO_LESSON'))? 'thm_organizer_is_full' : 'thm_organizer_is_empty';
?>
        <div class="thm_organizer_es_block <?php echo $upcomingClass ?>">
            <div class="thm_organizer_es_data <?php  echo $contentClass; ?>">
                <span class="thm_organizer_is_title_span"><?php  echo $upcoming['title']; ?></span>
<?php
        if (!empty($upcoming['extraInformation']))
        {
?>
                <br />
                <span class="thm_organizer_is_extrainfo_span">
                    <?php  echo $upcoming['extraInformation']; ?>
                </span>
<?php
        }
?>
            </div>
            <div class="thm_organizer_es_display_dates">
                <?php echo $upcoming['displayDates']; ?>
            </div>
        </div>
<?php
        $upcomingNo++;
    }
}
?>
    </div>
</div>
