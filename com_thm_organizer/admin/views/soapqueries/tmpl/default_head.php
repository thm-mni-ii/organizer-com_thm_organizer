<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view soapqueries default head
 * @description THM_Curriculum component admin view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$saveOrder	= $listOrder == 'ordering';
?>
<tr>

	<th width="20"><input type="checkbox" name="toggle" value=""
		onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>

	<th><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_NAME'), 'name', $listDirn, $listOrder);  ?>
	</th>
	<th><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUBMENU_SOAPQUERIES_OBJECT'), 'lsf_object', $listDirn, $listOrder);  ?>
	</th>
	<th><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUBMENU_SOAPQUERIES_MAJOR'), 'lsf_study_path', $listDirn, $listOrder);  ?>
	</th>
	<th><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_DGP_DEGREE_TITLE'), 'lsf_degree', $listDirn, $listOrder);  ?>
	</th>
	<th><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUBMENU_SOAPQUERIES_PO'), 'lsf_pversion', $listDirn, $listOrder);  ?>
	</th>
	<th><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUBMENU_SOAPQUERIES_ID'), 'id', $listDirn, $listOrder);  ?>
	</th>
</tr>
