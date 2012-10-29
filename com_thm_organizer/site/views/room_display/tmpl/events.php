<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        template for display of scheduled lessons on registered monitors
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @author      Daniel Kirsten danielDOTkirstenATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2012
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     2.5.4
 */
// No direct access
defined('_JEXEC') or die('Restricted access');
header('meta http-equiv="refresh" content="5"');
$imagepath = 'components/com_thm_organizer/assets/images/';
$this->thm_logo_image =
        JHtml::image($imagepath.'thm_logo_giessen.png', JText::_('COM_THM_ORGANIZER_RD_LOGO_GIESSEN'));
$this->thm_text_image =
        JHtml::image($imagepath.'thm_text_dinpro_compact.png', JText::_('COM_THM_ORGANIZER_RD_THM'));
?>
<script type="text/javascript">
var timer = null;
function auto_reload()
{
  window.location = document.URL;
}
window.onload = function(){
    timer = setTimeout('auto_reload()', 60000);
}
</script>
<div id="thm_organizer_is_registered">
    <div id="thm_organizer_is_head">
        <div id="thm_organizer_is_head_left">
            <div id="thm_organizer_is_head_upper">
                <div id="thm_organizer_is_thm_logo_div">
                    <?php echo $this->thm_logo_image; ?>
                </div>
                <div id="thm_organizer_is_divider_div"></div>
                <div id="thm_organizer_is_room_div">
                    <?php  echo $this->roomName; ?>
                </div>
            </div>
            <div id="thm_organizer_is_head_lower">
                <?php echo $this->thm_text_image; ?>
            </div>
        </div>
        <div id="thm_organizer_is_head_right">
            <?php echo JText::_(strtoupper(date('l'))); ?><br />
            <?php echo date('d.m.Y'); ?><br />
            <?php echo date('H:i'); ?>
        </div>
    </div><!-- end of head -->
    <div id="thm_organizer_is_break_div"></div>
    <div id="thm_organizer_is_schedule_area" class="thm_organizer_is_long">
    <?php $appointmentsNo = 0;
    if(count($this->appointments)){
        $time = date('H:i'); ?>
        <div class="thm_organizer_date_title"><?php echo  JText::_('COM_THM_ORGANIZER_RD_APPOINTMENTS'); ?></div>
        <?php foreach($this->appointments as $appointmentsKey => $appointments){
	        if ($appointmentsNo >= 10) break;
	        $appointmentsClass = ($appointmentsNo % 2 == 0)? 'thm_organizer_es_even' : 'thm_organizer_es_odd';
	        $activeClass = ($time >= $appointments['starttime'] AND $time <= $appointments['endtime'] AND count($this->appointments) > 1)? 
	        	'thm_organizer_is_active' : '';
	        $contentClass = ($appointments['title'] != JText::_('COM_THM_ORGANIZER_NO_LESSON'))? 'thm_organizer_is_full' : 'thm_organizer_is_empty';?>
	        <div class="thm_organizer_es_block <?php echo $appointmentsClass." ".$activeClass; ?>">
	            <div class="thm_organizer_es_data <?php  echo $contentClass; ?>">
	                <span class="thm_organizer_is_title_span"><?php  echo $appointments['title']; ?></span>
	            <?php if($appointments['extraInformation'] != ''): ?>
	                <br />
	                <span class="thm_organizer_is_extrainfo_span">
	                    <?php  echo $appointments['extraInformation']; ?>
	                </span>
	            <?php endif; ?>
	            </div>
	            <div class="thm_organizer_es_display_dates">
	                <?php echo $appointments['displayDates']; ?>
	            </div>
	        </div>
	    <?php $appointmentsNo++;}
    } ?>
    <?php $upcomingNo = 0;
    if(count($this->upcoming) && $appointmentsNo < 8) {
        $time = date('H:i'); ?>
        <div class="thm_organizer_date_title"><?php echo  JText::_('COM_THM_ORGANIZER_RD_UPCOMING'); ?></div>
        <?php foreach($this->upcoming as $upcomingKey => $upcoming){
	        if ((count($this->appointments)) ? ($appointmentsNo + $upcomingNo >= 9) : ($upcomingNo >= 10)) break;
	        $upcomingClass = ($upcomingNo % 2 == 0)? 'thm_organizer_es_even' : 'thm_organizer_es_odd';
	        $contentClass = ($upcoming['title'] != JText::_('COM_THM_ORGANIZER_NO_LESSON'))? 'thm_organizer_is_full' : 'thm_organizer_is_empty';?>
	        <div class="thm_organizer_es_block <?php echo $upcomingClass ?>">
	            <div class="thm_organizer_es_data <?php  echo $contentClass; ?>">
	                <span class="thm_organizer_is_title_span"><?php  echo $upcoming['title']; ?></span>
	            <?php if($upcoming['extraInformation'] != ''): ?>
	                <br />
	                <span class="thm_organizer_is_extrainfo_span">
	                    <?php  echo $upcoming['extraInformation']; ?>
	                </span>
	            <?php endif; ?>
	            </div>
	            <div class="thm_organizer_es_display_dates">
	                <?php echo $upcoming['displayDates']; ?>
	            </div>
	        </div>
	    <?php $upcomingNo++;}
    } ?>
    </div>
</div>
