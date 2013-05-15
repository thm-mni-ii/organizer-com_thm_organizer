<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view degrees default head
 * @description THM_Curriculum component admin view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$listOrder = $this->state->get('list.ordering', 'ordering');
$listDirn = $this->state->get('list.direction');
?>
<tr>
	<th width="3%">
		<input type="checkbox" name="toggle" value=""
			   onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>
	<th width="50%">
		<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_NAME'), 'name', $listDirn, $listOrder); ?>
	</th>
	<th width="22%">
		<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_ABBREVIATION'), 'abbreviation', $listDirn, $listOrder); ?>
	</th>
	<th width="22%">
		<?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_DEG_LSF_DEGREE_TITLE'), 'lsfDegree', $listDirn, $listOrder); ?>
	</th>
</tr>
