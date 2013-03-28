<?php
/**
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view colors default head
 * @description THM_Curriculum component admin view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$listOrder = $this->state->get('list.ordering', 'ordering');
$listDirn = $this->state->get('list.direction');
$idTitle = JText::_('COM_THM_ORGANIZER_ID') . '::' . JText::_('COM_THM_ORGANIZER_CLM_ID_DESC');
$nameTitle = JText::_('COM_THM_ORGANIZER_NAME') . '::' . JText::_('COM_THM_ORGANIZER_CLM_NAME_DESC');
$colorTitle = JText::_('COM_THM_ORGANIZER_CLM_COLOR') . '::' . JText::_('COM_THM_ORGANIZER_CLM_COLOR_DESC');
$hexTitle = JText::_('COM_THM_ORGANIZER_CLM_CODE') . '::' . JText::_('COM_THM_ORGANIZER_CLM_CODE_DESC');
JHtml::_('behavior.tooltip');
?>
<tr>
	<th width="3%">
		<input type="checkbox" name="toggle" value=""
			   onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>
	<th title="<?php echo $idTitle; ?>" class="hasTip" width="5%">
		<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_ID'), 'id', $listDirn, $listOrder); ?>
	</th>
	<th title="<?php echo $nameTitle; ?>" class="hasTip" width="30%">
		<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_NAME'), 'name', $listDirn, $listOrder); ?>
	</th>
	<th title="<?php echo $colorTitle; ?>" class="hasTip" width="20%">
		<?php echo JText::_('COM_THM_ORGANIZER_CLM_COLOR'); ?>
	</th>
	<th title="<?php echo $hexTitle; ?>" class="hasTip" width="15%">
		<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_CLM_CODE'), 'color', $listDirn, $listOrder); ?>
	</th>
</tr>
