<?php
/**
 * This file contains the data type class Image.
 *
 * PHP version 5
 *
 * @category Joomla Programming Weeks SS2008: FH Giessen-Friedberg
 * @package  com_staff
 * @author   Sascha Henry <sascha.henry@mni.fh-giessen.de>
 * @author   Christian Gueth <christian.gueth@mni.fh-giessen.de>
 * @author   Severin Rotsch <severin.rotsch@mni.fh-giessen.de>
 * @author   Martin Karry <martin.karry@mni.fh-giessen.de>
 * @author   Rene Bartsch <rene.bartsch@mni.fh-giessen.de>
 * @author   Dennis Priefer <dennis.priefer@mni.fh-giessen.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @link     http://www.mni.fh-giessen.de
 **/
defined('_JEXEC') or die ('Restricted access');
?>

<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>" method="post" name="adminForm">
<table class="adminform">
	<tr>
		<td>
			<?php
			echo "<span title='".JText::_( "COM_THM_ORGANIZER_VSM_LABEL_DESCRIPTION" )."'>" . JText::_( "COM_THM_ORGANIZER_VSM_LABEL_SEARCH" ) . "</span>" ;
			//echo "&nbsp;" . $this->lists['filter'];
			?>
			<input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area" onChange="document.adminForm.submit();" />
		</td>
		<td>
			<button onclick="this.form.submit();"><?php echo JText::_( "COM_THM_ORGANIZER_VSM_BUTTON_GO" ); ?></button>
			<button onclick="this.form.getElementById('search').value='';this.form.getElementById('groupFilters').value='0';this.form.getElementById('rolesFilters').value='0';this.form.submit();"><?php echo JText::_( "COM_THM_ORGANIZER_VSM_BUTTON_RESET" ); ?></button>
		</td>
	</tr>
</table>
<div id="editcell">
<table class="adminlist">
	<thead>
		<tr>
			<th width="1"><?php echo JText::_( "COM_THM_ORGANIZER_VSM_LABEL_KEY" ); ?></th>
			<th width="1"><input type="checkbox" name="toggle" value=""
				onclick="checkAll(<?php echo count( $this->items ); ?>);" /></th>

			<th nowrap="nowrap"><?php echo JHTML::_('grid.sort', JText::_( "COM_THM_ORGANIZER_VSM_LABEL_NAME" ), 'name', $this->lists['order_Dir'], @$this->lists['order'] ); ?>
			</th>
			<th align="center"><?php echo JHTML::_('grid.sort', JText::_( "COM_THM_ORGANIZER_VSM_LABEL_TYPE" ), 'type', $this->lists['order_Dir'], @$this->lists['order'] ); ?>
			</th>
			<th align="center"><?php echo JHTML::_('grid.sort', JText::_( "COM_THM_ORGANIZER_VSM_LABEL_RESPONSIBLE" ), 'responsible', $this->lists['order_Dir'], @$this->lists['order'] ); ?>
			</th>
			<th align="center"><?php echo JHTML::_('grid.sort',JText::_( "COM_THM_ORGANIZER_VSM_LABEL_DEPARTMENT" ), 'department', $this->lists['order_Dir'], @$this->lists['order'] ); ?>
			</th>
			<th align="center"><?php echo JHTML::_('grid.sort', JText::_( "COM_THM_ORGANIZER_VSM_LABEL_ELEMENTS" ), 'eid', $this->lists['order_Dir'], @$this->lists['order'] ); ?>
			</th>
			<th nowrap="nowrap"><?php echo JHTML::_('grid.sort', JText::_( "COM_THM_ORGANIZER_VSM_LABEL_SEMESTER" ), 'semesterid', $this->lists['order_Dir'], @$this->lists['order'] ); ?>
			</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$k = 0;
	for ($i=0, $n=count($this->items); $i < $n; $i++){
		$row = &$this->items[$i];
		$checked  = JHTML::_('grid.id',   $i, $row->id );
		$link = JRoute::_('index.php?option=com_thm_organizer&controller=virtual_schedule_manager&task=edit&cid[]='.$row->id);
		?>
	<tr class="<?php echo "row".$k; ?>">
		<td><?php echo $row->id; ?></td>
		<td><?php echo $checked; ?></td>

		<td><?php echo $row->name; ?></td>
		<td><?php echo $row->type; ?></td>
		<td><?php echo $row->responsible;?></td>
		<td><?php echo $row->department; ?></td>
		<td><?php echo $row->eid; ?></td>
		<td><?php echo $row->semesterid; ?></td>
	</tr>
	<?php
	$k = 1 - $k;
	}
	?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="10"><?php echo $this->pagination->getListFooter(); ?></td>
		</tr>
	</tfoot>
</table>
</div>

<input type="hidden" name="task" value="" />
<input type="hidden" name="view" value="virtual_schedule_manager" />
<input type="hidden" name="grchecked" value="off" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

</form>
