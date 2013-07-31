<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view room manager default template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=room_manager'); ?>"
      method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar" class='filter-bar'>
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search">
				<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
			</label>
			<input type="text" name="filter_search" id="filter_search"
				value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
				title="<?php echo JText::_('COM_CATEGORIES_ITEMS_SEARCH_FILTER'); ?>" />
			<button type="submit">
				<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
			</button>
			<button type="button"
				onclick="document.id('filter_search').value='';this.form.submit();">
				<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
			</button>
		</div>
	</fieldset>
	<table class="adminlist">
		<thead>
		<tr>
			<th width="5%">
				<input type="checkbox" name="toggle" value=""
					   onclick="checkAll(<?php echo count($this->items); ?>);" />
			</th>
			<th width="15%">
				<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_NAME'), 'name', $listDirn, $listOrder); ?>
			</th>
			<th width="20%">
				<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_RMM_LONGNAME_TITLE'), 'longname', $listDirn, $listOrder); ?>
			</th>
			<th width="15%">
				<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_GPUNTISID'), 'gpuntisID', $listDirn, $listOrder); ?>
			</th>
			<th width="20%">
				<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_RMM_TYPE_TITLE'), 'type', $listDirn, $listOrder); ?>
			</th>
		</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="7">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token');?>
		</tfoot>
		<tbody>
<?php 
foreach ($this->items as $i => $item)
{
?>
			<tr class="row<?php echo $i % 2; ?>">

				<td>
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<a href="index.php?option=com_thm_organizer&view=room_edit&id=<?php echo $item->id; ?>">
						<?php echo $item->name; ?>
					</a>
				</td>
				<td>
					<a href="index.php?option=com_thm_organizer&view=room_edit&id=<?php echo $item->id; ?>">
						<?php echo $item->longname; ?>
					</a>
				</td>
				<td>
					<a href="index.php?option=com_thm_organizer&view=room_edit&id=<?php echo $item->id; ?>">
						<?php echo $item->gpuntisID; ?>
					</a>
				</td>
				<td>
					<a href="index.php?option=com_thm_organizer&view=room_edit&id=<?php echo $item->id; ?>">
						<?php echo $item->type; ?>
					</a>
				</td>
			</tr>
<?php
}
?>
		</tbody>
	</table>
</form>
