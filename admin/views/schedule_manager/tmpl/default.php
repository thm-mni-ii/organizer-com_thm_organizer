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
defined("_JEXEC") or die;
$orderby = $this->escape($this->state->get('list.ordering'));
$direction = $this->escape($this->state->get('list.direction'));
$search = ($this->state->get('filter.search'))?
        $this->escape($this->state->get('filter.search')) : JText::_('COM_THM_ORGANIZER_SEARH_CRITERIA');
?>
<form action="<?php echo JRoute::_("index.php?option=com_thm_organizer"); ?>"
      enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
    <fieldset id="filter-bar">
    <?php if($this->state->get('semesterName')): ?>
        <div id="thm_organizer_search_div" class="filter-search fltlft">
            <label class="thm_organizer_label" for="file">
                <?php echo JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_TITLE"); ?>
            </label>
            <input name="file" type="file" />
        </div>
    <?php endif; ?>
        <div class="filter-search fltlft">
                <input type="text" name="filter_search" id="filter_search" value="<?php echo $search; ?>"
                       title="<?php echo JText::_('COM_THM_ORGANIZER_SEARCH_DESC'); ?>" />
                <button type="submit"><?php echo JText::_('COM_THM_ORGANIZER_SEARCH'); ?></button>
                <button type="button" onclick="document.id('filter_search').value='';this.form.submit();">
                    <?php echo JText::_('COM_THM_ORGANIZER_SEARCH_CLEAR'); ?>
                </button>
        </div>
        <div class="filter-select fltrt">
                <select name="filter_state" class="inputbox" onchange="this.form.submit()">
                        <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_SCH_SEARCH_STATES'); ?></option>
                        <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_SCH_ALL_STATES'); ?></option>
                        <option value="0"><?php echo JText::_('COM_THM_ORGANIZER_SCH_INACTIVE'); ?></option>
                        <option value="1"><?php echo JText::_('COM_THM_ORGANIZER_SCH_ACTIVE'); ?></option>
                </select>
                <select name="filter_semester" class="inputbox" onchange="this.form.submit()">
                        <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_SCH_SEARCH_SEMESTERS'); ?></option>
                        <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_SCH_ALL_SEMESTERS'); ?></option>
                        <?php echo JHtml::_('select.options', $this->semesters, 'id', 'name', $this->state->get('filter.semester'));?>
                </select>
                <select name="filter_type" class="inputbox" onchange="this.form.submit()">
                        <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_SCH_SEARCH_TYPES'); ?></option>
                        <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_SCH_ALL_TYPES'); ?></option>
                        <?php echo JHtml::_('select.options', $this->plantypes, 'id', 'name', $this->state->get('filter.type'));?>
                </select>
        </div>
    </fieldset>
    <div class="clr"> </div>
<?php if(!empty($this->schedules)) { $k = 0;?>
    <div>
        <table class="adminlist" cellpadding="0">
            <colgroup>
                <col id="thm_organizer_check_column" />
                <col id="thm_organizer_sch_file_column" />
                <col id="thm_organizer_sch_description_column" />
                <col class="thm_organizer_sch_date_column" />
                <col class="thm_organizer_sch_date_column" />
                <col class="thm_organizer_sch_date_column" />
                <col id="thm_organizer_sch_active_column" />
                <col id="thm_organizer_sch_semester_column" />
                <col id="thm_organizer_sch_semester_column" />
            </colgroup>
            <thead>
                <tr>
                    <th class="thm_organizer_sch_th" ></th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_NAME', 'sch.filename', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_DESC', 'sch.description', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_SCH_CREATION_DATE', 'creationdate', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JText::_('COM_THM_ORGANIZER_START_DATE'); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JText::_('COM_THM_ORGANIZER_END_DATE'); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JText::_('COM_THM_ORGANIZER_SCH_ACTIVE'); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JText::_('COM_THM_ORGANIZER_SCH_SEMESTER_TITLE'); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JText::_('COM_THM_ORGANIZER_SCH_PLANTYPE_TITLE'); ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="9">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
            <?php foreach($this->schedules as $k => $schedule){ ?>
                <tr class="row<?php echo $k % 2;?>">
                    <td><?php echo JHtml::_('grid.id', $k, $schedule->id); ?></td>
                    <td><?php echo $schedule->filename; ?></td>
                    <td><?php echo $schedule->description; ?></td>
                    <td><?php echo $schedule->creationdate; ?></td>
                    <td><?php echo $schedule->startdate; ?></td>
                    <td><?php echo $schedule->enddate; ?></td>
                    <td class="thm_organizer_sch_active_td">
                        <?php if($schedule->semester) echo JHtml::_('jgrid.isdefault', $schedule->active != null, $k, 'schedule.', $this->access);?>
                    </td>
                    <td><?php echo $schedule->semester; ?></td>
                    <td><?php echo JText::_($schedule->plantype); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $orderby; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $direction; ?>" />
    <input type="hidden" name="view" value="schedule_manager" />
    <input type="hidden" name="semesterID" value="<?php echo $this->state->get('filter.semester'); ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>