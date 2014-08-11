<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @description default view template file for thm organizer users list
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined("_JEXEC") or die;
?>
<form action="index.php?option=com_thm_organizer"
      enctype="multipart/form-data"
      method="post"
      name="adminForm"
      id="adminForm">
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
<?php
foreach ($this->model->filters AS $filter)
{
    echo '<div class="filter-select fltrt pull-right">';
    echo $filter;
    echo '</div>';
}
?>
    </div>
    <div class="clr"> </div>
    <div>
        <table class="table table-striped" cellpadding="0">
            <thead>
                <tr>
<?php
$column = 0;
foreach ($this->model->headers AS $header)
{
    echo '<th class="column' . $column . '">' . $header . '</th>';
    $column++;
}
?>
                </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="5">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
            </tfoot>
            <tbody>
<?php
$row = 0;
foreach ($this->items AS $item)
{
    echo '<tr class="row' . $row % 2 . ' list-row' . $row . '">';
    $column = 0;
    foreach ($item AS $tableData)
    {
        echo '<td class="column' . $column . '">' . $tableData . '</td>';
        $column++;
    }
    echo '</tr>';
    $row++;
}
?>
            </tbody>
        </table>
    </div>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($this->state->get('list.ordering')); ?>" />
    <input type="hidden" name="filter_order_dir" value="<?php echo $this->escape($this->state->get('list.direction')); ?>" />
    <input type="hidden" name="view" value="user_manager" />
    <?php echo JHtml::_('form.token'); ?>
</form>