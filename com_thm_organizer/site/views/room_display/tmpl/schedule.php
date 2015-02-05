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

$params = $this->model->params;
$blocks = $this->model->blocks;
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

        /* Falls keine aktive Veranstaltung vorliegt, soll der vorhandene Platz genutzt werden,
        * dazu wird ein neuer Klassennamen benötigt.
         */
        var scheduleBlockElement = document.getElementsByClassName("schedule-block");
        var bool = testforactiveelements(scheduleBlockElement);
        if (bool==false){
           for (var i=0; i<scheduleBlockElement.length; i++) {
               scheduleBlockElement[i].className += " nothingActive";
           }
       }
    }

     testforactiveelements = function( scheduleBlockElement){
         var bool=false;
      for (var i=0; i<scheduleBlockElement.length; i++) {
          if (scheduleBlockElement[i].classList.contains("active")) {
             var  bool = true;
          }
      }
         return bool;
  }
</script>
<div class='display-schedule'>
    <div class='head'>
        <div class='banner'>
            <div class='thm-logo'><img src="media/com_thm_organizer/images/thm_logo.png" alt="THM-Logo"/><!--Dummy Text for THM-LOGO--></div>
            <div class="room-name"><?php echo $params['roomName']; ?></div>
   <!--         <div class="thm-text"><img src="media/com_thm_organizer/images/thm_text_dinpro_compact.png" alt="THM-Logo"/><!--Dummy Text for TECHNISCHE&nbsp;HOCHSCHULE&nbsp;MITTELHESSEN</div> -->
        </div>
        <div class='date-info'>
            <div class='time'><?php echo $time; ?></div>
            <div class='weekday'><?php echo JText::_($dayName); ?></div>
            <div class='date'><?php echo date('d.m.Y'); ?></div>
        </div>
    </div>
    <div class="schedule-area schedule-wide">
<?php
if (!empty($blocks))
{
    foreach ($blocks as $blockKey => $block)
    {
        $blockClass = ($blockNo % 2)? 'block-odd' : 'block-even';
        $activeClass = ($time >= $block->starttime and $time <= $block->endtime)? 'active' : 'inactive';
?>
        <div class="schedule-block <?php echo $blockClass . ' ' . $activeClass; ?>">
            <div class="block-time">
                <?php echo $block->starttime . ' - ' . $block->endtime; ?>
            </div>
            <div class="block-data">
<?php
        if (!empty($block->lessons))
        {
            echo '<div class="block-title">';
            foreach ($block->lessons as $lesson)
            {
                echo '<span class="lesson-title">' . $lesson['title'] . '</span>';
                echo '<span class="lesson-time">' . $lesson['time'] . '</span>';
                echo '<br />';
            }
            echo '</div>';
            echo '<div class="block-extra">';
            foreach ($block->lessons as $lesson)
            {
                echo '<span class="lesson-teacher">' . $lesson['teacher'] . '</span>';
                echo '<br />';
            }
            echo '</div>';
        }
?>
            </div>
        </div>
<?php
        $blockNo++;
    }
}
?>
    </div>
</div>
