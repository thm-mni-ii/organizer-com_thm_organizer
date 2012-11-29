<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        monitor manager default template
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 *@author      Daniel Kirsten danielDOTkirstenATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('_JEXEC') or die;
$orderby = $this->escape($this->state->get('list.ordering'));
$direction = $this->escape($this->state->get('list.direction'));
?>
<form action="index.php?option=com_thm_organizer" method="post" name="adminForm" id="adminForm">
    <fieldset id="filter-bar">
        <div class="filter-select fltrt">
            <select name="filter_display" class="inputbox" onchange="this.form.submit()">
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_MON_SEARCH_BEHAVIOURS'); ?></option>
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_MON_ALL_BEHAVIOURS'); ?></option>
                    <?php echo JHtml::_('select.options', $this->behaviours, 'id', 'behaviour', $this->state->get('filter.display'));?>
            </select>
        </div>
        <div class="filter-select fltrt">
            <select name="filter_room" class="inputbox" onchange="this.form.submit()">
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_MON_SEARCH_ROOMS'); ?></option>
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_MON_ALL_ROOMS'); ?></option>
                    <?php echo JHtml::_('select.options', $this->rooms, 'id', 'name', $this->state->get('filter.room'));?>
            </select>
        </div>
    </fieldset>
    <div class="clr"> </div>
<?php
if (!empty($this->monitors))
{
    $k = 0;
?>
    <div>
        <table class="adminlist" id="thm_organizer_mon_table">
            <colgroup>
                <col id="thm_organizer_mon_col_checkbox" />
                <col id="thm_organizer_mon_col_room" />
                <col id="thm_organizer_mon_col_ip" />
                <col id="thm_organizer_mon_col_display" />
                <col id="thm_organizer_mon_col_schedule_refresh" />
                <col id="thm_organizer_mon_col_content_refresh" />
                <col id="thm_organizer_mon_col_content" />
            </colgroup>
            <thead>
                <tr>
                    <th />
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_ROOM') . "::" . JText::_('COM_THM_ORGANIZER_MON_ROOM_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_ROOM', 'name', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_IP') . "::" . JText::_('COM_THM_ORGANIZER_MON_IP_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_IP', 'ip', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_DISPLAY') . "::" . JText::_('COM_THM_ORGANIZER_MON_DISPLAY_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_DISPLAY', 'display', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_SCHEDULE_REFRESH') . "::" . JText::_('COM_THM_ORGANIZER_MON_SCHEDULE_REFRESH_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_SCHEDULE_REFRESH', 'schedule_refresh', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_CONTENT_REFRESH') . "::" . JText::_('COM_THM_ORGANIZER_MON_CONTENT_REFRESH_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_CONTENT_REFRESH', 'content_refresh', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_CONTENT') . "::" . JText::_('COM_THM_ORGANIZER_MON_CONTENT_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_CONTENT', 'content', $direction, $orderby); ?>
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
<?php
    foreach ($this->monitors as $k => $monitor)
    {
?>
                <tr class="row<?php echo $k % 2;?>">
                    <td><?php echo JHtml::_('grid.id', $k, $monitor->id); ?></td>
                    <td><a href='<?php echo $monitor->link; ?>' ><?php echo $monitor->room; ?></a></td>
                    <td><a href='<?php echo $monitor->link; ?>' > <?php echo $monitor->ip; ?></a></td>
                    <td><a href='<?php echo $monitor->link; ?>' ><?php echo $monitor->display; ?></a></td>
                    <td><a href='<?php echo $monitor->link; ?>' ><?php echo $monitor->schedule_refresh; ?></a></td>
                    <td><a href='<?php echo $monitor->link; ?>' ><?php echo $monitor->content_refresh; ?></a></td>
                    <td><a href='<?php echo $monitor->link; ?>' ><?php echo $monitor->content; ?></a></td>
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
    <input type="hidden" name="view" value="monitor_manager" />
    <?php echo JHtml::_('form.token'); ?>
</form>
