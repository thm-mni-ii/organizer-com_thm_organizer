<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        default.php
 * @description default template for the room resource manager view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die ('Restricted access');
?>

<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>" method="post" name="adminForm">
<table class="adminform">
	<tr>
		<td>
			<?php
			echo "<span title='Filter nach Vorname, Name oder Benutzerkennung'>" . JText::_( 'SEARCH' ) . "</span>" ;
			//echo "&nbsp;" . $this->lists['filter'];
			?>
			<input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area" onChange="document.adminForm.submit();" />
		</td>
		<td>
			<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
			<button onclick="this.form.getElementById('search').value='';this.form.getElementById('groupFilters').value='0';this.form.getElementById('rolesFilters').value='0';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
		</td>
	</tr>
</table>
<div id="editcell">
<table class="adminlist">
	<thead>
		<tr>
			<th width="1"><?php echo JText::_( 'NUM' ); ?></th>
			<th width="1"><input type="checkbox" name="toggle" value=""
				onclick="checkAll(<?php echo count( $this->items ); ?>);" /></th>

			<th nowrap="nowrap"><?php echo JHTML::_('grid.sort', JText::_('gpuntisID'), 'gpuntisID', $this->lists['order_Dir'], @$this->lists['order'] ); ?>
			</th>
			<th align="center"><?php echo JHTML::_('grid.sort', JText::_('Name'), 'name', $this->lists['order_Dir'], @$this->lists['order'] ); ?>
			</th>
			<th align="center"><?php echo JHTML::_('grid.sort', JText::_('Manager'), 'userName', $this->lists['order_Dir'], @$this->lists['order'] ); ?>
			</th>
			<th align="center"><?php echo JHTML::_('grid.sort',JText::_('Type'), 'type', $this->lists['order_Dir'], @$this->lists['order'] ); ?>
			</th>
			<th align="center"><?php echo JHTML::_('grid.sort', JText::_('Capacity'), 'capacity', $this->lists['order_Dir'], @$this->lists['order'] ); ?>
			</th>
			<th nowrap="nowrap"><?php echo JHTML::_('grid.sort', JText::_('Department'), 'dptName', $this->lists['order_Dir'], @$this->lists['order'] ); ?>
			</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count($this->items); $i < $n; $i++){
		$row = &$this->items[$i];
		$checked  = JHTML::_('grid.id',   $i, $row->id );
		$link = JRoute::_('index.php?option=com_thm_organizer&controller=virtual_schedule_manager&task=edit&cid[]='.$row->id);
		?>
	<tr class="<?php echo "row".$k; ?>">
		<td align="center"><?php echo $row->id; ?></td>
		<td align="center"><?php echo $checked; ?></td>

		<td align="center"><?php echo $row->gpuntisID; ?></td>
		<td align="center"><?php echo $row->name; ?></td>
		<td align="center"><?php echo $row->userName;?></td>
		<td align="center"><?php echo $row->type; ?></td>
		<td align="center"><?php echo $row->capacity; ?></td>
		<td align="center"><?php echo $row->dptName; ?></td>
	</tr>
	<?php
	$k = 1 -   $k;
	}
	?>
	<tfoot>
		<tr>
			<td colspan="10"><?php echo $this->pagination->getListFooter(); ?></td>
		</tr>
	</tfoot>
</table>
</div>

<input type="hidden" name="task" value="" />
<input type="hidden" name="view" value="resource_room_manager" />
<input type="hidden" name="grchecked" value="off" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

</form>
