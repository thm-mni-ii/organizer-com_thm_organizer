<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.view
 * @name        virtual schedule manager default template
 * @description default template virtual schedule manager view
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$resetScript = "this.form.getElementById('search').value='';this.form.getElementById('groupFilters').value='0';";
$resetScript .= "this.form.getElementById('rolesFilters').value='0';this.form.submit();";
?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>" method="post" name="adminForm">
<table class="adminform">
    <tr>
        <td>
            <?php
            echo "<span title='" . JText::_("COM_THM_ORGANIZER_VSM_LABEL_DESCRIPTION") . "'>" . JText::_("COM_THM_ORGANIZER_VSM_LABEL_SEARCH") . "</span>";
            ?>
            <input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>"
                   class="text_area" onChange="document.adminForm.submit();" />
        </td>
        <td>
            <button onclick="this.form.submit();"><?php echo JText::_("COM_THM_ORGANIZER_VSM_BUTTON_GO"); ?></button>
            <button onclick="<?php echo $resetScript; ?>">
                <?php echo JText::_("COM_THM_ORGANIZER_VSM_BUTTON_RESET"); ?>
            </button>
        </td>
    </tr>
</table>
<div id="editcell">
<table class="adminlist">
    <thead>
        <tr>
            <th width="1"><?php echo JText::_("COM_THM_ORGANIZER_VSM_LABEL_KEY"); ?></th>
            <th width="1"><input type="checkbox" name="toggle" value=""
                onclick="checkAll(<?php echo count($this->items); ?>);" /></th>

            <th nowrap="nowrap">
                <?php echo JHTML::_(
                                    'grid.sort',
                                    JText::_("COM_THM_ORGANIZER_VSM_LABEL_NAME"),
                                    'name',
                                    $this->lists['order_Dir'],
                                    @$this->lists['order']
                                   ); ?>
            </th>
            <th align="center">
                <?php echo JHTML::_(
                                    'grid.sort',
                                    JText::_("COM_THM_ORGANIZER_VSM_LABEL_TYPE"),
                                    'type', $this->lists['order_Dir'],
                                    @$this->lists['order']
                                   ); ?>
            </th>
            <th align="center">
                <?php echo JHTML::_(
                                    'grid.sort', 
                                    JText::_("COM_THM_ORGANIZER_VSM_LABEL_RESPONSIBLE"),
                                    'responsible',
                                    $this->lists['order_Dir'],
                                    @$this->lists['order']
                                   ); ?>
            </th>
            <th align="center">
                <?php echo JHTML::_(
                                    'grid.sort',
                                    JText::_("COM_THM_ORGANIZER_VSM_LABEL_DEPARTMENT"),
                                    'department',
                                    $this->lists['order_Dir'],
                                    @$this->lists['order']
                                   ); ?>
            </th>
            <th align="center">
                <?php echo JHTML::_(
                                    'grid.sort',
                                    JText::_("COM_THM_ORGANIZER_VSM_LABEL_ELEMENTS"),
                                    'eid', $this->lists['order_Dir'],
                                    @$this->lists['order']
                                   ); ?>
            </th>
            <th nowrap="nowrap">
                <?php echo JHTML::_(
                                    'grid.sort',
                                    JText::_("COM_THM_ORGANIZER_VSM_LABEL_SEMESTER"),
                                    'semesterID',
                                    $this->lists['order_Dir'],
                                    @$this->lists['order']
                                   ); ?>
            </th>
        </tr>
    </thead>
    <tbody>
    <?php
    $k = 0;
    for ($i = 0, $n = count($this->items); $i < $n; $i++)
    {
        $row = &$this->items[$i];
        $checked  = JHTML::_('grid.id', $i, $row->id);
        $link = JRoute::_('index.php?option=com_thm_organizer&controller=virtual_schedule&task=virtual_schedule.edit&cid[]=' . base64_encode($row->id));
        ?>
    <tr class="<?php echo "row" . $k; ?>">
        <td><?php echo $row->id; ?></td>
        <td><?php echo $checked; ?></td>

        <td><a href="<?php echo $link;?>">
        <?php echo $row->name; ?>
        </a></td>
        <td><?php echo $row->type; ?></td>
        <td><?php echo $row->responsible;?></td>
        <td><?php echo $row->department; ?></td>
        <td><?php echo $row->eid; ?></td>
        <td><?php echo $row->semesterID; ?></td>
    </tr>
    <?php
    $k = 1 - $k;
    }
    ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="10"><?php echo $this->pagination->getListFooter(); ?></td>
        </tr>
    </tfoot>
</table>
</div>

<input type="hidden" name="task" value="" />
<input type="hidden" name="view" value="virtual_schedule_manager" />
<input type="hidden" name="grchecked" value="off" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

</form>
