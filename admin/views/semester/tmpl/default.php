<?php defined('_JEXEC') or die('Restricted access');?>
<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>
		<table class="admintable">
			<tr>
				<td style="width: 20px;" align="right" class="key">
					<label for="orgunit">Organization</label>
				</td>
				<td>
					<input class="text_area" type="text" name="orgunit" id="ecname" size="25" maxlength="20" 
							value="<?php echo $this->semester->orgunit;?>" />
				</td>
			</tr>
			<tr>
				<td style="width: 20px;" align="right" class="key">
					<label for="ecdescription">Semester</label>
				</td>
				<td>
					<input class="text_area" type="text" name="semester" id="ecname" size="25" maxlength="20" 
							value="<?php echo $this->semester->semester;?>" />
				</td>
			</tr>
			<tr>
				<td style="width: 20px;" align="right" class="key">
					<label for="globalp">Verantwortliche</label>
				</td>
				<td>
					<input class="text_area" type="text" name="author" id="ecname" size="25" maxlength="20" 
							value="<?php echo $this->semester->author;?>" />
				</td>
			</tr>
		</table>
	</fieldset>
</div>
<div class="clr"></div>
<input type="hidden" name="option" value="com_thm_organizer" />
<input type="hidden" name="id" value="<?php echo $this->semester->sid; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="semester" />
</form>
