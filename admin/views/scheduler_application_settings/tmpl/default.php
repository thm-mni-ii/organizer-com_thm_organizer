<?php defined('_JEXEC') or die('Restricted access');?>
<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Allgemein' ); ?></legend>
		<table class="admintable">
			<tr>
				<td class="key">
					<label for="scheduler_downFolder">Download Folder</label>
				</td>
				<td>
					<input class="text_area" type="text" name="scheduler_downFolder" id="scheduler_downFolder" size="100" maxlength="100"
							value="<?php echo $this->settings[0]->downFolder; ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="scheduler_vacationcat">Vacation Category</label>
				</td>
				<td>
					<?php echo $this->categories;?><br/>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'eStudy' ); ?></legend>
		<table class="admintable">
			<tr>
				<td class="key">
					<label for="scheduler_eStudyPath">Path</label>
				</td>
				<td>
					<input class="text_area" type="text" name="scheduler_eStudyPath" id="scheduler_eStudyPath" size="100" maxlength="100"
							value="<?php echo $this->settings[0]->eStudyPath; ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="scheduler_eStudywsapiPath">wsapi Path</label>
				</td>
				<td>
					<input class="text_area" type="text" name="scheduler_eStudywsapiPath" id="scheduler_eStudywsapiPath" size="100" maxlength="100"
							value="<?php echo $this->settings[0]->eStudywsapiPath; ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="scheduler_eStudyCreateCoursePath">Create Course Path</label>
				</td>
				<td>
					<input class="text_area" type="text" name="scheduler_eStudyCreateCoursePath" id="scheduler_eStudyCreateCoursePath" size="100" maxlength="100"
							value="<?php echo $this->settings[0]->eStudyCreateCoursePath; ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="scheduler_eStudySoapSchema">Soap Schema</label>
				</td>
				<td>
					<input class="text_area" type="text" name="scheduler_eStudySoapSchema" id="scheduler_eStudySoapSchema" size="100" maxlength="100"
							value="<?php echo $this->settings[0]->eStudySoapSchema; ?>" />
				</td>
			</tr>
		</table>
	</fieldset>
</div>
<input type="hidden" name="option" value="com_thm_organizer" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="scheduler_application_settings" />
</form>
