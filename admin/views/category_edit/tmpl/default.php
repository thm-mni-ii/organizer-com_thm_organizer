<?php defined('_JEXEC') or die('Restricted access');?>
<div id="thm_organizer_ce" >
    <form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>"
          method="post" name="adminForm" id="adminForm">
        <div id="thm_organizer_ce_ecat">
            <div id="thm_organizer_ce_ecat_name_div">
                <div class="thm_organizer_ce_label">
                    <label for="name"><?php echo JText::_('Name:');?></label>
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
                    <textarea name='description' rows='5' cols='35' id='description'><?php
                        echo $this->description;
                    ?></textarea>
                </div>
            </div>
            <div class="thm_organizer_ce_ecat_display_div">
                <div class="thm_organizer_ce_label">
                    <label for="global"><?php echo JText::_('COM_THM_ORGANIZER_GLOBAL_LABEL');?></label>
                </div>
                <div class="thm_organizer_ce_data">
                    <table>
                        <tr>
                            <td><?php echo JText::_('COM_THM_ORGANIZER_YES');?></td>
                            <td>
                                <input class="thm_organizer_radio_button"
                                       type="radio" name="global" value="1"
                                       <?php if($this->global) echo 'checked="checked"';?> >

                            </td>
                        </tr>
                        <tr>
                            <td><?php echo JText::_('COM_THM_ORGANIZER_NO');?></td>
                            <td>
                                <input class="thm_organizer_radio_button"
                                       type="radio" name="global" value="0"
                                       <?php if(!$this->global) echo 'checked="checked"';?> >
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="thm_organizer_ce_ecat_display_div">
                <div class="thm_organizer_ce_label">
                    <label for="reserves"><?php echo JText::_('COM_THM_ORGANIZER_RESERVES_LABEL');?></label>
                </div>
                <div class="thm_organizer_ce_data">
                    <table>
                        <tr>
                            <td><?php echo JText::_('COM_THM_ORGANIZER_YES');?></td>
                            <td>
                                <input class="thm_organizer_radio_button"
                                       type="radio" name="reserves" value="1"
                                       <?php if($this->reserves) echo 'checked="checked"';?> >

                            </td>
                        </tr>
                        <tr>
                            <td><?php echo JText::_('COM_THM_ORGANIZER_NO');?></td>
                            <td>
                                <input class="thm_organizer_radio_button"
                                       type="radio" name="reserves" value="0"
                                       <?php if(!$this->reserves) echo 'checked="checked"';?> >

                            </td>
                        </tr>
                    </table>
                     </div>
            </div>
        </div>
        <div id="thm_organizer_ce_ccat">
            <div id="thm_organizer_ce_ccat_name_div">
                <div class="thm_organizer_ce_label">
                    <label for="name"><?php echo JText::_('COM_THM_ORGANIZER_CONTENT_CATEGORY_LABEL');?></label>
                </div>
                <div class="thm_organizer_ce_data">
                    <?php echo $this->contentCatBox;?>
                </div>
            </div>
            <div id="thm_organizer_ce_ecat_desc_div">
                <div class="thm_organizer_ce_label">
                    <label for="description"><?php echo JText::_('COM_THM_ORGANIZER_CONTENT_CATEGORY_DESCRIPTION');?></label>
                </div>
                <div class="thm_organizer_ce_data" id="thm_organizer_ce_content_description">
                </div>
            </div>
            <div class="thm_organizer_ce_ecat_display_div">
                <div class="thm_organizer_ce_label">
                    <label for="global"><?php echo JText::_('COM_THM_ORGANIZER_CONTENT_CATEGORY_PERMISSIONS');?></label>
                </div>
                <div class="thm_organizer_ce_data" id="thm_organizer_ce_content_permissions">
                    <?php 
                        var_dump($this->temp);
                        echo "<br /><br />";
                    ?>
                </div>
            </div>
        </div>
        <input type="hidden" name="id" value="<?php echo $this->id; ?>" />
        <input type="hidden" name="task" value="" />
    </form>
</div>
