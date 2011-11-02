<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        room display default template
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
$document = JFactory::getDocument();
$document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");
$title = JText::_('COM_THM_ORGANIZER_RD_TITLE');
$title .= $this->roomname;
$title .= JText::_('COM_THM_ORGANIZER_RD_ON');
$title .= $this->displayDate;
$document->setTitle($title);

if(isset($model->roomSelectLink))
{
    $backSpan = "<span id='thm_organizer_back_span' class='thm_organizer_action_span'></span>";
    $backTip = JText::_('COM_THM_ORGANIZER_RD_RS_LINK_TITLE');
    $backTip .= "::";
    $backTip .= JText::_('COM_THM_ORGANIZER_RD_RS_LINK_TEXT');
    $attributes = array();
    $attributes['title'] = $backTip;
    $attributes['class'] = "hasTip thm_organizer_action_link";
    $backLink = JHtml::link($model->roomSelectLink, $backSpan.JText::_('COM_THM_ORGANIZER_RD_RS_LINK_TITLE'), $attributes);
    $this->backLink = $backLink;
}
?>
<div id="thm_organizer_rd">
    <div id="thm_organizer_rd_head">
        <div id ="thm_organizer_rd_title">
            <span id="thm_organizer_rd_highlight">
                <?php echo $this->roomname; ?>
            </span>
            <?php echo JText::_('COM_THM_ORGANIZER_RD_ON'); ?>
            <span id="thm_organizer_rd_highlight" >
                <?php echo date('d.m.Y', $this->date[0]); ?>
            </span>
        </div>
        <div id="thm_organizer_rd_button_div">
            <?php if(isset($this->backLink)) echo $this->backLink; ?>
        </div>
    </div>
    <div id="thm_organizer_rd_lessons">
    <?php if($this->lessonsExist){ ?>
        <h3><?php echo JText::_('COM_THM_ORGANIZER_RD_PLANNED_LESSONS'); ?></h3>
        <div class="thm_organizer_rd_table_container" >
            <table id="thm_organizer_rd_lessons_table" class="thm_organizer_rd_table" >
            <?php $row = 0; foreach($this->blocks as $blockKey => $block){
                $rowclass = ($row % 2 == 0)? "thm_organizer_rd_row_even" : "thm_organizer_rd_row_odd";
                if($block['type'] != 'empty'){ ?>
                <tr class="<?php echo $rowclass; ?>">
                    <td class="thm_organizer_rd_lesson_time">
                        <?php echo $block['displayTime']; ?>
                    </td>
                    <td class="thm_organizer_rd_lesson_name">
                        <?php echo ($block['type'] == 'COM_THM_ORGANIZER_RD_TYPE_APPOINTMENT')? $block['link'] : $block['title']; ?>
                    </td>
                    <td class="thm_organizer_rd_lesson_type">
                        <?php echo JText::_($block['type']); ?>
                    </td>
                </tr>
            <?php $row++; }} ?>
            </table>
        </div>
    <?php }else{ ?>
        <p><?php echo JText::_('COM_THM_ORGANIZER_RD_NO_LESSONS'); ?></p>
    <?php } ?>
    </div>	
<?php if($this->eventsExist){ ?>
    <?php if(count($this->appointments) > 0){ ?>
    <div class="thm_organizer_rd_events" id="thm_organizer_rd_appointments" >
        <h3><?php echo JText::_('COM_THM_ORGANIZER_RD_APPOINTMENTS'); ?></h3>
        <div class="thm_organizer_rd_table_container" >
            <table class="thm_organizer_rd_table" id="thm_organizer_rd_appointments_table" >
            <?php $row = 0; foreach($this->appointments as $appointment){
                $rowclass = ($row % 2 == 0)? "thm_organizer_rd_row_even" : "thm_organizer_rd_row_odd"; ?>
                <tr class='<?php echo $rowclass; ?>'>
                    <td class="thm_organizer_rd_event_title" >
                        <?php echo $appointment['link']; ?>
                    </td>
                    <td class="thm_organizer_rd_event_dates" >
                        <?php echo $appointment['displayDates']; ?>
                    </td>
                </tr>
            <?php $row++; } ?>
            </table>
        </div>
    </div>
    <?php } if(count($this->notices) > 0){ ?>
    <div class="thm_organizer_rd_events" id="thm_organizer_rd_notes" >
	<h3><?php echo JText::_('COM_THM_ORGANIZER_RD_NOTICES'); ?></h3>
        <div class="thm_organizer_rd_table_container" >
            <table  class="thm_organizer_rd_table" id="thm_organizer_rd_notices_table" >
            <?php $row = 0; foreach($this->notices as $notice){
                $rowclass = ($row % 2 == 0)? "thm_organizer_rd_row_even" : "thm_organizer_rd_row_odd"; ?>
                <tr class='<?php echo $rowclass; ?>'>
                    <td class="thm_organizer_rd_event_title" >
                        <?php echo $notice['link']; ?>
                    </td>
                    <td class="thm_organizer_rd_event_dates" >
                        <?php echo $notice['displayDates']; ?>
                    </td>
                </tr>
            <?php $row++; } ?>
            </table>
        </div>
    </div>
    <?php } if(count($this->upcoming) > 0){ ?>
    <div class="thm_organizer_rd_events" id="thm_organizer_rd_futureevents" >
	<h3><?php echo JText::_('COM_THM_ORGANIZER_RD_UPCOMING'); ?></h3>
        <div class="thm_organizer_rd_table_container" >
            <table  class="thm_organizer_rd_table" id="thm_organizer_rd_futureevents_table" >
            <?php $row = 0; foreach($this->upcoming as $coming){
                $rowclass = ($row % 2 == 0)? "thm_organizer_rd_row_even" : "thm_organizer_rd_row_odd"; ?>
                <tr class='<?php echo $rowclass; ?>'>
                    <td class="thm_organizer_rd_event_title" >
                        <?php echo $coming['link']; ?>
                    </td>
                    <td class="thm_organizer_rd_event_dates" >
                        <?php echo $coming['displayDates']; ?>
                    </td>
                </tr>
            <?php $row++; } ?>
            </table>
        </div>
    </div>
    <?php } ?>
<?php }else { ?>
    <p><?php echo JText::_('COM_THM_ORGANIZER_RD_NO_EVENTS'); ?></p>
<?php } ?>
</div>
