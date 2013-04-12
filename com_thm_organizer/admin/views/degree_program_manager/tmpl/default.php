<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view majors default
 * @description THM_Curriculum component admin view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=majors'); ?>"
	method="post" name="adminForm">
	<table class="adminlist">
		<thead>
			<tr>
				<th width="3%"><input type="checkbox" name="toggle" value=""
					onclick="checkAll(<?php echo count($this->items); ?>);" />
				</th>
				<th width="72%">
					<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_DGP_DEGREE_PROGRAM'), 'degreeProgram', $listDirn, $listOrder); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('COM_THM_ORGANIZER_DGP_LSF_MODELED'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('COM_THM_ORGANIZER_DGP_EDIT_STRUCTURE'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="5"><?php echo $this->pagination->getListFooter(); ?></td>
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
	$check = !empty($item->lsfFieldID) && !empty($item->lsfDegree)?
		'<img src="templates/thmstylebackend/images/admin/tick.png" />' : '';
?>
			<tr class="row<?php echo $i % 2; ?>">

				<td align="center">
					<?php echo JHtml::_('grid.id', $i, $item->id) ?>
				</td>
				<td>
					<a href="index.php?option=com_thm_organizer&view=degree_program_edit&id=<?php echo $item->id; ?>">
						<?php echo $item->degreeProgram; ?>
					</a>
				</td>
				<td align="center">
					<?php echo $check; ?>
				</td>
				<td align="center">
					<a title="<?php echo JText::_('COM_THM_ORGANIZER_SHOW_CONTENT'); ?>"
					   href="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=mappings&id=' . $item->id) ?>">
						<img src="components/com_thm_organizer/assets/images/list.png" />
					</a>
				</td>
			</tr>
<?php
}
?>
		</tbody>
	</table>
</form>
