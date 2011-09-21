<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        schedule manager default template
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined("_JEXEC") or die ?>
<form action="<?php echo JRoute::_("index.php?option=com_thm_organizer"); ?>"
      enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
    <div class="m">
        <div id="toolbar" class="toolbar-list">
            <ul>
                <li id="toolbar-apply" class="button">
                    <button type="submit" class="hasTip"
                       title="<?php echo JText::_('COM_THM_ORGANIZER_APPLY')."::".JText::_('COM_THM_ORGANIZER_SM_APPLY_DESC');?>">
                        <span class="icon-32-apply"></span>
                        <?php echo JText::_('COM_THM_ORGANIZER_APPLY'); ?>
                    </button>
                </li>
                <li id="toolbar-close" class="button">
                    <a onClick="window.parent.location.reload(true);"
                       href="#" class="hasTip"
                       title="<?php echo JText::_('COM_THM_ORGANIZER_CLOSE')."::".JText::_('COM_THM_ORGANIZER_SM_CLOSE_DESC');?>">
                        <span class="icon-32-cancel"></span>
                        <?php echo JText::_('COM_THM_ORGANIZER_CLOSE'); ?>
                    </a>
                </li>
            </ul>
            <div class="clr"></div>
        </div>
        <div class="pagetitle icon-48-generic">
            <h2>
                <?php echo $this->semesterName.": ".JText::_('COM_THM_ORGANIZER_SM_SCHEDULES'); ?>
            </h2>
        </div>
    </div>
    <div id="thm_organizer_sm_seperator"></div>
    <div id="thm_organizer_sm_schedules">
    <?php if(!empty($this->schedules)) { $k = 0;?>
        <table class="admintable">
            <colgroup>
                <col id="thm_organizer_sm_active_column" />
                <col id="thm_organizer_sm_delete_column" />
                <col id="thm_organizer_sm_file_column" />
                <col id="thm_organizer_sm_upload_date_column" />
                <col id="thm_organizer_sm_description_column" />
                <col id="thm_organizer_sm_startdate_column" />
                <col id="thm_organizer_sm_enddate_column" />
            </colgroup>
            <thead>
                <th class="thm_organizer_sm_th" ></th>
                <th class="thm_organizer_sm_th" ></th>
                <th class="thm_organizer_sm_th" >
                    <?php echo JText::_("COM_THM_ORGANIZER_SM_FILENAME"); ?>
                </th>
                <th class="thm_organizer_sm_th" >
                    <?php echo JText::_("COM_THM_ORGANIZER_SM_UPLOAD_DATE"); ?>
                </th>
                <th class="thm_organizer_sm_th" >
                    <?php echo JText::_("COM_THM_ORGANIZER_SM_SCHEDULE_DESC"); ?>
                </th>
                <th class="thm_organizer_sm_th" >
                    <?php echo JText::_("COM_THM_ORGANIZER_SM_START_DATE"); ?>
                </th>
                <th class="thm_organizer_sm_th" >
                    <?php echo JText::_("COM_THM_ORGANIZER_SM_END_DATE"); ?>
                </th>
            </thead>
            <tbody>
<?php foreach($this->schedules as $schedule){ $k % 2 == 0? $class = "row0" : $class = "row1"; $k++; ?>
                <tr class="<?php echo $class; ?>">
                    <td><?php echo $schedule["publish"]; ?></td>
                    <td><?php echo $schedule["delete"]; ?></td>
                    <td><?php echo $schedule["filename"]; ?></td>
                    <td><?php echo $schedule["includedate"]; ?></td>
                    <td>
                        <input type="text" name="description<?php echo $schedule["id"]; ?>" size="50" value="<?php echo $schedule["description"]; ?>" />
                    </td>
                    <td><?php echo $schedule["startdate"]; ?></td>
                    <td><?php echo $schedule["enddate"]; ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    <?php } ?>
    </div>
    <div id="thm_organizer_sm_upload">
        <form action="<?php echo JRoute::_("index.php?option=com_thm_organizer"); ?>"
              enctype="multipart/form-data" method="post" name="uploadForm" id="uploadForm">
            <table class="admintable">
                <tr>
                    <td>
                        <label class="thm_organizer_sm_label" for="file">
                            <?php echo JText::_("COM_THM_ORGANIZER_SM_UPLOAD").":"; ?>
                        </label>
                    </td>
                    <td>
                        <input name="file" type="file" />
                    </td>
                </tr>
            </table>
            <input type="hidden" name="task" value="schedule_manager.upload" />
            <input type="hidden" name="semesterID" value="<?php echo $this->semesterID; ?>" />
        </form>
    </div>
    <input type="hidden" name="task" value="schedule_manager.apply" />
    <input type="hidden" name="semesterID" value="<?php echo $this->semesterID; ?>" />
</form>