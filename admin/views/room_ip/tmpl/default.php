<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>
		<table class="admintable">
			<tr>
				<td style="width: 20px;" align="right" class="key">
					<label for="room">Raum</label>
				</td>
				<td>
					<input class="text_area" type="text" name="room" id="room" size="6" maxlength="6" 
							value="<?php echo $this->room_ip->room;?>" />
				</td>
			</tr>
			<tr>
				<td style="width: 20px;" align="right" class="key">
					<label for="points">IP</label>
				</td>
				<td>
					<input class="text_area" type="text" name="ip" id="ip" size="6" maxlength="20"
 							value="<?php echo $this->room_ip->ip;?>" />
				</td>
			</tr>
			<tr>
				<td style="width: 20px;" align="right" class="key">
					<label for="points">Semester</label>
				</td>
				<td>
					<?php echo $this->semesterbox;?>
				</td>
			</tr>
		</table>
	</fieldset>
</div>
<div class="clr"></div>
<input type="hidden" name="option" value="com_thm_organizer" />
<input type="hidden" name="id" value="<?php echo $this->room_ip->ip; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="room_ip" />
</form>
