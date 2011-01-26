<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

$attribs = 'style="height: 16px; width: 16px;"';
$image = JHTML::_('image.site', 'edit.png', 'components/com_thm_organizer/assets/images/', NULL, NULL, NULL, $attribs);
?>
<div class="thm_organizer_schedulelist">
	<table style="margin: 0px;">
		<tr>
			<td>
				<table border="0" cellspacing="0" align="left" class="listArea">
					<thead>
						<tr>
							<td />
							<td><h4>Filename</h4></td>
							<td><h4>Upload Date</h4></td>
							<td />
							<td />
							<td><h4>Description</h4></td>
							<td />
						</tr>
					</thead>
					<?php 
					//our server puts index.php at the end of the root verzeichnis this line eliminates problems that occur
					$temp = str_replace("/index.php", "",$_SERVER['SCRIPT_NAME']);
					if($this->schedules != "empty" && count($this->schedules) > 0)
					{
						foreach($this->schedules as $schedule)
						{ ?>
					<tr>
						<td>
							<?php if($schedule->active){ ?>
							<img style="height:16px; width:16px;" 
								 src="<?php echo 'components/com_thm_organizer/assets/images/active.png'; ?>" 
								 alt="Active">
							<?php } ?>
						</td>
						<td class="file" valign="middle"><?php echo $schedule->filename ?></td>
						<td><?php echo $schedule->includedate; ?></td>
						<?php if($schedule->active)
						{ ?>
						<td>
							<?php echo $this->links['unpublish'][$schedule->id] ?>
						</td>
						<td />
						<?php }
						else
						{ ?>
						<td>
							<?php echo $this->links['publish'][$schedule->id] ?>
						</td>
						<td>
							<?php echo $this->links['delete'][$schedule->id] ?>
						</td>
						<?php
						} 
						?>
						<td>
							<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?controller=schedulelist&view=schedulelist') ?>" method="post">
								<table style="margin: 5px;">
									<tr>
										<td style="padding:0px 5px;">
											<input type='text' name='description' 
												size='50' value='<?php echo $schedule->description ?>' />
										</td>
										<td style="padding:0px 5px;">
											<input type='hidden' name='task' value='updatetext' />
											<input type='hidden' name='schedule_id' value='<?php echo $schedule->id; ?>' />
											<input type="hidden" name="semesterid" value="<?php echo $this->sid; ?>" />
											<span class="hasTip" title="Beschreibung::Hinzuf&uuml;gen/Editieren der Beschreibung dieses Stundenplans.">
											<input type='image' style="height:16px; width: 16px;"
												   src='<?php 
												   			$server = str_replace("index.php", "", $_SERVER['SCRIPT_NAME']);
												   			echo $server."components/com_thm_organizer/assets/images/edit.png"; ?>'
												   name="submit" value="Submit" />
											</span>
										</td>
									</tr>
								</table>	
							</form>
						</td>
					</tr>
				<?php }
				} 
				else echo "<tr><td></td><td><h4>".JText::_("Es gibt keine gespeicherte Stundenpl&aumlne").".</h4></td></tr>"					
				?>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?controller=schedulelist&view=schedulelist') ?>" method="post">
					<table border="0" cellspacing="0" cellpadding="1" align="left" class="uploadArea">
							<tr>
								<td>
									<input name="schedule" type="file" id="schedule" />
									<input type="hidden" name="task" value="schedule_upload" />
									<input type="hidden" name="semesterid" value="<?php echo $this->sid; ?>" />
								</td>
								<td><input name="schedule_upload" type="submit" id="schedule_upload" value="Hochladen" /></td>
							</tr>
					</table>
				</form>
			</td>
		</tr>
	</table>
</div>