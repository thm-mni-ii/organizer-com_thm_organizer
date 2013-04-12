<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view colors default head
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$listOrder = $this->state->get('list.ordering', 'ordering');
$listDirn = $this->state->get('list.direction');
$nameTitle = JText::_('COM_THM_ORGANIZER_NAME') . '::' . JText::_('COM_THM_ORGANIZER_FLM_NAME_DESC');
$colorTitle = JText::_('COM_THM_ORGANIZER_COLOR') . '::' . JText::_('COM_THM_ORGANIZER_FLM_COLOR_DESC');
$idTitle = JText::_('COM_THM_ORGANIZER_GPUNTISID') . '::' . JText::_('COM_THM_ORGANIZER_FLM_GPUNTISID_DESC');
JHtml::_('behavior.tooltip');
?>
<tr>
	<th width="3%">
		<input type="checkbox" name="toggle" value=""
			   onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>
	<th title="<?php echo $nameTitle; ?>" class="hasTip" width="42%">
		<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_NAME'), 'name', $listDirn, $listOrder); ?>
	</th>
	<th title="<?php echo $idTitle; ?>" class="hasTip" width="22%">
		<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_GPUNTISID'), 'id', $listDirn, $listOrder); ?>
	</th>
	<th title="<?php echo $colorTitle; ?>" class="hasTip" width="22%">
		<?php echo JText::_('COM_THM_ORGANIZER_COLOR'); ?>
	</th>
</tr>
