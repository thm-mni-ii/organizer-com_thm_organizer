<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use Organizer\Helpers\Dates;
use Organizer\Helpers\Departments;
use Organizer\Helpers\Languages;

$activeDay      = date('w');
$categoryPH     = Languages::_('ORGANIZER_SELECT_CATEGORY');
$datesOfTheWeek = [
	Dates::formatDate('monday this week'),
	Dates::formatDate('tuesday this week'),
	Dates::formatDate('wednesday this week'),
	Dates::formatDate('thursday this week'),
	Dates::formatDate('friday this week'),
	Dates::formatDate('saturday this week'),
	Dates::formatDate('sunday this week')
];
$daysOfTheWeek  = [
	Languages::_('MON'),
	Languages::_('TUE'),
	Languages::_('WED'),
	Languages::_('THU'),
	Languages::_('FRI'),
	Languages::_('SAT'),
	Languages::_('SUN')
];
$departmentPH   = Languages::_('ORGANIZER_SELECT_DEPARTMENT');
$displayName    = empty($this->model->displayName) ?
	'THM Organizer  - ' . Languages::_('ORGANIZER_SCHEDULES') : $this->model->displayName;
$grid           = json_decode($this->params['defaultGrid'], true);
$gridOptions    = '';
foreach ($this->grids as $gridOption)
{
	$selected    = $gridOption['defaultGrid'] ? 'selected' : '';
	$gridOptions .= "<option value=\"{$gridOption['id']}\" $selected>{$gridOption['name']}</option>";
}
$groupPH     = Languages::_('ORGANIZER_SELECT_GROUP');
$mobile      = $this->isMobile ? 'mobile' : '';
$periods     = $grid['periods'];
$personPH    = Languages::_('ORGANIZER_SELECT_PERSON');
$roomPH      = Languages::_('ORGANIZER_SELECT_ROOM');
$roomTypePH  = Languages::_('ORGANIZER_SELECT_ROOMTYPE');
$typeOptions = '';
if ($this->params['showCategories'])
{
	$typeOptions .= '<option value="category" selected>' . Languages::_('ORGANIZER_EVENT_PLANS') . '</option>';
}
if ($this->params['showRooms'])
{
	$typeOptions .= '<option value="roomtype">' . Languages::_('ORGANIZER_ROOM_PLANS') . '</option>';
}

if ($this->params['showPersons'])
{
	$typeOptions .= '<option value="person">' . Languages::_('ORGANIZER_PERSON_PLANS') . '</option>';
}

