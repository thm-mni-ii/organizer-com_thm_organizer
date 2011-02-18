<?php defined('_JEXEC') or die('Restricted access');
$temp = str_replace("/index.php", "",$_SERVER['SCRIPT_NAME']);
?>
<div id="thm_organizer_se">
    <form action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>"
          enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
        <div class="t"><div class="t"><div class="t"></div></div></div>
        <div class="m" id="thm_organizer_se_meta">
            <table class="admintable">
                <colgroup>
                    <col id="thm_organizer_se_org_label" /><col />
                    <col id="thm_organizer_se_pp_label" /><col />
                    <col id="thm_organizer_se_mngr_label" /><col />
                </colgroup>
                <tr>
                    <td class="thm_organizer_se_label_data">
                        <label for="orgunit"><?php echo JText::_("Organization:"); ?></label>
                    </td>
                    <td>
                        <input class="text_area" type="text" name="organization" id="ecname" size="25" maxlength="20"
                        value="<?php echo $this->organization;?>" />
                    </td>
                    <td class="thm_organizer_se_label_data">
                        <label for="ecdescription"><?php echo JText::_("Planning Period Name:"); ?></label>
                    </td>
                    <td>
                        <input class="text_area" type="text" name="semester" id="ecname" size="25" maxlength="20"
                        value="<?php echo $this->semesterDesc;?>" />
                    </td>
                    <td class="thm_organizer_se_label_data">
                        <label for="globalp"><?php echo JText::_("Managing Usergroup:"); ?></label>
                    </td>
                    <td><?php echo $this->userGroupsBox; ?></td>
                </tr>
            </table>
        </div>
        <div class="b"><div class="b"><div class="b"></div></div></div>
        <div id="thm_organizer_se_seperator"></div>
        <div class="t"><div class="t"><div class="t"></div></div></div>
        <div class="m" id="thm_organizer_se_schedules">
<?php if($this->id == 0){ ?>
            <span id="thm_organizer_se_add_content_tip"><?php echo $this->scheduleText; ?></span>
<?php }else{ ?>
<?php if(!empty($this->schedules)) { $k = 0;?>
            <table class="admintable">
                <colgroup>
                    <col id="thm_organizer_se_checkbox_column" />
                    <col id="thm_organizer_se_active_column" />
                    <col id="thm_organizer_se_file_column" />
                    <col id="thm_organizer_se_upload_date_column" />
                    <col id="thm_organizer_se_description_column" />
                    <col id="thm_organizer_se_startdate_column" />
                    <col id="thm_organizer_se_enddate_column" />
                </colgroup>
                <thead>
                    <td />
                    <td />
                    <td><?php echo JText::_("Filename"); ?></td>
                    <td><?php echo JText::_("Upload Date"); ?></td>
                    <td><?php echo JText::_("Description"); ?></td>
                    <td><?php echo JText::_("Start Date"); ?></td>
                    <td><?php echo JText::_("End Date"); ?></td>
                </thead>
                <tbody>
<?php foreach($this->schedules as $schedule){
        $k % 2 == 0? $class = "row0" : $class = "row1"; $k++;
        $checked = JHTML::_( 'grid.id', $schedule["id"], $schedule["id"] ); ?>
                    <tr class="<?php echo "row$k"; ?>">
                        <td class="thm_organizer_sm_checkbox">
                            <?php echo $checked; ?>
                        </td>
                        <td>
<?php if($schedule['active']){ ?>
                            <img id="thm_organizer_se_active_image"
                                 src="<?php echo 'components/com_thm_organizer/assets/images/active.png'; ?>"
                                 alt="Active" />
<?php } ?>
                        </td>
                        <td><?php echo $schedule['filename']; ?></td>
                        <td><?php echo $schedule['includedate']; ?></td>
                        <td>
                            <input type='text' name='description' size='50' value='<?php echo $schedule['description']; ?>' />
                        </td>
                        <td><?php echo $schedule['startdate']; ?></td>
                        <td><?php echo $schedule['enddate']; ?></td>
                    </tr>
<?php } ?>
                </tbody>
            </table>
<?php }else{ ?>
            <div id="thm_organizer_se_no_schedules"><?php echo $this->noSchedulesText; ?></div>
<?php } ?>
            <div id="thm_organizer_se_upload_area" >
                <form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_thm_organizer&task=semester.upload') ?>" method="post">
                    <table border="0" cellspacing="0" cellpadding="1" align="left" class="uploadArea">
                        <tr>
                            <td>
                                <input name="file" type="file" />
                                <input type="hidden" name="semesterID" value="<?php echo $this->id; ?>" />
                            </td>
                            <td><input name="schedule_upload" type="submit" id="schedule_upload" value="Hochladen" /></td>
                        </tr>
                    </table>
                </form>
            </div>
<?php } ?>
        </div>
        <div class="b"><div class="b"><div class="b"></div></div></div>
        <input type="hidden" name="semesterID" value="<?php echo $this->id; ?>" />
        <input type="hidden" name="task" value="" />
    </form>
</div>