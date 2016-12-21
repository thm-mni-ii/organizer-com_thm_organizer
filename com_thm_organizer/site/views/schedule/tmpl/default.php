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
$noMobile = !$this->isMobile ? 'no-mobile' : '';
?>

<script type="text/javascript" charset="utf-8">
	<?php require_once "components/com_thm_organizer/views/schedule/tmpl/js/text.js"; ?>
	<?php require_once "components/com_thm_organizer/views/schedule/tmpl/js/variables.js"; ?>
</script>
<div class="organizer <?php echo $noMobile; ?>">

	<h1>THM Organizer <?php echo $this->getModel()->departmentName; ?></h1> <!--TODO: find a long mark header -->

	<div class="menu-bar">
		<!--	<a id="rl_tabs-scrollto_0" class="anchor rl_tabs-scroll nn_tabs-scroll"></a> -->
		<ul class="tabs" role="tablist">
			<li class="tabs-tab active" role="presentation">
				<a href="#schedule-form" class="tabs-toggle" id="tab-schedule-form"
				   data-toggle="tab" data-id="schedule-form" role="tab" aria-controls="schedule-form"
				   aria-selected="true">
					<span class="icon-calendar"></span>
				</a>
			</li>
			<li class="tabs-tab" role="presentation">
				<a href="#selected-schedules" class="tabs-toggle" id="tab-selected-schedules"
				   data-toggle="tab" data-id="selected-schedules" role="tab" aria-controls="selected-schedules"
				   aria-selected="true">
					<span class="icon-calendar"></span>
				</a>
			</li>
			<li class="tabs-tab" role="presentation">
				<a href="#time-selection" class="tabs-toggle" id="tab-time-selection"
				   data-toggle="tab" data-id="time-selection" role="tab" aria-controls="time-selection"
				   aria-selected="true">
					<span class="icon-time-grid"></span>
				</a>
			</li>
			<li class="date-input-list-item">
				<div class="date-input">
					<button id="previous-month" class="controls" type="button">
						<span class="icon-arrow-left-22"></span>
					</button>
					<button id="previous-day" class="controls" type="button">
						<span class="icon-arrow-left"></span>
					</button>
					<form>
						<input id="date" type="date" name="date" required/>
						<button id="calendar-icon" type="button" class="controls">
							<span class="icon-calendar"></span>
						</button>
						<div id="choose-date">
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
					</form>
					<button id="next-day" class="controls" type="button">
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
				</a>
			</li>
		</ul>

		<!-- Menu -->
		<div class="tab-content">

			<div class="tab-panel selection active" id="schedule-form" role="tabpanel"
			     aria-labelledby="tab-schedule-form" aria-hidden="false">
				<div id="category-input">
					<select id="category" name="category" required>
						<option value="placeholder" disabled
						        selected><?php echo JText::_("COM_THM_ORGANIZER_SELECT_CATEGORY"); ?></option>
						<option value="program"><?php echo JText::_("COM_THM_ORGANIZER_PROGRAMS"); ?></option>
						<option value="roomtype"><?php echo JText::_("COM_THM_ORGANIZER_ROOM_PLANS"); ?></option>
						<option value="teacher"><?php echo JText::_("COM_THM_ORGANIZER_TEACHERPLAN"); ?></option>
					</select>
				</div>
				<div id="program-input" class="input-wrapper">
					<select id="program" name="program" multiple
					        data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_PROGRAM_SELECT_PLACEHOLDER"); ?>">
						<!-- filled by ajax -->
					</select>
				</div>
				<div id="pool-input" class="input-wrapper">
					<select id="pool" name="pool" multiple
					        data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_POOL_SELECT_PLACEHOLDER"); ?>">
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
					        data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_ROOM_SELECT_PLACEHOLDER"); ?>">
						<!-- filled by ajax -->
					</select>
				</div>
				<div id="teacher-input" class="input-wrapper">
					<select id="teacher" name="teacher"
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
				<select id="grid" name="grid" required>
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
				<select id="export-selection" name="export" required>
					<option value="pdf"><?php echo JText::_('COM_THM_ORGANIZER_PDF_SCHEDULE') ?></option>
					<option value="excel"><?php echo JText::_('COM_THM_ORGANIZER_ACTION_EXPORT_EXCEL') ?></option>
					<option value="iCal"><?php echo JText::_('COM_THM_ORGANIZER_SCHEDULER_ICS') ?></option>
				</select>
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
	?>

	<div id="scheduleWrapper" class="scheduleWrapper">
		<?php
		if (JFactory::getUser()->guest)
		{
			?>
			<input id="guest-schedule" class="scheduler-input" checked type="radio" name="schedules">
			<div id="guest-schedule" class="scheduler">
				<table>
					<thead>
					<tr>
						<th><?php echo JText::_('COM_THM_ORGANIZER_TIME'); ?></th>
						<?php
						for ($weekday = $grid->startDay - 1; $weekday < $grid->endDay; ++$weekday)
						{
							echo "<th>$daysOfTheWeek[$weekday]</th>";
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
						echo "</tr>";

						if ($period == 1 OR $period == 2 OR $period == 4 OR $period == 5)
						{
							echo "<tr>";
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
</div>
