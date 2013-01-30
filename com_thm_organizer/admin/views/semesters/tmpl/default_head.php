<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view semesters default head
 * @description THM_Curriculum component admin view
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
?>
<tr>
	<th width="3%"><input type="checkbox" name="toggle" value=""
		onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>
	<th width="52%"><?php echo JHTML::_('grid.sort',
			JText::_('COM_THM_ORGANIZER_SUBMENU_SEMESTERS_NAME'), 'name', $listDirn, $listOrder
			); ?>
	</th>
	<th width="20%"><?php echo JHTML::_('grid.sort',
			JText::_('COM_THM_ORGANIZER_SUBMENU_SEMESTERS_SHORT_TITLE_DE'), 'short_title_de', $listDirn, $listOrder
			); ?>
	</th>
	<th width="20%"><?php echo JHTML::_('grid.sort',
			JText::_('COM_THM_ORGANIZER_SUBMENU_SEMESTERS_SHORT_TITLE_EN'), 'short_title_en', $listDirn, $listOrder
			); ?>
	</th>
	<th width="5%"><?php echo JHTML::_('grid.sort',
			JText::_('COM_THM_ORGANIZER_SUBMENU_SEMESTERS_ID'), 'id', $listDirn, $listOrder
			); ?>
	</th>
</tr>
