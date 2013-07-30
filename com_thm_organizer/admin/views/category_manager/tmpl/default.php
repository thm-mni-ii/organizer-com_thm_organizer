<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @descriptiom default view template file for category lists
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$orderby = $this->escape($this->state->get('list.ordering'));
$direction = $this->escape($this->state->get('list.direction'));
?>
<form action="index.php?option=com_thm_organizer" method="post" name="adminForm">
    <fieldset id="filter-bar" class='filter-bar'>
        <div class="filter-select fltrt">
            <select name="filter_global" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_CAT_SEARCH_GLOBAL'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_CAT_ALL_GLOBAL'); ?></option>
                <option value="0"><?php echo JText::_('COM_THM_ORGANIZER_CAT_NOT_GLOBAL'); ?></option>
                <option value="1"><?php echo JText::_('COM_THM_ORGANIZER_CAT_GLOBAL'); ?></option>
            </select>
            <select name="filter_reserves" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_CAT_SEARCH_RESERVES'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_CAT_ALL_RESERVES'); ?></option>
                <option value="0"><?php echo JText::_('COM_THM_ORGANIZER_CAT_NOT_RESERVES'); ?></option>
                <option value="1"><?php echo JText::_('COM_THM_ORGANIZER_CAT_RESERVES'); ?></option>
            </select>
            <select name="filter_content_cat" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_CAT_SEARCH_CCATS'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_CAT_ALL_CCATS'); ?></option>
                <?php echo JHtml::_('select.options', $this->contentCategories, 'id', 'title', $this->state->get('filter.content_cat'));?>
            </select>
        </div>
    </fieldset>
    <table class="adminlist" id="thm_organizer_cat_table">
        <colgroup>
            <col id="thm_organizer_cat_checkbox_column" align="center" />
            <col id="thm_organizer_cat_title_column" />
            <col id="thm_organizer_cat_global_column" />
            <col id="thm_organizer_cat_reserves_column" />
            <col id="thm_organizer_cat_content_cat_column" />
        </colgroup>
        <thead>
            <tr>
                <th align="left">
                    <input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
                </th>
                <th>
					<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_NAME'), 'ectitle', $direction, $orderby); ?>
                </th>
                <th>
					<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_CAT_GLOBAL'), 'global', $direction, $orderby); ?>
                </th>
                <th>
					<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_CAT_RESERVES'), 'reserves', $direction, $orderby); ?>
                </th>
                <th>
					<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_CAT_CONTENT_CATEGORY'), 'cctitle', $direction, $orderby); ?>
                </th>
            </tr>
        </thead>
        <tbody>
<?php
$k = 0;
if (!empty($this->categories))
{
    foreach ($this->categories as $category)
    {
        $checked = JHTML::_('grid.id', $k, $category->id);
        $class = ($k % 2 == 0)?  'row0' : 'row1';
        $k++
?>
            <tr class="<?php echo $class; ?>">
                <td class="thm_organizer_cat_checkbox"><?php echo $checked; ?></td>
                <td class="thm_organizer_cat_name">
                    <a class="jgrid" href='<?php echo $category->link; ?>' >
                        <?php echo $category->ectitle; ?>
                    </a>
                </td>
                <td class="thm_organizer_cat_global">
                    <a class="jgrid" href='<?php echo $category->link; ?>' >
<?php echo ($category->global)? "<span class='state publish'></span>" : "<span class='state expired'></span>"; ?>
                    </a>
                </td>
                <td class="thm_organizer_cat_reserve">
                    <a class="jgrid" href='<?php echo $category->link; ?>' >
<?php echo ($category->reserves)? "<span class='state publish'></span>" : "<span class='state expired'></span>"; ?>
                    </a>
                </td>
                <td class="thm_organizer_cat_reserve">
                    <a class="jgrid" href='<?php echo $category->link; ?>' >
                        <?php echo $category->cctitle; ?>
                    </a>
                </td>
            </tr>
<?php
    }
}
?>
        </tbody>
    </table>
    <input type="hidden" name="filter_order" value="<?php echo $orderby; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $direction; ?>" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="view" value="category_manager" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo JHtml::_('form.token'); ?>
</form>
