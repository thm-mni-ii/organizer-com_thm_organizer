<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use Joomla\CMS\Factory;
use Organizer\Helpers\Dates;
use Organizer\Helpers\Languages;

$mobile      = $this->isMobile ? 'mobile' : '';
$displayName = empty($this->model->displayName) ?
    'THM Organizer  - ' . Languages::_('THM_ORGANIZER_SCHEDULES')
    : $this->model->displayName;
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
                    <span class="icon-schedules"></span>
                    <span class="tab-text"><?php echo Languages::_('THM_ORGANIZER_SCHEDULES'); ?></span>
                </a>
            </li>
            <li class="tabs-tab" role="presentation">
                <a href="#selected-schedules" class="tabs-toggle" id="tab-selected-schedules"
                   data-toggle="tab" data-id="selected-schedules" role="tab" aria-controls="selected-schedules"
                   aria-selected="true">
                    <span class="icon-checkbox-checked"></span>
                    <span class="tab-text"><?php echo Languages::_('THM_ORGANIZER_SELECTED'); ?></span>
                </a>
            </li>
            <li class="tabs-tab" role="presentation">
                <a href="#time-selection" class="tabs-toggle" id="tab-time-selection"
                   data-toggle="tab" data-id="time-selection" role="tab" aria-controls="time-selection"
                   aria-selected="true">
                    <span class="icon-grid-2"></span>
                    <span class="tab-text"><?php echo Languages::_('THM_ORGANIZER_GRIDS'); ?></span>
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
                                    <?php echo Languages::_('THM_ORGANIZER_TODAY'); ?>
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
            <li class="check-input">
                <input type="checkbox" id="check-notify-box"
                       onclick="scheduleApp.toggleCheckbox();" <?php echo $this->model->setCheckboxChecked(); ?>/>
                <span class="tab-text"
                      id="check-notify-text"><?php echo \JText::_('THM_ORGANIZER_CHECK_NOTIFY'); ?></span>
            </li>
            <li class="tabs-tab" role="presentation">
                <a href="#exports" class="tabs-toggle" id="tab-exports" data-toggle="tab"
                   data-id="exports" role="tab" aria-controls="exports" aria-selected="true">
                    <span class="icon-download"></span>
                    <span class="tab-text"><?php echo Languages::_('THM_ORGANIZER_FILE_FORMAT'); ?></span>
                </a>
            </li>
        </ul>

        <!-- Menu -->
        <div class="tab-content">

            <div class="tab-panel selection active" id="schedule-form" role="tabpanel"
                 aria-labelledby="tab-schedule-form" aria-hidden="false">
                <?php
                if (!empty($this->model->params['showDepartments'])) {
                    ?>
                    <div id="department-input" class="input-wrapper">
                        <select id="department" multiple data-input="static"
                                data-placeholder="<?php echo Languages::_('THM_ORGANIZER_SELECT_DEPARTMENT'); ?>">
                            <?php
                            foreach ($this->getModel()->departments as $id => $department) {
                                echo "<option value='" . $id . "'>$department</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <?php
                }
                ?>
                <div id="category-input" class="input-wrapper">
                    <select id="category" required data-input="static"
                            data-placeholder="<?php echo Languages::_('THM_ORGANIZER_SELECT_CATEGORY'); ?>">
                        <?php
                        if (!empty($this->model->params['showCategories'])) {
                            echo '<option value="category" selected>' . Languages::_('THM_ORGANIZER_EVENT_PLANS');
                            echo '</option>';
                        }

                        if (!empty($this->model->params['showRooms'])) {
                            echo '<option value="roomType">' . Languages::_('THM_ORGANIZER_ROOM_PLANS') . '</option>';
                        }

                        if (!empty($this->model->params['showTeachers'])) {
                            echo '<option value="teacher">' . Languages::_('THM_ORGANIZER_TEACHER_PLANS') . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div id="category-input" class="input-wrapper">
                    <select id="category" data-next="group"
                            data-placeholder="<?php echo Languages::_('THM_ORGANIZER_SELECT_CATEGORY'); ?>">
                        <!-- filled by ajax -->
                    </select>
                </div>
                <div id="group-input" class="input-wrapper">
                    <select id="group" data-next="lesson"
                            data-placeholder="<?php echo Languages::_('THM_ORGANIZER_SELECT_GROUP'); ?>">
                        <!-- filled by ajax -->
                    </select>
                </div>
                <div id="roomType-input" class="input-wrapper">
                    <select id="roomType" data-next="room"
                            data-placeholder="<?php echo Languages::_('THM_ORGANIZER_SELECT_ROOM_TYPE'); ?>">
                        <!-- filled by ajax -->
                    </select>
                </div>
                <div id="room-input" class="input-wrapper">
                    <select id="room" data-next="lesson"
                            data-placeholder="<?php echo Languages::_('THM_ORGANIZER_SELECT_ROOM'); ?>">
                        <!-- filled by ajax -->
                    </select>
                </div>
                <div id="teacher-input" class="input-wrapper">
                    <select id="teacher" data-next="lesson"
                            data-placeholder="<?php echo Languages::_('THM_ORGANIZER_SELECT_TEACHER'); ?>">
                        <!-- filled by ajax -->
                    </select>
                </div>
            </div>

            <div class="tab-panel" id="selected-schedules" role="tabpanel"
                 aria-labelledby="tab-selected-schedules" aria-hidden="false">
            </div>

            <div class="tab-panel" id="time-selection" role="tabpanel" aria-labelledby="tab-time" aria-hidden="false">
                <select id="grid" required onchange="scheduleApp.changeGrid();">
                    <?php
                    foreach ($this->getModel()->grids as $grid) {
                        $selected = ($grid->name == $this->defaultGrid->name) ? 'selected' : '';
                        echo "<option value='" . $grid->id . "' $selected >$grid->name</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="tab-panel" id="exports" role="tabpanel" aria-labelledby="tab-exports-menu" aria-hidden="false">
                <div class="link-item">
                    <a onclick="scheduleApp.handleExport('pdf.a4');">
                        <span class="icon-file-pdf"></span>
                        <?php echo Languages::_('THM_ORGANIZER_PDF_DOCUMENT'); ?>
                    </a>
                </div>
                <div class="link-item">
                    <a onclick="scheduleApp.handleExport('xls.si');">
                        <span class="icon-file-excel"></span>
                        <?php echo Languages::_('THM_ORGANIZER_XLS_SPREADSHEET'); ?>
                    </a>
                </div>
                <div class="link-item">
                    <a onclick="scheduleApp.handleExport('ics');">
                        <span class="icon-info-calender"></span>
                        <?php echo Languages::_('THM_ORGANIZER_ICS_CALENDAR'); ?>
                    </a>
                </div>
                <div class="link-item">
                    <a href="?option=com_thm_organizer&view=schedule_export" target="_blank">
                        <span class="icon-plus"></span>
                        <?php echo Languages::_('THM_ORGANIZER_OTHER_EXPORT_OPTIONS'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php
    $daysOfTheWeek  = [
        Languages::_('MON'),
        Languages::_('TUE'),
        Languages::_('WED'),
        Languages::_('THU'),
        Languages::_('FRI'),
        Languages::_('SAT'),
        Languages::_('SUN')
    ];
    $datesOfTheWeek = [
        Dates::formatDate('monday this week'),
        Dates::formatDate('tuesday this week'),
        Dates::formatDate('wednesday this week'),
        Dates::formatDate('thursday this week'),
        Dates::formatDate('friday this week'),
        Dates::formatDate('saturday this week'),
        Dates::formatDate('sunday this week')
    ];
    $grid           = json_decode($this->defaultGrid->grid);
    $periods        = get_object_vars($grid->periods);
    $activeDay      = date('w');
    ?>

    <div id="scheduleWrapper" class="scheduleWrapper">
        <?php
        if (Factory::getUser()->guest) {
            ?>
            <input id="default-input" class="schedule-input" checked="checked" type="radio" name="schedules">
            <div id="default-schedule" class="schedule-table">
                <table>
                    <thead>
                    <tr>
                        <th><?php echo Languages::_('THM_ORGANIZER_TIME'); ?></th>
                        <?php
                        for ($weekday = $grid->startDay - 1; $weekday < $grid->endDay; ++$weekday) {
                            if ($activeDay == $weekday + 1) {
                                echo "<th class='activeColumn'>$daysOfTheWeek[$weekday]</th>";
                            } else {
                                echo "<th>$daysOfTheWeek[$weekday]</th>";
                            }
                        }
                        ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    for ($period = 1; $period <= count($periods); ++$period) {
                        echo '<tr>';
                        echo '<td>';
                        echo Dates::formatTime($periods[$period]->startTime);
                        echo '<br> - <br>';
                        echo Dates::formatTime($periods[$period]->endTime);
                        echo '</td>';

                        for ($weekday = $grid->startDay - 1; $weekday < $grid->endDay; ++$weekday) {
                            $class = ($activeDay == $weekday + 1) ? ' class="activeColumn"' : '';
                            echo '<td' . $class . '></td>';
                        }

                        echo '</tr>';
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
        <button class="icon-cancel" onclick="this.parentElement.style.display='none';"></button>
        <div class="lesson-data">
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
                <?php echo Languages::_('THM_ORGANIZER_SAVE_LESSON_SEMESTER') ?>
            </button>
            <button id="save-mode-period">
                <?php echo Languages::_('THM_ORGANIZER_SAVE_LESSON_PERIOD') ?>
            </button>
            <button id="save-mode-instance">
                <?php echo Languages::_('THM_ORGANIZER_SAVE_LESSON_INSTANCE') ?>
            </button>
        </div>
        <div class="delete">
            <button id="delete-mode-semester">
                <?php echo Languages::_('THM_ORGANIZER_DELETE_LESSON_SEMESTER') ?>
            </button>
            <button id="delete-mode-period">
                <?php echo Languages::_('THM_ORGANIZER_DELETE_LESSON_PERIOD') ?>
            </button>
            <button id="delete-mode-instance">
                <?php echo Languages::_('THM_ORGANIZER_DELETE_LESSON_INSTANCE') ?>
            </button>
        </div>
    </div>

    <div id="next-date-selection" class="message pop-up">
        <p><?php echo Languages::_('THM_ORGANIZER_JUMP_DATE'); ?></p>
        <button class="icon-cancel" onclick="this.parentElement.style.display='none';"></button>
        <button id="past-date" onclick="scheduleApp.nextDateEventHandler(event);">
            <span class="icon-arrow-left-2"></span>
            <?php echo sprintf(Languages::_('THM_ORGANIZER_JUMP_TO_DATE'), date("d.m.Y")); ?>
        </button>
        <button id="future-date" onclick="scheduleApp.nextDateEventHandler(event);">
            <span class="icon-arrow-right-2"></span>
            <?php echo sprintf(Languages::_('THM_ORGANIZER_JUMP_TO_DATE'), date("d.m.Y")); ?>
        </button>
    </div>

    <div id="no-lessons" class="message pop-up">
        <p>
            <span class="icon-notification"></span>
            <span><?php echo Languages::_('THM_ORGANIZER_NO_LESSONS_PLANNED'); ?></span>
        </p>
        <button class="icon-cancel" onclick="this.parentElement.style.display='none';"></button>
    </div>

    <div id="reg-fifo" class="message pop-up">
        <p>
            <span class="icon-notification"></span>
            <span><?php echo Languages::_('THM_ORGANIZER_COURSE_MAIL_STATUS_REGISTERED'); ?></span>
        </p>
        <button class="icon-cancel" onclick="this.parentElement.style.display='none';"></button>
    </div>

    <div id="reg-manual" class="message pop-up">
        <p>
            <span class="icon-notification"></span>
            <span><?php echo Languages::_('THM_ORGANIZER_COURSE_MAIL_STATUS_WAIT_LIST'); ?></span>
        </p>
        <button class="icon-cancel" onclick="this.parentElement.style.display='none';"></button>
    </div>
</div>
