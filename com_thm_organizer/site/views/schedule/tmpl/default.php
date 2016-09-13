<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  COM_THM_ORGANIZER.site
 * @name        default .php
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
$noMobile = !$this->isMobile ? 'no-mobile' : '';
?>

<div class="organizer <?php echo $noMobile; ?>">
	<input id="time-menu-item" type="radio" name="schedule-menu" role="menubar">
	<input id="schedule-selection-menu-item" type="radio" name="schedule-menu" role="menubar">
	<input id="export-menu-item" type="radio" name="schedule-menu" role="menubar">
	<input id="schedule-form-menu-item" type="radio" name="schedule-menu" role="menubar">

	<div class="menu-bar">
		<a href="<?php echo JPATH_ROOT ?>" tabindex="1">
			<img class="home" src="media/COM_THM_ORGANIZER/images/thm.svg"/>
		</a>
		<label for="schedule-form-menu-item"><span class="icon-menu-3"></span></label>
		<label for="time-menu-item"><span class="icon-clock"></span></label>
		<label for="schedule-selection-menu-item"><span class="icon-calendars"></span></label>
		<button type="button"><span class="icon-save"></span></button>
		<label for="export-menu-item"><span class="icon-download"></span></label>
	</div>

	<!-- Menu -->
	<div id="time-selection" tabindex="0" class="selection">
		<ul>
		</ul>
	</div>
	<div id="schedule-selection" tabindex="0" class="selection">
		<ul>
			<?php
			foreach ($this->schedules as $schedule)
			{
				echo '<li><label for="' . $schedule->id . '" tabindex="0">' .
					$schedule->name . ' - ' . $schedule->pool .
					'</label></li>';
			}
			?>
		</ul>
	</div>
	<div id="export-selection" tabindex="0" class="selection">
		<ul>
			<li>
				<button type="button" value="pdf"><span class="icon-file-pdf"></span>
					<?php echo JText::_('COM_THM_ORGANIZER_PDF_SCHEDULE') ?>
				</button>
			</li>
			<li>
				<button type="button" value="excel"><span class="icon-file-excel"></span>
					<?php echo JText::_('COM_THM_ORGANIZER_ACTION_EXPORT_EXCEL') ?>
				</button>
			</li>
			<li>
				<button type="button" value="iCal"><span class="icon-calendar"></span>
					<?php echo JText::_('COM_THM_ORGANIZER_SCHEDULER_ICS') ?>
				</button>
			</li>
		</ul>
	</div>
	<!-- on last position, because on big devices it is not expandable and the other menus should be near their icon -->
	<div id="schedule-form" tabindex="0" class="selection">
		<form action="">
			<select id="department" name="department" required>
				<option value="0" selected><?php echo JText::_("JALL"); ?></option>
				<?php
				foreach ($this->departments as $department)
				{
					echo "<option value=" . $department->id . ">" . $department->name . "</option>";
				}
				?>
			</select>
			<select id="program" name="program" multiple required>
				<!-- filled by ajax -->
			</select>
			<select id="pool" name="pool" disabled multiple required>
				<!-- filled by ajax -->
			</select>
			<select id="room" name="room">
				<!-- filled by ajax -->
			</select>
			<select id="teacher" name="teacher">
				<!-- filled by ajax -->
			</select>
		</form>
	</div>

	<div class="date-input">
		<button id="previous-month" class="controls" type="button">
			<span class="icon-backward"></span>
		</button>
		<button id="previous-day" class="controls" type="button">
			<span class="icon-arrow-left"></span>
		</button>
		<form>
			<span id="weekday">Mo</span>
			<!--?php echo  JHtml::calendar(date_format(new DateTime(), 'd.m.Y'), 'date', 'date', '%d.%m.%Y'); ?-->
			<input id="date" type="date" name="date" required onchange="setUpCalendar();"/>
			<button id="calendar-icon" type="button" onclick="showCalendar();">
				<span class="icon-calendar"></span>
			</button>
			<div id="choose-date">
				<table id="calendar-table">
					<thead>
					<tr>
						<td colspan="1">
							<button onclick="previousMonth();" type="button">
								<span class="icon-arrow-left"></span>
							</button>
						</td>
						<td colspan="5">
							<span id="display-month"></span> <span id="display-year"></span>
						</td>
						<td colspan="1">
							<button onclick="nextMonth();" type="button">
								<span class="icon-arrow-right"></span>
							</button>
						</td>
					</tr>
					</thead>
					<thead>
					<tr>
						<td><?php echo JText::_("MON"); ?></td>
						<td><?php echo JText::_("TUE"); ?></td>
						<td><?php echo JText::_("WED"); ?></td>
						<td><?php echo JText::_("THU"); ?></td>
						<td><?php echo JText::_("FRI"); ?></td>
						<td><?php echo JText::_("SAT"); ?></td>
						<td><?php echo JText::_("SUN"); ?></td>
					</tr>
					</thead>
					<tbody>
					<!-- generated code with JavaScript -->
					</tbody>
					<tfoot>
					<tr>
						<td colspan="7">
							<button type="button" class="today" onclick="insertDate();setUpCalendar();">
								<?php echo JText::_("COM_THM_ORGANIZER_TODAY"); ?>
							</button>
						</td>
					</tr>
					</tfoot>
				</table>
			</div>
		</form>
		<button id="next-day" class="controls" type="button">
			<span class="icon-arrow-right"></span>
		</button>
		<button id="next-month" class="controls" type="button">
			<span class="icon-forward"></span>
		</button>
	</div>

	<div id="scheduleWrapper" class="scheduleWrapper">
		<?php
		for ($schedule = 0; $schedule < count($this->schedules); ++$schedule)
		{
			$scheduler = $this->schedules[$schedule];
			echo '<input class="scheduler-input" type="radio" id="' . $scheduler->id . '" name="schedules"';
			if ($schedule == 0)
			{
				echo " checked";
			}
			echo '>';
			?>
			<div id="<?php echo $scheduler->id ?>-scheduler" class="scheduler">
				<table>
					<caption><?php echo $scheduler->name . ' - ' . $scheduler->pool ?></caption>
					<thead>
					<tr>
						<th><?php echo JText::_('COM_THM_ORGANIZER_TIME') ?></th>
						<?php
						$daysOfTheWeek = array(JText::_('Monday'), JText::_('Tuesday'), JText::_('Wednesday'),
						                       JText::_('Thursday'), JText::_('Friday'), JText::_('Saturday'),
						                       JText::_('Sunday'));

						for ($weekday = $this->defaultGrid->startDay - 1; $weekday < $this->defaultGrid->endDay; ++$weekday)
						{
							echo '<th>' . $daysOfTheWeek[$weekday] . '</th>';
						}
						?>
					</tr>
					</thead>
					<tbody>
					<?php
					$periods = get_object_vars($this->defaultGrid->periods);
					for ($period = 1; $period <= count($periods); ++$period)
					{
						if ($period == 4)
						{
							?>
							<tr>
								<td class="break" colspan="7"><?php echo JText::_('COM_THM_ORGANIZER_LUNCHTIME') ?></td>
							</tr>
							<?php
						}
						?>
						<tr>
							<td>
                            <span class="time-semester">
<?php
echo THM_OrganizerHelperComponent::formatTime($periods[$period]->startTime);
echo "<br> - <br>";
echo THM_OrganizerHelperComponent::formatTime($periods[$period]->endTime);
?>
                            </span>
							</td>
							<?php
							foreach ($scheduler->days as $day)
							{
								?>
								<td>
									<?php
									foreach ($day->$period as $lesson)
									{
										?>
										<div class="lesson">
											<?php
											if (isset($lesson->time))
											{
												echo '<span class="own-time">' .
													THM_OrganizerHelperComponent::formatTime($lesson->time) .
													'</span> ';
											}

											if ($this->languageTag == 'de-DE' AND isset($lesson->name_de))
											{
												echo '<span class="name">' . $lesson->name_de . '</span> ';
											}
											elseif ($this->languageTag == 'en-GB' AND isset($lesson->name_en))
											{
												echo '<span class="name">' . $lesson->name_en . '</span> ';
											}

											if (isset($lesson->module))
											{
												echo '<span class="module">' . $lesson->module . '</span> ';
											}

											if (isset($lesson->teacher))
											{
												echo '<span class="person">' . $lesson->teacher . '</span> ';
											}

											if (isset($lesson->misc))
											{
												echo '<span class="misc">' . $lesson->misc . '</span> ';
											}

											if (isset($lesson->room))
											{
												?>
												<span class="locations">
			                                        <span class="old"></span>
			                                        <span class="new">
			                                            <a href="#"><?php echo $lesson->room ?></a>
			                                        </span>
			                                    </span>
												<?php
											}
											?>
											<button class="add-lesson"><span class="icon-plus-2"></span></button>
										</div>
										<?php
									}
									?>
								</td>
								<?php
							} // Days
							?>
						</tr>
						<?php
					} // Blocks
					?>
					</tbody>
				</table>
			</div>
			<?php
		} // Schedules for-loop
		?>
	</div>
</div>
