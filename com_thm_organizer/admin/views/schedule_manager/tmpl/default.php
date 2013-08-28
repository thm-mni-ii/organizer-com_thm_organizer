<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @description default view template file for schedule lists
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Daniel Kirsten, <daniel.kirsten@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined("_JEXEC") or die;
$orderby = $this->escape($this->state->get('list.ordering'));
$direction = $this->escape($this->state->get('list.direction'));
?>
<form action="index.php?option=com_thm_organizer"
      enctype="multipart/form-data"
      method="post"
      name="adminForm"
      id="adminForm">
    <fieldset id="filter-bar" class='filter-bar'>
        <div class="filter-select fltrt">
            <select name="filter_state" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_SCH_SEARCH_STATES'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_SCH_ALL_STATES'); ?></option>
                <option value="0" <?php echo ($this->state->get('filter.state') === 0)? $this->state->get('filter.state') : ''; ?> >
                    <?php echo JText::_('COM_THM_ORGANIZER_SCH_INACTIVE'); ?>
                </option>
                <option value="1" <?php echo ($this->state->get('filter.state') === 1)? $this->state->get('filter.state') : ''; ?> >
                    <?php echo JText::_('COM_THM_ORGANIZER_SCH_ACTIVE'); ?>
                </option>
            </select>
            <select name="filter_semester" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_SCH_SEARCH_SEMESTERS'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_SCH_ALL_SEMESTERS'); ?></option>
                <?php echo JHtml::_('select.options', $this->semesters, 'name', 'name', $this->state->get('filter.semester'));?>
            </select>
            <select name="filter_department" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_SCH_SEARCH_DEPARTMENTS'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_SCH_ALL_DEPARTMENTS'); ?></option>
                <?php echo JHtml::_('select.options', $this->departments, 'name', 'name', $this->state->get('filter.department'));?>
            </select>
        </div>
    </fieldset>
    <div class="clr"> </div>
<?php if (!empty($this->schedules))
      {
          $k = 0;
?>
    <div>
        <table class="adminlist" cellpadding="0">
            <colgroup>
                <col id="thm_organizer_check_column" class="thm_organizer_check_column" />
                <col class="thm_organizer_sch_semester_column" />
                <col class="thm_organizer_sch_semester_column" />
                <col class="thm_organizer_sch_date_column" />
                <col class="thm_organizer_sch_date_column" />
                <col class="thm_organizer_sch_date_column" />
                <col id="thm_organizer_sch_active_column" class='thm_organizer_sch_active_column' />
                <col id="thm_organizer_sch_description_column" />
            </colgroup>
            <thead>
                <tr>
                    <th class="thm_organizer_sch_th" ></th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_DEPT', 'departmentname', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_SEMESTER', 'semestername', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_SCH_CREATION_DATE', 'creationdate', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_SCH_START_DATE', 'creationdate', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_SCH_END_DATE', 'creationdate', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_SCH_ACTIVE', 'active', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_DESC', 'description', $direction, $orderby); ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="8">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
<?php
foreach ($this->schedules as $k => $schedule)
{ ?>
                <tr class="row<?php echo $k % 2;?>">
                    <td><?php echo JHtml::_('grid.id', $k, $schedule->id); ?></td>
                    <td>
                        <a href="<?php echo $schedule->url; ?>">
                            <?php echo $schedule->departmentname; ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $schedule->url; ?>">
                            <?php echo $schedule->semestername; ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $schedule->url; ?>">
                            <?php echo $schedule->creationdate; ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $schedule->url; ?>">
                            <?php echo $schedule->startdate; ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $schedule->url; ?>">
                            <?php echo $schedule->enddate; ?>
                        </a>
                    </td>
                    <td class="thm_organizer_sch_active_td jgrid">
                        <span class="state <?php echo ($schedule->active)? 'default' : 'notdefault'; ?>" ></span>
                    </td>
                    <td><?php echo $schedule->description; ?></td>
                </tr>
            <?php
}
            ?>
            </tbody>
        </table>
    </div>
<?php
}
?>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $orderby; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $direction; ?>" />
    <input type="hidden" name="view" value="schedule_manager" />
    <?php echo JHtml::_('form.token'); ?>
</form>