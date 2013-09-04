<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        program manager view default layout
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>
<form
    action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>"
    method="post" name="adminForm">
    <fieldset id="filter-bar" class='filter-bar'>
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
        <div class="filter-select fltrt">
            <select name="filter_degree" class="inputbox" onchange="this.form.submit()">
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_PRM_SEARCH_DEGREES'); ?></option>
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_PRM_ALL_DEGREES'); ?></option>
                    <?php echo JHtml::_('select.options', $this->degrees, 'id', 'name', $this->state->get('filter.degree'));?>
            </select>
            <select name="filter_version" class="inputbox" onchange="this.form.submit()">
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_PRM_SEARCH_VERSIONS'); ?></option>
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_PRM_ALL_VERSIONS'); ?></option>
                    <?php echo JHtml::_('select.options', $this->versions, 'id', 'value', $this->state->get('filter.version'));?>
            </select>
            <select name="filter_field" class="inputbox" onchange="this.form.submit()">
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_PRM_SEARCH_FIELDS'); ?></option>
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_PRM_ALL_FIELDS'); ?></option>
                    <?php echo JHtml::_('select.options', $this->fields, 'id', 'field', $this->state->get('filter.field'));?>
            </select>
        </div>
    </fieldset>
    <table class="adminlist">
        <thead>
            <tr>
                <th width="3%"><input type="checkbox" name="toggle" value=""
                    onclick="checkAll(<?php echo count($this->items); ?>);" />
                </th>
                <th width="32%">
                    <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_PRM_DEGREE_PROGRAM'), 'subject', $listDirn, $listOrder); ?>
                </th>
                <th width="10%">
                    <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_DEGREE'), 'd.name', $listDirn, $listOrder); ?>
                </th>
                <th width="10%">
                    <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_VERSION'), 'version', $listDirn, $listOrder); ?>
                </th>
                <th width="20%">
                    <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_FIELD'), 'field', $listDirn, $listOrder); ?>
                </th>
                <th width="10%">
                    <?php echo JText::_('COM_THM_ORGANIZER_PRM_LSF_MODELED'); ?>
                </th>
                <th width="10%">
                    <?php echo JText::_('COM_THM_ORGANIZER_PRM_EDIT_STRUCTURE'); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="7"><?php echo $this->pagination->getListFooter(); ?></td>
            </tr>
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="view" value="program_manager" />
            <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
            <?php echo JHtml::_('form.token'); ?>
        </tfoot>
        <tbody>
<?php
foreach ($this->items as $i => $item)
{
    $check = !empty($item->lsfFieldID) && !empty($item->lsfDegree)?
        '<img src="templates/thmstylebackend/images/admin/tick.png" />' : '';
    if (!empty($item->mapping))
    {
        $mappingLink = "<a title='" . JText::_('COM_THM_ORGANIZER_PRM_CHILDREN') . "' ";
        $mappingLink .= "href='" . JRoute::_('index.php?option=com_thm_organizer&view=pool_manager&programID=' . $item->id) . "' >";
        $mappingLink .= "<img src='components/com_thm_organizer/assets/images/pools.png' /></a>";
    }
    else
    {
        $mappingLink = '';
    }
?>
            <tr class="row<?php echo $i % 2; ?>">

                <td align="center">
                    <?php echo JHtml::_('grid.id', $i, $item->id) ?>
                </td>
                <td>
                    <a href="index.php?option=com_thm_organizer&view=program_edit&id=<?php echo $item->id; ?>">
                        <?php echo $item->subject; ?>
                    </a>
                </td>
                <td>
                    <a href="index.php?option=com_thm_organizer&view=program_edit&id=<?php echo $item->id; ?>">
                        <?php echo $item->abbreviation; ?>
                    </a>
                </td>
                <td>
                    <a href="index.php?option=com_thm_organizer&view=program_edit&id=<?php echo $item->id; ?>">
                        <?php echo $item->version; ?>
                    </a>
                </td>
                <td style="background-color: #<?php echo $item->color; ?>">
                    <?php echo $item->field; ?>
                </td>
                <td align="center">
                    <?php echo $check; ?>
                </td>
                <td align="center">
                    <?php echo $mappingLink; ?>
                </td>
            </tr>
<?php
}
?>
        </tbody>
    </table>
</form>
