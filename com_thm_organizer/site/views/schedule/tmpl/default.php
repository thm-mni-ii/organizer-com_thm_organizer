<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        default .php
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
$mobile = $this->isMobile ? 'mobile' : '';
?>

<script type="text/javascript" charset="utf-8">
	<?php require_once "components/com_thm_organizer/views/schedule/tmpl/js/text.js"; ?>
	<?php require_once "components/com_thm_organizer/views/schedule/tmpl/js/variables.js"; ?>
</script>
<div class="organizer <?php echo $mobile; ?>">

	<h1>THM Organizer <?php echo $this->getModel()->departmentName; ?></h1> <!--TODO: find a long mark header -->

	<div class="menu-bar">
		<!--	<a id="rl_tabs-scrollto_0" class="anchor rl_tabs-scroll nn_tabs-scroll"></a> -->
		<ul class="tabs" role="tablist">
			<li class="tabs-tab active" role="presentation">
				<a href="#schedule-form" class="tabs-toggle" id="tab-schedule-form"
				   data-toggle="tab" data-id="schedule-form" role="tab" aria-controls="schedule-form"
				   aria-selected="true">
					<span class="icon-schedules"></span>
					<span class="tab-text"><?php echo JText::_("COM_THM_ORGANIZER_SCHEDULER_OPTIONS_SELECTION_TITLE"); ?></span>
				</a>
			</li>
			<li class="tabs-tab" role="presentation">
				<a href="#selected-schedules" class="tabs-toggle" id="tab-selected-schedules"
				   data-toggle="tab" data-id="selected-schedules" role="tab" aria-controls="selected-schedules"
				   aria-selected="true">
					<span class="icon-checkin"></span>
					<span class="tab-text"><?php echo JText::_("COM_THM_ORGANIZER_SELECTED"); ?></span>
				</a>
			</li>
			<li class="tabs-tab" role="presentation">
				<a href="#time-selection" class="tabs-toggle" id="tab-time-selection"
				   data-toggle="tab" data-id="time-selection" role="tab" aria-controls="time-selection"
				   aria-selected="true">
					<span class="icon-grid-2"></span>
					<span class="tab-text"><?php echo JText::_("COM_THM_ORGANIZER_GRID_MANAGER_TITLE"); ?></span>
				</a>
			</li>
			<li class="date-input-list-item">
				<div class="date-input">
					<button id="previous-month" class="controls" type="button">
						<span class="icon-arrow-left-22"></span>
					</button>
					<button id="previous-week" class="controls" type="button">
						<span class="icon-arrow-left"></span>
					</button>
					<input id="date" type="text" required/>
					<button id="calendar-icon" type="button" class="controls">
						<span class="icon-calendar"></span>
					</button>
					<div id="calendar">
						<table id="calendar-table">
							<thead>
							<tr>
								<td colspan="1">
									<button id="calendar-previous-month" type="button">
										<span class="icon-arrow-left"></span>
									</button>
								</td>
								<td colspan="5">
									<span id="display-month"></span> <span id="display-year"></span>
								</td>
								<td colspan="1">
									<button id="calendar-next-month" type="button">
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
									<button id="today" type="button" class="today">
										<?php echo JText::_("COM_THM_ORGANIZER_TODAY"); ?>
									</button>
								</td>
							</tr>
							</tfoot>
						</table>
					</div>
					<button id="next-week" class="controls" type="button">
						<span class="icon-arrow-right"></span>
					</button>
					<button id="next-month" class="controls" type="button">
						<span class="icon-arrow-right-22"></span>
					</button>
				</div>
			</li>
			<li class="tabs-tab" role="presentation">
				<a href="#exports" class="tabs-toggle" id="tab-exports" data-toggle="tab"
				   data-id="exports" role="tab" aria-controls="exports" aria-selected="true">
					<span class="icon-download"></span>
					<span class="tab-text"><?php echo JText::_("COM_THM_ORGANIZER_FILE_FORMAT"); ?></span>
				</a>
			</li>
		</ul>

		<!-- Menu -->
		<div class="tab-content">

			<div class="tab-panel selection active" id="schedule-form" role="tabpanel"
			     aria-labelledby="tab-schedule-form" aria-hidden="false">
				<div id="category-input">
					<select id="category" required>
						<option value="placeholder" disabled>
							<?php echo JText::_("COM_THM_ORGANIZER_SELECT_CATEGORY"); ?>
						</option>
						<option value="program" selected><?php echo JText::_("COM_THM_ORGANIZER_PROGRAMS"); ?></option>
						<option value="roomtype"><?php echo JText::_("COM_THM_ORGANIZER_ROOM_PLANS"); ?></option>
						<option value="teacher"><?php echo JText::_("COM_THM_ORGANIZER_TEACHERPLAN"); ?></option>
					</select>
				</div>
				<?php
				if ($this->departmentID == 0)
				{
					?>
					<div id="department-input" class="input-wrapper">
						<select id="department" multiple
						        data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_DEPARTMENT_SELECT_PLACEHOLDER"); ?>">
							<?php
							foreach ($this->getModel()->departments as $id => $department)
							{
								echo "<option value='" . $id . "'>$department</option>";
							}
							?>
						</select>
					</div>
				<?php } ?>
				<div id="program-input" class="input-wrapper ">
					<select id="program">
						<!-- filled by ajax -->
					</select>
				</div>
				<div id="pool-input" class="input-wrapper hide">
					<select id="pool">
						<!-- filled by ajax -->
					</select>
				</div>
				<div id="room-type-input" class="input-wrapper hide">
					<select id="roomtype">
						<!-- filled by ajax -->
					</select>
				</div>
				<div id="room-input" class="input-wrapper hide">
					<select id="room">
						<!-- filled by ajax -->
					</select>
				</div>
				<div id="teacher-input" class="input-wrapper hide">
					<select id="teacher"
					        data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_TEACHER_SELECT_PLACEHOLDER"); ?>">
						<!-- filled by ajax -->
					</select>
				</div>
			</div>

			<div class="tab-panel" id="selected-schedules" role="tabpanel"
			     aria-labelledby="tab-selected-schedules" aria-hidden="false">
				<select id="schedules" name="schedules" required
				        data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_SCHEDULER_SELECT_OPTION"); ?>"></select>
			</div>

			<div class="tab-panel" id="time-selection" role="tabpanel"
			     aria-labelledby="tab-time" aria-hidden="false">
				<select id="grid" required>
					<?php
					foreach ($this->getModel()->grids as $grid)
					{
						$checked = ($grid->name == $this->defaultGrid->name) ? 'checked' : '';
						echo "<option value='" . $grid->grid . "' $checked >$grid->name</option>";
					}
					?>
				</select>
			</div>

			<div class="tab-panel" id="exports"
			     role="tabpanel" aria-labelledby="tab-exports-menu" aria-hidden="false">
				<div class="link-item">
					<a onclick="handleExport('pdf.a4');">
						<span class="icon-file-pdf"></span>
						<?php echo JText::_('COM_THM_ORGANIZER_PDF_DOCUMENT'); ?>
					</a>
				</div>
				<div class="link-item">
					<a onclick="handleExport('xls.si');">
						<span class="icon-file-xls"></span>
						<?php echo JText::_('COM_THM_ORGANIZER_XLS_SPREADSHEET'); ?>
					</a>
				</div>
				<div class="link-item">
					<a onclick="handleExport('ics');">
						<span class="icon-info-calender"></span>
						<?php echo JText::_('COM_THM_ORGANIZER_ICS_CALENDAR'); ?>
					</a>
				</div>
				<div class="link-item">
					<a href="index.php?option=com_thm_organizer&view=schedule_export" target="_blank">
						<span class="icon-plus"></span>
						<?php echo JText::_('COM_THM_ORGANIZER_OTHER_EXPORT_OPTIONS'); ?>
					</a>
				</div>
			</div>
		</div>
	</div>

	<?php
	$daysOfTheWeek  = array(
		JText::_('MON'), JText::_('TUE'), JText::_('WED'), JText::_('THU'),
		JText::_('FRI'), JText::_('SAT'), JText::_('SUN')
	);
	$datesOfTheWeek = array(
		THM_OrganizerHelperComponent::formatDate('monday this week'),
		THM_OrganizerHelperComponent::formatDate('tuesday this week'),
		THM_OrganizerHelperComponent::formatDate('wednesday this week'),
		THM_OrganizerHelperComponent::formatDate('thursday this week'),
		THM_OrganizerHelperComponent::formatDate('friday this week'),
		THM_OrganizerHelperComponent::formatDate('saturday this week'),
		THM_OrganizerHelperComponent::formatDate('sunday this week')
	);
	$grid           = json_decode($this->defaultGrid->grid);
	$periods        = get_object_vars($grid->periods);
	$activeDay      = date("w");
	?>

	<div id="scheduleWrapper" class="scheduleWrapper">
		<?php
		if (JFactory::getUser()->guest)
		{
			?>
			<input id="default-schedule" class="schedule-input" checked type="radio" name="schedules">
			<div id="default-schedule" class="schedule-table">
				<table>
					<thead>
					<tr>
						<th><?php echo JText::_('COM_THM_ORGANIZER_TIME'); ?></th>
						<?php
						for ($weekday = $grid->startDay - 1; $weekday < $grid->endDay; ++$weekday)
						{
							if ($activeDay == $weekday + 1)
							{
								echo "<th class='activeColumn'>$daysOfTheWeek[$weekday]</th>";
							}
							else
							{
								echo "<th>$daysOfTheWeek[$weekday]</th>";
							}
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
							echo '<tr class="break-row">';
							echo '<td class="break" colspan="7">' . JText::_('COM_THM_ORGANIZER_LUNCHTIME') . '</td>';
							echo "</tr>";
						}

						echo "<tr>";
						echo "<td>";
						echo THM_OrganizerHelperComponent::formatTime($periods[$period]->startTime);
						echo "<br> - <br>";
						echo THM_OrganizerHelperComponent::formatTime($periods[$period]->endTime);
						echo "</td>";
						echo '<td class="activeColumn"></td>';
						echo "</tr>";

						if ($period == 1 OR $period == 2 OR $period == 4 OR $period == 5)
						{
							echo '<tr class="break-row">';
							echo '<td class="break" colspan="7"></td>';
							echo "</tr>";
						}
					} // Periods
					?>
					</tbody>
				</table>
			</div>
			<?php
		} // Guest table
		?>
	</div>

	<div class="lesson-menu">
		<div class="save">
			<button class="icon-cancel close-save"></button>
			<button id="save-mode-semester">
				<?php echo JText::_('COM_THM_ORGANIZER_SAVE_LESSON_SEMESTER') ?>
			</button>
			<button id="save-mode-period">
				<?php echo JText::_('COM_THM_ORGANIZER_SAVE_LESSON_PERIOD') ?>
			</button>
			<button id="save-mode-instance">
				<?php echo JText::_('COM_THM_ORGANIZER_SAVE_LESSON_INSTANCE') ?>
			</button>
		</div>
		<div class="delete">
			<button class="icon-cancel close-delete"></button>
			<button id="delete-mode-semester">
				<?php echo JText::_('COM_THM_ORGANIZER_DELETE_LESSON_SEMESTER') ?>
			</button>
			<button id="delete-mode-period">
				<?php echo JText::_('COM_THM_ORGANIZER_DELETE_LESSON_PERIOD') ?>
			</button>
			<button id="delete-mode-instance">
				<?php echo JText::_('COM_THM_ORGANIZER_DELETE_LESSON_INSTANCE') ?>
			</button>
		</div>
	</div>

	<div id="next-date-selection" class="message-pop-up">
		<p><?php echo JText::_("COM_THM_ORGANIZER_JUMP_DATE"); ?></p>
		<button class="close icon-cancel"></button>
		<button id="past-date">
			<?php echo JText::sprintf("COM_THM_ORGANIZER_JUMP_TO_DATE", date("d.m.Y")); ?>
		</button>
		<button id="future-date">
			<?php echo JText::sprintf("COM_THM_ORGANIZER_JUMP_TO_DATE", date("d.m.Y")); ?>
		</button>
	</div>

	<div id="no-lessons" class="message-pop-up">
		<p><?php echo JText::_("COM_THM_ORGANIZER_NO_LESSONS"); ?></p>
		<button class="close icon-cancel"></button>
	</div>
</div>
