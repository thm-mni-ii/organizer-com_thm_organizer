<?php
?>

<div class="times">
	<?php if(count($this->semesters))
	{ ?>
	<h3>Bitte w&auml;hlen Sie einen Semester:</h3>
	<form enctype='multi' name='semesterlist' method='post'
			action='<?php echo JRoute::_( 'index.php?option=com_thm_organizer&view=schedulelist') ?>' >
		<select name="semesterid">
	<?php
		foreach($this->semesters as $semester)
		{
			echo "<option value='".$semester->sid."'>".$semester->orgunit."-".$semester->semester."</option>";
		}
	 ?>
		</select>
		<input type="submit" value="Los!">
	</form>
	<?php } ?>
</div>