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
</script>
<div class="organizer <?php echo $noMobile; ?>">
	<input id="time-menu-item" type="checkbox" name="schedule-menu" role="menubar">
	<input id="schedule-selection-menu-item" type="checkbox" name="schedule-menu" role="menubar">
	<input id="export-menu-item" type="checkbox" name="schedule-menu" role="menubar">
	<input id="schedule-form-menu-item" type="checkbox" name="schedule-menu" role="menubar">

	<h1>THM Organizer <?php echo $this->getModel()->departmentName; ?></h1> <!--TODO: find a long mark header -->
	<div class="menu-bar">
		<label for="schedule-form-menu-item"><span class="icon-menu-3"></span></label>
		<label for="time-menu-item"><span class="time-grid-icon"></span></label>
		<label for="schedule-selection-menu-item"><span class="icon-calendar"></span></label>
		<button type="button"><span class="save-icon"></span></button>
		<label for="export-menu-item"><span class="icon-download"></span></label>
	</div>

	<!-- Menu -->
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
	<div id="time-selection" tabindex="0" class="selection">
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
	<div id="schedule-selection" tabindex="0" class="selection">
		<select id="schedules" name="schedules" required>
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
				} // Periods
				?>
				</tbody>
			</table>
		</div>
		<?php 
		} // guest table
		?>
	</div>
</div>
