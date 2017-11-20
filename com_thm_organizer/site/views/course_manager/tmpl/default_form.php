<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
?>
<form action="index.php?option=com_thm_organizer&task=participant.changeStatus"
	  method="post" id="adminForm" name="adminForm">

	<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>"/>
	<input type="hidden" name="lessonID" value="<?php echo $this->course["id"]; ?>"/>
	<input type="hidden" name="subjectID" value="<?php echo $this->course["subjectID"]; ?>"/>

	<select title="actions" name="actions" size="1" style="width: 200px" required>
		<option value=""><?php echo $this->lang->_('COM_THM_ORGANIZER_FILTER_ACTION_SELECT') ?></option>
		<option value="0"><?php echo $this->lang->_('COM_THM_ORGANIZER_ACTION_WAIT_LIST') ?></option>
		<option value="1"><?php echo $this->lang->_('COM_THM_ORGANIZER_ACTION_ACCEPT') ?></option>
		<?php
		if ($this->isAdmin)
		{
			echo "<option value='2'>{$this->lang->_('COM_THM_ORGANIZER_ACTION_DELETE')}</option>";
		}
		?>
	</select>

	<button id="submitBtn" type="submit" class="validate btn btn-primary">OK</button>

	<table class="table table-striped">
		<thead>
		<tr>
			<?php

			echo '<th>' . JHTML::_('grid.sort', '', '', $this->sortDirection, $this->sortColumn) . '</th>';
			echo '<th>' . JHTML::_('grid.sort', $this->lang->_('COM_THM_ORGANIZER_NAME'), 'name', $this->sortDirection, $this->sortColumn) . '</th>';
			echo '<th>' . JHTML::_('grid.sort', $this->lang->_('COM_THM_ORGANIZER_PROGRAM'), 'program', $this->sortDirection, $this->sortColumn) . '</th>';
			echo '<th>' . JHTML::_('grid.sort', $this->lang->_('JGLOBAL_EMAIL'), 'email', $this->sortDirection, $this->sortColumn) . '</th>';
			echo '<th>' . JHTML::_('grid.sort', $this->lang->_('COM_THM_ORGANIZER_STATUS_DATE'), 'status_date', $this->sortDirection, $this->sortColumn) . '</th>';
			echo '<th>' . JHTML::_('grid.sort', $this->lang->_('JSTATUS'), 'status', $this->sortDirection, $this->sortColumn) . '</th>';

			?>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($this->items as $item)
		{
			$status = ($item->status ? 'COM_THM_ORGANIZER_COURSE_REGISTERED' : 'COM_THM_ORGANIZER_WAIT_LIST');

			echo '<tr>';

			echo "<td><input title='' type='checkbox' name='checked[]' value='{$item->cid}'/></td>";
			echo "<td>{$item->name}</td>";
			echo "<td>{$item->program}</td>";
			echo "<td>{$item->email}</td>";
			$dateFormat = JComponentHelper::getParams('com_thm_organizer')->get('dateFormat', 'd.m.Y') . " " .
				JComponentHelper::getParams('com_thm_organizer')->get('timeFormat', 'H.i');
			echo '<td>' . JHtml::_('date', $item->status_date, $dateFormat) . '</td>';
			echo "<td>{$this->lang->_($status)}</td>";

			echo '</tr>';
		}
		?>
		</tbody>
	</table>

</form>