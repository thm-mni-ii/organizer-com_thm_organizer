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

<script type="text/javascript" charset="utf-8">
	<?php require_once "components/com_thm_organizer/views/schedule/tmpl/js/text.js"; ?>
</script>
<div class="organizer <?php echo $noMobile; ?>">
	<input id="time-menu-item" type="checkbox" name="schedule-menu" role="menubar">
	<input id="schedule-selection-menu-item" type="checkbox" name="schedule-menu" role="menubar">
	<input id="export-menu-item" type="checkbox" name="schedule-menu" role="menubar">
	<input id="schedule-form-menu-item" type="checkbox" name="schedule-menu" role="menubar">

	<div class="menu-bar">
		<label for="schedule-form-menu-item"><span class="icon-menu-3"></span></label>
		<label for="time-menu-item"><span class="time-grid-icon"></span></label>
		<label for="schedule-selection-menu-item"><span class="icon-calendar"></span></label>
		<button type="button"><span class="save-schedule-icon"></span></button>
		<label for="export-menu-item"><span class="icon-download"></span></label>
	</div>

	<!-- Menu -->
	<div id="time-selection" tabindex="0" class="selection">
		<select id="grid" name="grid" required>
			<?php
			foreach ($this->grids as $grid)
			{
				$checked = ($grid->name == $this->defaultGrid->name) ? 'checked' : '';
				echo "<option value='" . $grid->grid . "' $checked >$grid->name</option>";
			}
			?>
		</select>
	</div>
	<div id="schedule-selection" tabindex="0" class="selection">
		<select id="schedules" name="schedules" required>
			<option value="my-schedule" selected><?php echo JText::_("COM_THM_ORGANIZER_MY_SCHEDULE"); ?></option>
		</select>
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
		<div id="category-input">
			<select id="category" name="category" required>
				<option value="placeholder" disabled
				        selected><?php echo JText::_("COM_THM_ORGANIZER_SCHEDULER_SELECT_OPTION"); ?></option>
				<option value="program"><?php echo JText::_("COM_THM_ORGANIZER_PROGRAMS"); ?></option>
				<option value="roomtype"><?php echo JText::_("COM_THM_ORGANIZER_ROOM_PLANS"); ?></option>
				<option value="teacher"><?php echo JText::_("COM_THM_ORGANIZER_TEACHERPLAN"); ?></option>
			</select>
		</div>
		<div id="program-input" class="input-wrapper">
			<select id="program" name="program" multiple
			        data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_SCHEDULER_SELECT_OPTION"); ?>">
				<!-- filled by ajax -->
			</select>
		</div>
		<div id="pool-input" class="input-wrapper">
			<select id="pool" name="pool" multiple
			        data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_SCHEDULER_SELECT_OPTION"); ?>">
				<!-- filled by ajax -->
			</select>
		</div>
		<div id="room-type-input" class="input-wrapper">
			<select id="roomtype" name="room-type"
			        data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_SCHEDULER_SELECT_OPTION"); ?>">
				<!-- filled by ajax -->
			</select>
		</div>
		<div id="room-input" class="input-wrapper">
			<select id="room" name="room" multiple
			        data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_SCHEDULER_SELECT_OPTION"); ?>">
				<!-- filled by ajax -->
			</select>
		</div>
		<div id="teacher-input" class="input-wrapper">
			<select id="teacher" name="teacher"
			        data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_SCHEDULER_SELECT_OPTION"); ?>">
				<!-- filled by ajax -->
			</select>
		</div>
	</div>

	<div class="date-input">
		<button id="previous-month" class="controls" type="button">
			<span class="icon-arrow-left-22"></span>
		</button>
		<button id="previous-day" class="controls" type="button">
			<span class="icon-arrow-left"></span>
		</button>
		<form>
			<span id="weekday"><!-- filled by setUpCalender --></span>
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
			<span class="icon-arrow-right-22"></span>
		</button>
	</div>

	<?php
	$daysOfTheWeek = array(
		JText::_('Monday'), JText::_('Tuesday'), JText::_('Wednesday'), JText::_('Thursday'),
		JText::_('Friday'), JText::_('Saturday'), JText::_('Sunday')
	);
	$periods       = get_object_vars($this->defaultGrid->periods);
	?>

	<div id="scheduleWrapper" class="scheduleWrapper">
		<input class="scheduler-input" checked type="radio" id="my-schedule" name="schedules">
		<div id="my-schedule" class="scheduler">
			<table>
				<thead>
				<tr>
					<th><?php echo JText::_('COM_THM_ORGANIZER_TIME'); ?></th>

					<?php
					for ($weekday = $this->defaultGrid->startDay - 1; $weekday < $this->defaultGrid->endDay; ++$weekday)
					{
						echo '<th>' . $daysOfTheWeek[$weekday] . '</th>';
					}
					?>

				</tr>
				</thead>
				<tbody>

				<?php
				for ($period = 1; $period <= count($periods); ++$period)
				{
					if ($period == 4)
					{
						echo "<tr>";
						echo '<td class="break" colspan="7">' . JText::_('COM_THM_ORGANIZER_LUNCHTIME') . '</td>';
						echo "</tr>";
					}

					echo "<tr>";
					echo "<td>";
					echo THM_OrganizerHelperComponent::formatTime($periods[$period]->startTime);
					echo "<br> - <br>";
					echo THM_OrganizerHelperComponent::formatTime($periods[$period]->endTime);
					echo "</td>";
					foreach ($this->mySchedule->days as $day)
					{
						echo "<td>";
						foreach ($day->$period as $lesson)
						{
							echo '<div class="lesson">';
							if (isset($lesson->time))
							{
								echo '<span class="own-time">' .
									THM_OrganizerHelperComponent::formatTime($lesson->time) .
									'</span> ';
							}

							if (isset($lesson->name))
							{
								echo '<span class="name">' . $lesson->name . '</span> ';
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
								echo '<span class="locations">';
								/* TODO: <span class="old"></span> */
								echo '<span class="new">';
								echo '<a href="#">' . $lesson->room . '</a>';
								echo "</span>";
								echo "</span>";
							}

							echo '<button class="add-lesson"><span class="icon-plus-2"></span></button>';
							echo "</div>";
						} // Lessons
						echo "</td>";
					} // Days
					echo "</tr>";
				} // Periods
				?>
				</tbody>
			</table>
		</div>
	</div>
</div>
