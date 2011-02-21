<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester editor default template
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined('_JEXEC') or die('Restricted access');
?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>"
      enctype="multipart/form-data" method="post" name="adminForm" id="adminForm" >
    <div id="thm_organizer_se_meta">
        <table class="admintable">
            <colgroup>
                <col id="thm_organizer_se_org_label" />
                <col />
                <col id="thm_organizer_se_pp_label" />
                <col />
                <col id="thm_organizer_se_mngr_label" />
                <col />
            </colgroup>
            <tr>
                <td class="thm_organizer_se_label" >
                    <label for="orgunit"><?php echo JText::_("Organization:"); ?></label>
                </td>
                <td class="thm_organizer_se_meta_data" >
                    <input class="text_area" type="text" name="organization" id="ecname" size="25" maxlength="20"
                    value="<?php echo $this->organization;?>" />
                </td>
                <td class="thm_organizer_se_label" >
                    <label for="ecdescription"><?php echo JText::_("Planning Period Name:"); ?></label>
                </td>
                <td class="thm_organizer_se_meta_data" >
                    <input class="text_area" type="text" name="semester" id="ecname" size="25" maxlength="20"
                    value="<?php echo $this->semesterDesc;?>" />
                </td>
                <td class="thm_organizer_se_label" >
                    <label for="globalp"><?php echo JText::_("Managing Usergroup:"); ?></label>
                </td>
                <td class="thm_organizer_se_meta_data" >
                    <?php echo $this->userGroupsBox; ?>
                </td>
            </tr>
        </table>
    <?php if($this->id != 0){ ?>
        <form action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>"
              enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
            <table class="admintable">
                <tr>
                    <td>
                        <label class="thm_organizer_se_label" for="file">
                            <?php echo JText::_('File:'); ?>
                        </label>
                    </td>
                    <td>
                        <input name="file" type="file" />
                    </td>
                    <td>
                        <input type="submit" name="upload" value="<?php echo JText::_('Upload');?>" />
                    </td>
                </tr>
            </table>
            <input type="hidden" name="task" value="semester.upload" />
            <input type="hidden" name="semesterID" value="<?php echo $this->id; ?>" />
        </form>
    <?php } ?>
    </div>
    <div id="thm_organizer_se_seperator"></div>
    <div id="thm_organizer_se_schedules">
    <?php if($this->id == 0){ ?>
        <span id="thm_organizer_se_add_content_tip"><?php echo $this->scheduleText; ?></span>
    <?php }else{ ?>
    <?php if(!empty($this->schedules)) { $k = 0;?>
        <div id="thm_organizer_se_gpu">
            <?php echo JText::_("Schedules"); ?>
        </div>
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
                <th align="left">
                    <input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
                </th>
                <th />
                <th class="thm_organizer_se_th" ><?php echo JText::_("Filename"); ?></th>
                <th class="thm_organizer_se_th" ><?php echo JText::_("Upload Date"); ?></th>
                <th class="thm_organizer_se_th" ><?php echo JText::_("Description"); ?></th>
                <th class="thm_organizer_se_th" ><?php echo JText::_("Start Date"); ?></th>
                <th class="thm_organizer_se_th" ><?php echo JText::_("End Date"); ?></th>
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
    <div id="thm_organizer_se_no_schedules">
        <?php echo $this->noSchedulesText; ?>
    </div>
    <?php }  } ?>
    </div>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="semesterID" value="<?php echo $this->id; ?>" />
</form>