<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view colors default
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$listOrder = $this->state->get('list.ordering', 'ordering');
$listDirn = $this->state->get('list.direction');
$idTitle = JText::_('COM_THM_ORGANIZER_ID') . '::' . JText::_('COM_THM_ORGANIZER_CLM_ID_DESC');
$nameTitle = JText::_('COM_THM_ORGANIZER_NAME') . '::' . JText::_('COM_THM_ORGANIZER_CLM_NAME_DESC');
$colorTitle = JText::_('COM_THM_ORGANIZER_COLOR') . '::' . JText::_('COM_THM_ORGANIZER_CLM_COLOR_DESC');
$hexTitle = JText::_('COM_THM_ORGANIZER_CLM_CODE') . '::' . JText::_('COM_THM_ORGANIZER_CLM_CODE_DESC');
?>
<form id="adminForm" action="index.php?option=com_thm_organizer" method="post" name="adminForm" >
    <div id="filter-bar" class="filter-bar">
        <div class="filter-search fltlft">
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
    </div>
    <div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th width="3%">
                        <input type="checkbox" name="toggle" value=""
                               onclick="checkAll(<?php echo count($this->items); ?>);" />
                    </th>
                    <th title="<?php echo $nameTitle; ?>" class="hasTip" width="30%">
                        <?php echo JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_NAME'), 'name', $listDirn, $listOrder); ?>
                    </th>
                    <th title="<?php echo $hexTitle; ?>" class="hasTip" width="15%">
                        <?php echo JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_CLM_CODE'), 'color', $listDirn, $listOrder); ?>
                    </th>
                    <th title="<?php echo $colorTitle; ?>" class="hasTip" width="20%">
                        <?php echo JText::_('COM_THM_ORGANIZER_COLOR'); ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="4"><?php echo $this->pagination->getListFooter(); ?></td>
                </tr>
                <input type="hidden" name="task" value="" />
                <input type="hidden" name="boxchecked" value="0" />
                <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
                <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
                <input type="hidden" name="view" value="color_manager" />
                <?php echo JHtml::_('form.token');?>
            </tfoot>
            <tbody>
<?php
if($this->items != null){
    foreach ($this->items as $i => $item)
    {
?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td align="center">
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </td>
                    <td>
                        <a href="index.php?option=com_thm_organizer&view=color_edit&id=<?php echo $item->id; ?>">
                            <?php echo $item->name; ?>
                        </a>
                    <td>
                        <a href="index.php?option=com_thm_organizer&view=color_edit&id=<?php echo $item->id; ?>">
                            <?php echo $item->color; ?>
                        </a>
                    </td>
                    </td>
                    <td align="center" style="background-color: <?php echo "#$item->color"; ?>">&nbsp;</td>
                </tr>
<?php
    }
}
?>
            </tbody>
        </table>
    </div>
</form>
