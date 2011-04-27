<?php
defined('_JEXEC') or die('Restricted access');
echo "<pre>".print_r($this->eventsExist, true)."</pre>";
?>
<div id="thm_organizer_rd">
    <div id="thm_organizer_rd_head">
        <span id="thm_organizer_rd_room">
            <?php echo $this->name.JText::_('COM_THM_ORGANIZER_RD_ON'); ?>
        </span>
        <span id="thm_organizer_rd_date" >
            <?php echo "$this->day.", ".$this->date";?>
        </span>
    </div>
    <div id="thm_organizer_rd_lessons">
    <?php if($this->lessonsExist){ ?>
        <h3><?php echo JText::_('COM_THM_ORGANIZER_RD_PLANNED_LESSONS'); ?></h3>
        <table id="thm_organizer_rd_lessons_table" >
        <?php $row = 0; foreach($this->blocks as $blockKey => $block){
            $rowclass = ($row % 2 == 0)? "thm_organizer_rd_row_even" : "thm_organizer_rd_row_odd";
            if($block['lessonExists']){ ?>
            <tr class="<?php echo $rowclass; ?>">
                <td class="thm_organizer_rd_lesson_time">
                    <?php echo $block['displayTime']; ?>
                </td>
                <td class="thm_organizer_rd_lesson_name">
                    <?php echo $block['title']; ?>
                </td>
                <td class="thm_organizer_rd_lesson_teachers">
                    <?php echo $block['extraInformation']; ?>
                </td>
            </tr>
        <?php $row++; }} ?>
        </table>
    <?php }else{ ?>
        <h3><?php echo JText::_('COM_THM_ORGANIZER_RD_NO_LESSONS'); ?></h3>
    <?php } ?>
    </div>	
<?php if($this->eventsExist){ ?>
    <?php if(count($this->appointments) > 0){ ?>
    <div class="thm_organizer_rd_events" id="thm_organizer_rd_appointments" >
        <h3><?php echo JText::_('COM_THM_ORGANIZER_RD_APPOINTMENTS'); ?></h3>
	<table class="thm_organizer_rd_eventtable" id="thm_organizer_rd_appointments_table" >
        <?php $row = 0; foreach($this->appointments as $k => $appointment){
            $rowclass = ($row % 2 == 0)? "thm_organizer_rd_row_even" : "thm_organizer_rd_row_odd"; ?>
            <tr class='<?php echo $rowclass; ?>'>
                <td><?php echo $appointment['titleLink']; ?></td>
                <td><?php echo $appointment['timeLink']; ?></td>
                <td><?php echo $appointment['authorLink']; ?></td>
            </tr>
        <?php $row++; } ?>
        </table>
    </div>
    <?php } if(count($this->notices) > 0){ ?>
    <div class="thm_organizer_rd_events" id="thm_organizer_rd_notes" >
	<h3><?php echo JText::_('COM_THM_ORGANIZER_RD_NOTICES'); ?></h3>
	<table  class="thm_organizer_rd_eventtable" id="thm_organizer_rd_notices_table" >
        <?php $row = 0; foreach($this->notes as $k => $notice){
            $rowclass = ($row % 2 == 0)? "thm_organizer_rd_row_even" : "thm_organizer_rd_row_odd"; ?>
            <tr class='<?php echo $rowclass; ?>'>
                <td><?php echo $notice['titleLink']; ?></td>
                <td><?php echo $notice['timeLink']; ?></td>
                <td><?php echo $notice['authorLink']; ?></td>
            </tr>
        <?php $row++; } ?>
        </table>
    </div>
    <?php } if(count($this->upcoming) > 0){ ?>
    <div class="thm_organizer_rd_events" id="thm_organizer_rd_futureevents" >
	<h3>k&uuml;nftige Ereignisse die diesen Raum betreffen</h3>
	<table  class="thm_organizer_rd_eventtable" id="thm_organizer_rd_futureevents_table" >
        <?php $row = 0; foreach($this->futureevents as $k => $v){
            $rowclass = ($row % 2 == 0)? "thm_organizer_rd_row_even" : "thm_organizer_rd_row_odd"; ?>
            <tr class='<?php echo $rowclass; ?>'>
                <td><?php echo $notice['titleLink']; ?></td>
                <td><?php echo $notice['timeLink']; ?></td>
                <td><?php echo $notice['authorLink']; ?></td>
            </tr>
        <?php $row++; } ?>
        </table>
    </div>
    <?php } ?>
<?php }else { ?>
    <h3>Es gibt zur Zeit keine Anmerkungen zu den Ressourcen dieses Raumes.</h3>
<?php } ?>
</div>