$typePH = Languages::_('ORGANIZER_SELECT_PLAN_TYPE');
?>
<div class="organizer <?php echo $mobile; ?>">
    <div class="page-header">
        <h2><?php echo $displayName; ?></h2>
    </div>
    <div class="menu-bar">
        <ul class="tabs" role="tablist">
            <li class="tabs-tab active" role="presentation">
                <a href="#schedule-form" class="tabs-toggle" id="tab-schedule-form"
                   data-toggle="tab" data-id="schedule-form" role="tab" aria-controls="schedule-form"
                   aria-selected="true">
                    <span class="icon-calendars"></span>
                    <span class="tab-text"><?php echo Languages::_('ORGANIZER_SCHEDULES'); ?></span>
                </a>
            </li>
            <li class="tabs-tab" role="presentation">
                <a href="#selected-schedules" class="tabs-toggle" id="tab-selected-schedules"
                   data-toggle="tab" data-id="selected-schedules" role="tab" aria-controls="selected-schedules"
                   aria-selected="true">
                    <span class="icon-checkbox-checked"></span>
                    <span class="tab-text"><?php echo Languages::_('ORGANIZER_SELECTED'); ?></span>
                </a>
            </li>
            <li class="tabs-tab" role="presentation">
                <a href="#time-selection" class="tabs-toggle" id="tab-time-selection"
                   data-toggle="tab" data-id="time-selection" role="tab" aria-controls="time-selection"
                   aria-selected="true">
                    <span class="icon-grid-2"></span>
                    <span class="tab-text"><?php echo Languages::_('ORGANIZER_GRIDS'); ?></span>
                </a>
            </li>
            <li class="date-input">
                <button id="previous-month" class="controls" type="button"
                        onclick="scheduleApp.getCalendar().changeSelectedDate(false, 'month');">
                    <span class="icon-arrow-left-22"></span>
                </button>
                <button id="previous-week" class="controls" type="button"
                        onclick="scheduleApp.getCalendar().changeSelectedDate(false, 'week');">
                    <span class="icon-arrow-left"></span>
                </button>
                <input id="date" type="text" required onchange="scheduleApp.updateSchedule('date');"/>
                <button id="calendar-icon" type="button" class="controls"
                        onclick="scheduleApp.getCalendar().showCalendar();">
                    <span class="icon-calendar"></span>
                </button>
                <div id="calendar">
                    <table id="calendar-table">
                        <thead>
                        <tr>
                            <td colspan="1">
                                <button id="calendar-previous-month" type="button"
                                        onclick="scheduleApp.getCalendar().changeCalendarMonth(false);">
                                    <span class="icon-arrow-left"></span>
                                </button>
                            </td>
                            <td colspan="5">
                                <span id="display-month"></span> <span id="display-year"></span>
                            </td>
                            <td colspan="1">
                                <button id="calendar-next-month" type="button"
                                        onclick="scheduleApp.getCalendar().changeCalendarMonth(true);">
                                    <span class="icon-arrow-right"></span>
                                </button>
                            </td>
                        </tr>
                        </thead>
                        <thead>
                        <tr>
                            <td><?php echo Languages::_('MON'); ?></td>
                            <td><?php echo Languages::_('TUE'); ?></td>
                            <td><?php echo Languages::_('WED'); ?></td>
                            <td><?php echo Languages::_('THU'); ?></td>
                            <td><?php echo Languages::_('FRI'); ?></td>
                            <td><?php echo Languages::_('SAT'); ?></td>
                            <td><?php echo Languages::_('SUN'); ?></td>
                        </tr>
                        </thead>
                        <tbody>
                        <!-- generated code with JavaScript -->
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="7">
                                <button id="today" type="button" class="today"
                                        onclick="scheduleApp.getCalendar().changeSelectedDate(true, 'week');">
									<?php echo Languages::_('ORGANIZER_TODAY'); ?>
                                </button>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <button id="next-week" class="controls" type="button"
                        onclick="scheduleApp.getCalendar().changeSelectedDate(true, 'week');">
                    <span class="icon-arrow-right"></span>
                </button>
                <button id="next-month" class="controls" type="button"
                        onclick="scheduleApp.getCalendar().changeSelectedDate(true, 'month');">
                    <span class="icon-arrow-right-22"></span>
                </button>
            </li>
            <li class="tabs-tab" role="presentation">
                <a href="#exports" class="tabs-toggle" id="tab-exports" data-toggle="tab"
                   data-id="exports" role="tab" aria-controls="exports" aria-selected="true">
                    <span class="icon-download"></span>
                    <span class="tab-text"><?php echo Languages::_('ORGANIZER_FILE_FORMAT'); ?></span>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-panel selection active" id="schedule-form" role="tabpanel"
                 aria-labelledby="tab-schedule-form" aria-hidden="false">
                <div id="department-input" class="input-wrapper">
                    <select id="department" data-input="static" data-placeholder="<?php echo $departmentPH; ?>">
						<?php foreach (Departments::getOptions() as $department) : ?>
                            <option value="<?php echo $department->value; ?>">
								<?php echo $department->text; ?>
                            </option>
						<?php endforeach; ?>
                    </select>
                </div>
                <div id="type-input" class="input-wrapper">
                    <select id="type" required data-input="static" data-placeholder="<?php echo $typePH; ?>">
						<?php echo $typeOptions ?>
                    </select>
                </div>
                <div id="category-input" class="input-wrapper">
                    <select id="category" data-next="group" data-placeholder="<?php echo $categoryPH; ?>"></select>
                </div>
                <div id="group-input" class="input-wrapper">
                    <select id="group" data-next="event" data-placeholder="<?php echo $groupPH; ?>"></select>
                </div>
                <div id="roomtype-input" class="input-wrapper">
                    <select id="roomtype" data-next="room" data-placeholder="<?php echo $roomTypePH; ?>"></select>
                </div>
                <div id="room-input" class="input-wrapper">
                    <select id="room" data-next="event" data-placeholder="<?php echo $roomPH; ?>"></select>
                </div>
                <div id="person-input" class="input-wrapper">
                    <select id="person" data-next="event" data-placeholder="<?php echo $personPH; ?>"></select>
                </div>
            </div>
            <div class="tab-panel" id="selected-schedules" role="tabpanel"
                 aria-labelledby="tab-selected-schedules" aria-hidden="false">
            </div>
            <div class="tab-panel" id="time-selection" role="tabpanel" aria-labelledby="tab-time" aria-hidden="false">
                <select id="grid" required onchange="scheduleApp.changeGrid();">
					<?php echo $gridOptions; ?>
                </select>
            </div>

            <div class="tab-panel" id="exports" role="tabpanel" aria-labelledby="tab-exports-menu" aria-hidden="false">
                <div class="link-item">
                    <a onclick="scheduleApp.handleExport('pdf.a4');">
                        <span class="icon-file-pdf"></span>
						<?php echo Languages::_('ORGANIZER_PDF_DOCUMENT'); ?>
                    </a>
                </div>
                <div class="link-item">
                    <a onclick="scheduleApp.handleExport('xls.si');">
                        <span class="icon-file-excel"></span>
						<?php echo Languages::_('ORGANIZER_XLS_SPREADSHEET'); ?>
                    </a>
                </div>
                <div class="link-item">
                    <a onclick="scheduleApp.handleExport('ics');">
                        <span class="icon-info-calender"></span>
						<?php echo Languages::_('ORGANIZER_ICS_CALENDAR'); ?>
                    </a>
                </div>
                <div class="link-item">
                    <a href="?option=com_thm_organizer&view=schedule_export" target="_blank">
                        <span class="icon-plus"></span>
						<?php echo Languages::_('ORGANIZER_OTHER_EXPORT_OPTIONS'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div id="scheduleWrapper" class="scheduleWrapper">
        <input id="default-input" class="schedule-input" checked="checked" type="radio" name="schedules">
        <div id="default-schedule" class="schedule-table">
            <table>
                <thead>
                <tr>
                    <th><?php echo Languages::_('ORGANIZER_TIME'); ?></th>
					<?php for ($weekday = $grid['startDay'] - 1; $weekday < $grid['endDay']; ++$weekday) : ?>
                        <th <?php echo ($activeDay == $weekday + 1) ? 'class="activeColumn"' : ''; ?>>
							<?php echo $daysOfTheWeek[$weekday]; ?>
                        </th>
					<?php endfor; ?>
                </tr>
                </thead>
                <tbody>
				<?php for ($period = 1; $period <= count($periods); ++$period) : ?>
                    <tr>
                        <td>
							<?php echo Dates::formatTime($periods[$period]['startTime']); ?>
                            <br> - <br>
							<?php echo Dates::formatTime($periods[$period]['endTime']); ?>
                        </td>
						<?php for ($weekday = $grid['startDay'] - 1; $weekday < $grid['endDay']; ++$weekday) : ?>
                            <td <?php echo ($activeDay == $weekday + 1) ? ' class="activeColumn"' : ''; ?>></td>
						<?php endfor; ?>
                    </tr>
				<?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="instance-menu">
        <button class="icon-cancel" onclick="this.parentElement.style.display='none';"></button>
        <div class="event-data">
            <div class="subjectNameNr">
                <span class="subject"></span>
                <span class="module"></span>
            </div>
            <div class="persons"></div>
            <div class="rooms"></div>
            <div class="groups"></div>
            <span class="description"></span>
        </div>
        <div class="save">
            <button id="save-mode-semester">
				<?php echo Languages::_('ORGANIZER_SAVE_EVENT_SEMESTER') ?>
            </button>
            <button id="save-mode-period">
				<?php echo Languages::_('ORGANIZER_SAVE_EVENT_PERIOD') ?>
            </button>
            <button id="save-mode-instance">
				<?php echo Languages::_('ORGANIZER_SAVE_EVENT_INSTANCE') ?>
            </button>
        </div>
        <div class="delete">
            <button id="delete-mode-semester">
				<?php echo Languages::_('ORGANIZER_DELETE_EVENT_SEMESTER') ?>
            </button>
            <button id="delete-mode-period">
				<?php echo Languages::_('ORGANIZER_DELETE_EVENT_PERIOD') ?>
            </button>
            <button id="delete-mode-instance">
				<?php echo Languages::_('ORGANIZER_DELETE_EVENT_INSTANCE') ?>
            </button>
        </div>
    </div>

    <div id="next-date-selection" class="message pop-up">
        <p><?php echo Languages::_('ORGANIZER_JUMP_DATE'); ?></p>
        <button class="icon-cancel" onclick="this.parentElement.style.display='none';"></button>
        <button id="past-date" onclick="scheduleApp.nextDateEventHandler(event);">
            <span class="icon-arrow-left-2"></span>
			<?php echo sprintf(Languages::_('ORGANIZER_JUMP_TO_DATE'), date("d.m.Y")); ?>
        </button>
        <button id="future-date" onclick="scheduleApp.nextDateEventHandler(event);">
            <span class="icon-arrow-right-2"></span>
			<?php echo sprintf(Languages::_('ORGANIZER_JUMP_TO_DATE'), date("d.m.Y")); ?>
        </button>
    </div>

    <div id="no-events" class="message pop-up">
        <p>
            <span class="icon-notification"></span>
            <span><?php echo Languages::_('ORGANIZER_NO_EVENTS_PLANNED'); ?></span>
        </p>
        <button class="icon-cancel" onclick="this.parentElement.style.display='none';"></button>
    </div>

    <div id="reg-fifo" class="message pop-up">
        <p>
            <span class="icon-notification"></span>
            <span><?php echo Languages::_('ORGANIZER_COURSE_MAIL_STATUS_REGISTERED'); ?></span>
        </p>
        <button class="icon-cancel" onclick="this.parentElement.style.display='none';"></button>
    </div>

    <div id="reg-manual" class="message pop-up">
        <p>
            <span class="icon-notification"></span>
            <span><?php echo Languages::_('ORGANIZER_COURSE_MAIL_STATUS_WAIT_LIST'); ?></span>
        </p>
        <button class="icon-cancel" onclick="this.parentElement.style.display='none';"></button>
    </div>
</div>
