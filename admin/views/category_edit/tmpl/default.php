<?php defined('_JEXEC') or die('Restricted access');?>
<div id="thm_organizer_ce" >
    <form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>"
          method="post" name="adminForm" id="adminForm">
        <div id="thm_organizer_ce_ecat">
            <div id="thm_organizer_ce_ecat_name_div">
                <div class="thm_organizer_ce_label">
                    <label for="name"><?php echo JText::_('Name');?></label>
                </div>
                <div class="thm_organizer_ce_data">
                    <input type="text" name="title" size="25" maxlength="100" value="<?php echo $this->title;?>" />
                </div>
            </div>
            <div id="thm_organizer_ce_ecat_desc_div">
                <div class="thm_organizer_ce_label">
                    <label for="description"><?php echo JText::_('Description:');?></label>
                </div>
                <div class="thm_organizer_ce_data">
                    <textarea name='description' rows='5' cols='48' id='description'><?php
                        echo $this->description;
                    ?></textarea>
                </div>
            </div>
            <div class="thm_organizer_ce_ecat_display_div">
                <div class="thm_organizer_ce_label">
                    <label for="globalp"><?php echo JText::_('GLOBAL');?></label>
                </div>
                <div class="thm_organizer_ce_data">
                    <input type="radio" name="globalp" value="1" <?php if($this->global) echo 'checked="checked"';?> >
                        <?php echo JText::_('YES');?><br>
                    <input type="radio" name="globalp" value="0" <?php if(!$this->global) echo 'checked="checked"';?> >
                        <?php echo JText::_('NO');?><br>
                </div>
                <div class="thm_organizer_ce_explanation_div">
                    <span class="thm_organizer_ce_explanation">
                        <?php echo JText::_("Global events are of general importance and are displayed on all monitors."); ?>
                    </span>
                </div>
            </div>
            <div class="thm_organizer_ce_ecat_display_div">
                <div class="thm_organizer_ce_label">
                    <label for="reserves"><?php echo JText::_('RESERVES');?></label>
                </div>
                <div class="thm_organizer_ce_data">
                    <input type="radio" name="reserves" value="1" <?php if($this->reserves) echo 'checked="checked"';?> >
                        <?php echo JText::_('YES');?><br>
                    <input type="radio" name="reserves" value="0" <?php if(!$this->reserves) echo 'checked="checked"';?> >
                        <?php echo JText::_('NO');?><br>
                </div>
                <div class="thm_organizer_ce_explanation_div">
                    <span class="thm_organizer_ce_explanation">
                        <?php echo JText::_("COM_THM_ORGANIZER_RESERVING_EXPLANATION"); ?>
                    </span>
                </div>
            </div>
        </div>
        <div id="thm_organizer_ce_ccat">hi
        </div>
        <input type="hidden" name="id" value="<?php echo $this->id; ?>" />
        <input type="hidden" name="task" value="" />
    </form>
</div>
