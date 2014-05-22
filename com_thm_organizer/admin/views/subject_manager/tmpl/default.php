<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewPool_Edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$listOrder    = $this->state->get('list.ordering');
$listDirn    = $this->state->get('list.direction');
?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=subject_manager');?>"
      method="post" name="adminForm" id="adminForm">
    <div id="filter-bar" class='filter-bar'>
        <div class="filter-search fltlft pull-left">
            <label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
            <input type='text' name='filter_search' id='filter_search'
                   value='<?php echo $this->escape($this->state->get('filter.search')); ?>'
                   title='<?php echo JText::_('COM_THM_ORGANIZER_SEARCH_TITLE'); ?>' />
            <button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" onclick="document.id('filter_search').value='';this.form.submit();">
                <?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
            </button>
        </div>
        <div class="filter-select fltrt pull-right">
<?php
echo $this->programSelect;
if (isset($this->poolSelect))
{
    echo $this->poolSelect;
}
?>
 
        </div>
    </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th width="3%">
                    <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
                </th>
                <th width="55%">
                    <?php echo JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_NAME'), 'name', $listDirn, $listOrder);  ?>
                </th>
                <th width="13%">
                    <?php echo JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_FIELD'), 'field', $listDirn, $listOrder);  ?>
                </th>
                <th width="8%">
                    <?php echo JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUM_LSFID_TITLE'), 'lsfID', $listDirn, $listOrder);  ?>
                </th>
                <th width="8%">
                    <?php echo JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUM_HISID_TITLE'), 'hisID', $listDirn, $listOrder);  ?>
                </th>
                <th width="8%">
                    <?php echo JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUM_EXTERNALID_TITLE'), 'externalID', $listDirn, $listOrder);  ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="6"><?php echo $this->pagination->getListFooter(); ?></td>
            </tr>
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
            <?php echo JHtml::_('form.token'); ?>
        </tfoot>
        <tbody>
<?php
foreach ($this->items as $i => $item)
{
?>
            <tr class="row<?php echo $i % 2; ?>">
                <td>
                    <?php echo JHtml::_('grid.id', $i, $item->id)?>
                </td>
                <td>
                    <a href="index.php?option=com_thm_organizer&view=subject_edit&id=<?php echo $item->id; ?>">
                        <?php echo $item->name;?>
                    </a>
                </td>
                <td style="background-color: #<?php echo $item->color; ?>;">
                    <a href="index.php?option=com_thm_organizer&view=subject_edit&id=<?php echo $item->id; ?>">
                        <?php echo $item->field; ?>
                    </a>
                </td>
                <td align="center">
                    <a href="index.php?option=com_thm_organizer&view=subject_edit&id=<?php echo $item->id; ?>">
                        <?php echo $item->lsfID; ?>
                    </a>
                </td>
                <td align="center">
                    <a href="index.php?option=com_thm_organizer&view=subject_edit&id=<?php echo $item->id; ?>">
                        <?php echo $item->hisID; ?>
                    </a>
                </td>
                <td align="center">
                    <a href="index.php?option=com_thm_organizer&view=subject_edit&id=<?php echo $item->id; ?>">
                        <?php echo $item->externalID; ?>
                    </a>
                </td>
            </tr>
<?php
}
?>
        </tbody>
    </table>
</form>
