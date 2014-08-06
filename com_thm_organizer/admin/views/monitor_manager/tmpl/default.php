<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @description monitor manager default template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Daniel Kirsten, <daniel.kirsten@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$orderby = $this->escape($this->state->get('list.ordering'));
$direction = $this->escape($this->state->get('list.direction'));
$defaultDisplay = JComponentHelper::getParams('com_thm_organizer')->get('display');
$defaultScheduleRefresh = JComponentHelper::getParams('com_thm_organizer')->get('schedule_refresh');
$defaultContentRefresh = JComponentHelper::getParams('com_thm_organizer')->get('content_refresh');
$defaultContent = JComponentHelper::getParams('com_thm_organizer')->get('content');
?>
<form id="adminForm" action="index.php?option=com_thm_organizer&view=monitor_manager" method="post" name="adminForm">
    <div id="filter-bar" class='filter-bar'>
        <div class="filter-search fltlft pull-left">
            <label class="filter-search-lbl" for="filter_search">
                <?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
            </label>
            <input type="text" name="filter_search" id="filter_search"
                value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                title="<?php echo JText::_('COM_THM_ORGANIZER_SEARCH_TITLE'); ?>" />
            <button type="submit">
                <?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
            </button>
            <button type="button"
                onclick="document.id('filter_search').value='';this.form.submit();">
                <?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
            </button>
        </div>
        <div class="filter-select fltrt pull-right">
            <select name="filter_room" class="inputbox" onchange="this.form.submit()">
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_MON_SEARCH_ROOMS'); ?></option>
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_MON_ALL_ROOMS'); ?></option>
                    <?php echo JHtml::_('select.options', $this->rooms, 'id', 'name', $this->state->get('filter.room'));?>
            </select>
            <select name="filter_display" class="inputbox" onchange="this.form.submit()">
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_MON_SEARCH_BEHAVIOURS'); ?></option>
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_MON_ALL_BEHAVIOURS'); ?></option>
                    <?php echo JHtml::_('select.options', $this->behaviours, 'id', 'behaviour', $this->state->get('filter.display'));?>
            </select>
            <select name="filter_content" class="inputbox" onchange="this.form.submit()">
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_MON_SEARCH_CONTENT'); ?></option>
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_MON_ALL_CONTENT'); ?></option>
                    <?php echo JHtml::_('select.options', $this->contents, 'name', 'name', $this->state->get('filter.content'));?>
            </select>
        </div>
    </div>
    
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $orderby; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $direction; ?>" />
    <input type="hidden" name="view" value="monitor_manager" />
    </form>
    <div class="clr"> </div>
    <div>
        <table class="adminlist" id="thm_organizer_mon_table" class='thm_organizer_mon_table table table-striped'>
            <colgroup>
                <col class='thm_organizer_mon_col_checkbox'/>
                <col class='thm_organizer_mon_col_room' />
                <col class='thm_organizer_mon_col_ip' />
                <col class='thm_organizer_mon_col_use_defaults'/>
                <col class='thm_organizer_mon_col_display'/>
                <col class='thm_organizer_mon_col_interval' />
                <col class='thm_organizer_mon_col_interval'/>
                <col class='thm_organizer_mon_col_content' />
            </colgroup>
            <thead>
                <tr>
                    <th width="10px">
                        <input type="checkbox" name="toggle" value=""
                               onclick="checkAll(<?php echo count($this->monitors); ?>);" />
                    </th>
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_ROOM') . "::"
                                . JText::_('COM_THM_ORGANIZER_MON_ROOM_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_ROOM', 'name', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_IP') . "::"
                                . JText::_('COM_THM_ORGANIZER_MON_IP_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_IP', 'ip', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_USEDEFAULTS') . "::"
                                . JText::_('COM_THM_ORGANIZER_MON_USEDEFAULTS_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_USEDEFAULTS', 'useDefaults', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_DISPLAY') . "::"
                                . JText::_('COM_THM_ORGANIZER_MON_DISPLAY_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_DISPLAY', 'display', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_SCHEDULE_REFRESH') . "::" .
                        JText::_('COM_THM_ORGANIZER_MON_SCHEDULE_REFRESH_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_SCHEDULE_REFRESH', 'schedule_refresh', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_CONTENT_REFRESH') . "::" .
                        JText::_('COM_THM_ORGANIZER_MON_CONTENT_REFRESH_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_CONTENT_REFRESH', 'content_refresh', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_CONTENT') . "::"
                                . JText::_('COM_THM_ORGANIZER_MON_CONTENT_DESC'); ?>">
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
if ($this->monitors !== false)
{
    foreach ($this->monitors as $k => $monitor)
    {
        $useDefaults = $monitor->useDefaults? 0 : 1;
        $url = "index.php?option=com_thm_organizer&task=monitor.saveDefaultBehaviour&id=";
        $url .= "$monitor->id&useDefaults=$useDefaults";
        $href = JRoute::_($url);
        if ($monitor->useDefaults)
        {
            $defaultText = JText::_('COM_THM_ORGANIZER_MON_USEDEFAULTS_ACTIVE');
            $defaultClass = 'publish';
            $defaultTitle = JText::_('COM_THM_ORGANIZER_MON_USEDEFAULTS') . '::';
            $defaultTitle .= JText::_('COM_THM_ORGANIZER_MON_USEDEFAULTS_INACTIVE_TOGGLE');
            if(isset($this->behaviours[$defaultDisplay])){
                $display = $this->behaviours[$defaultDisplay];
            } else {
                $display = "";
            }
            $scheduleRefresh = $defaultScheduleRefresh;
            $contentRefresh = $defaultContentRefresh;
            $content = $defaultContent;
        }
        else
        {
            $defaultText = JText::_('COM_THM_ORGANIZER_MON_USEDEFAULTS_INACTIVE');
            $defaultClass = 'unpublish';
            $defaultTitle = JText::_('COM_THM_ORGANIZER_MON_USEDEFAULTS') . '::';
            $defaultTitle .= JText::_('COM_THM_ORGANIZER_MON_USEDEFAULTS_ACTIVE_TOGGLE');
            $display = $this->behaviours[$monitor->display];
            $scheduleRefresh = $monitor->schedule_refresh;
            $contentRefresh = $monitor->content_refresh;
            $content = $monitor->content;
        }
?>
                <tr class="row<?php echo $k % 2;?>">
                    <td><?php echo JHtml::_('grid.id', $k, $monitor->id); ?></td>
                    <td>
                        <a href='<?php echo $monitor->link; ?>' >
                            <?php echo $monitor->room; ?>
                        </a>
                    </td>
                    <td>
                        <a href='<?php echo $monitor->link; ?>' >
                            <?php echo $monitor->ip; ?>
                        </a>
                    </td>
                    <td class="center">
                        <a class="jgrid hasTip"
                           href="<?php echo $href; ?>"
                           title="<?php echo $defaultTitle; ?>">
                            <span class="state <?php echo $defaultClass; ?>">
                                <span class="text"><?php echo $defaultText; ?></span>
                            </span>
                        </a>
                    </td>
                    <td>
                        <a href='<?php echo $monitor->link; ?>' >
                            <?php echo $display; ?>
                        </a>
                    </td>
                    <td>
                        <a href='<?php echo $monitor->link; ?>' >
                            <?php echo $scheduleRefresh; ?>
                        </a>
                    </td>
                    <td>
                        <a href='<?php echo $monitor->link; ?>' >
                            <?php echo $contentRefresh; ?>
                        </a>
                    </td>
                    <td>
                        <a href='<?php echo $monitor->link; ?>' >
                            <?php echo $content; ?>
                        </a>
                    </td>
                </tr>
<?php
    }
}
?>
            </tbody>
        </table>
    </div>
<?php
echo JHtml::_('form.token');
