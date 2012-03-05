<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        template for display of scheduled lessons on registered monitors
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     1.7.0
 */
// No direct access
defined('_JEXEC') or die('Restricted access');
$imagepath = 'components/com_thm_organizer/assets/images/';
$this->thm_logo_image =
        JHtml::image($imagepath.'thm_logo_giessen.png', JText::_('COM_THM_ORGANIZER_RD_LOGO_GIESSEN'));
$this->thm_text_image =
        JHtml::image($imagepath.'thm_text_dinpro_compact.png', JText::_('COM_THM_ORGANIZER_RD_THM'));
?>
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
    </div>
    <div id="thm_organizer_is_break_div"></div>
    <?php $widthClass = ($this->eventsExist)? 'thm_organizer_is_short' : 'thm_organizer_is_long'; ?>
    <div id="thm_organizer_is_schedule_area" class="<?php echo $widthClass; ?>">
    <?php if(count($this->blocks)){
        $blockNo = 0; $time = date('H:i');
        foreach($this->blocks as $blockKey => $block){
        $blockClass = ($blockNo % 2 == 0)? 'thm_organizer_is_even' : 'thm_organizer_is_odd';
        $activeClass = ($time >= $block['starttime'] and $time <= $block['endtime'])? 'thm_organizer_is_active' : '';
        $contentClass = ($block['title'] != JText::_('COM_THM_ORGANIZER_NO_LESSON'))? 'thm_organizer_is_full' : 'thm_organizer_is_empty';?>
        <div class="thm_organizer_is_block <?php echo $blockClass." ".$activeClass; ?>">
            <div class="thm_organizer_is_time_div">
                <?php echo $block['starttime']; ?><br />-<br /><?php echo $block['endtime']; ?>
            </div>
            <div class="thm_organizer_is_data <?php  echo $contentClass; ?>">
                <span class="thm_organizer_is_title_span"><?php  echo $block['title']; ?></span>
            <?php if($block['extraInformation'] != ''): ?>
                <br />
                <span class="thm_organizer_is_extrainfo_span">
                    <?php  echo $block['extraInformation']; ?>
                </span>
            <?php endif; ?>
            </div>
        </div>
    <?php $blockNo++;}}else{ ?>
        <br /><br /><h2><?php echo JText::_('COM_THM_ORGANIZER_RD_NO_LESSONS'); ?></h2>
    <?php } ?>
    </div>
    <?php if($this->eventsExist){ $metric = 0;?>
    <div id="thm_organizer_is_events_area">
        <?php if(count($this->appointments) > 0){ $metric++;?>
            <h1><?php echo JText::_('COM_THM_ORGANIZER_RD_APPOINTMENTS_REGISTERED'); ?></h1>
            <ul>
            <?php foreach($this->appointments as  $event){ $metric++; if($metric < 6){?>
            <li>
                <h2><?php echo $event['title']; ?></h2>
                <p><?php echo $event['displayDates']; ?></p>
                <p><?php echo $event['description']; ?></p>
            </li>
            <?php }} ?>
            </ul>
        <?php } if(count($this->notices) > 0 and $metric < 5){ $metric++; ?>
	<h1><?php echo JText::_('COM_THM_ORGANIZER_RD_NOTICES_REGISTERED'); ?></h1>
	<ul>
        <?php foreach($this->notices as $event){ $metric++; if($metric < 7){?>
            <li>
                <h2><?php echo $event['title']; ?></h2>
                <p><?php echo $event['displayDates']; ?></p>
                <p><?php echo $event['description']; ?></p>
            </li>
        <?php }} ?>
        </ul>
        <?php } if(count($this->information) > 0 and $metric < 5){ $metric ++?>
	<h1><?php echo JText::_('COM_THM_ORGANIZER_RD_INFORMATION_REGISTERED'); ?></h1>
	<ul>
        <?php foreach($this->information as $event){ $metric++; if($metric < 7){ ?>
            <li>
                <h2><?php echo $event['title']; ?></h2>
                <p><?php echo $event['displayDates']; ?></p>
                <p><?php echo $event['description']; ?></p>
            </li>
        <?php }} ?>
        </ul>
        <?php } if(count($this->upcoming) > 0 and $metric < 5){ $metric++; ?>
	<h1><?php echo JText::_('COM_THM_ORGANIZER_RD_UPCOMING_REGISTERED'); ?></h1>
	<ul>
        <?php foreach($this->upcoming as $event){ $metric++; if($metric < 7){ ?>
            <li>
                <h2><?php echo $event['title']; ?></h2>
                <p><?php echo $event['displayDates']; ?></p>
                <p><?php echo $event['description']; ?></p>
            </li>
        <?php }} ?>
        </ul>
        <?php } ?>
    </div>
    <?php } ?>
</div>
