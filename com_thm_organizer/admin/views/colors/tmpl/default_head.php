<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view colors default head
 * @description THM_Curriculum component admin view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');

$saveOrder = $listOrder == 'ordering';
?>
<tr>
	<th width="3%"><input type="checkbox" name="toggle" value=""
		onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>

	<th width="57%"><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUBMENU_COLORS_NAME'), 'name', $listDirn, $listOrder); ?>
	</th>
	<th width="15%"><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUBMENU_COLORS_COLOR_CODE'), 'color', $listDirn, $listOrder); ?>
	</th>
	<th width="20%"><?php echo JText::_('COM_THM_ORGANIZER_SUBMENU_COLORS_COLOR') ?>
	</th>
	<th width="5%"><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUBMENU_COLORS_ID'), 'id', $listDirn, $listOrder); ?>
	</th>
</tr>
