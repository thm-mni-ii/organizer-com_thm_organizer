<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

$fieldset = $this->form->getFieldset('participant_management');

$shortTag = Languages::getShortTag();
$baseURL  = '?option=com_thm_organizer&view=courses&format=pdf';
$baseURL  .= "&lessonID={$this->course['id']}&languageTag=$shortTag&type=";

$participantListRoute = Route::_($baseURL . 0, false);
$departmentListRoute  = Route::_($baseURL . 1, false);
$badgesRoute          = Route::_($baseURL . 2, false);

$registeredText = Languages::_('THM_ORGANIZER_COURSE_REGISTERED');
$waitListText   = Languages::_('THM_ORGANIZER_WAIT_LIST');

$params     = OrganizerHelper::getParams();
$dateFormat = $params->get('dateFormat', 'd.m.Y') . ' ';
$dateFormat .= $params->get('timeFormat', 'H.i');
?>
<form action="?" method="post" id="adminForm" name="adminForm" onsubmit="listItemChecked();">
    <input type="hidden" name="option" value="com_thm_organizer"/>
    <input type="hidden" name="task" value="course.changeParticipantState"/>
    <?php echo $this->form->getField('id')->input; ?>
    <input type="hidden" name="subjectID" value="<?php echo $this->course['subjectID']; ?>"/>
    <input type="hidden" name="Itemid" value="<?php echo $this->menu['id']; ?>"/>
    <input type="hidden" name="participantState" id="participantState" value=""/>
    <div class="section">
        <div class="left form-header">
            <h4><?php echo Languages::_('THM_ORGANIZER_PARTICIPANTS'); ?></h4>
        </div>
        <div class="right">
            <?php echo $this->course['capacityText']; ?>
        </div>
        <div class="clear"></div>
        <div class="left">
            <button class="btn" onclick="listAction(1);">
                <span class="icon-checkbox-checked"></span> <?php echo Languages::_('THM_ORGANIZER_ACCEPT'); ?>
            </button>
            <button class="btn" onclick="listAction(0);">
                <span class="icon-checkbox-partial"></span> <?php echo Languages::_('THM_ORGANIZER_ADD_WAIT_LIST'); ?>
            </button>
            <button class="btn" onclick="listAction(2);">
                <span class="icon-remove"></span> <?php echo Languages::_('THM_ORGANIZER_DELETE'); ?>
            </button>
        </div>
        <div class="right">
            <a href="#" class="btn btn-mini callback-modal" type="button" data-toggle="modal" data-target="#circular">
                <span class="icon-mail"></span> <?php echo Languages::_('THM_ORGANIZER_CIRCULAR'); ?>
            </a>
            <div class="print-container">
                <a class="dropdown-toggle print btn" data-toggle="dropdown" href="#">
                    <span class="icon-print"></span>
                    <?php echo Languages::_('THM_ORGANIZER_PRINT_OPTIONS'); ?>
                    <span class="icon-arrow-down-3"></span>
                </a>
                <ul id="print" class="dropdown-menu">
                    <li>
                        <a href="<?php echo $participantListRoute; ?>" target="_blank">
                            <span class="icon-file-pdf"></span><?php echo Languages::_('THM_ORGANIZER_PARTICIPANTS'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $departmentListRoute; ?>" target="_blank">
                            <span class="icon-file-pdf"></span><?php echo Languages::_('THM_ORGANIZER_DEPARTMENT_STATISTICS'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $badgesRoute; ?>" target="_blank">
                            <span class="icon-file-pdf"></span><?php echo Languages::_('THM_ORGANIZER_BADGE_SHEETS'); ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <table class="table table-striped">
        <thead>
        <tr>
            <th><input type="checkbox" name="toggleSelect" id="toggleSelect" onclick="toggleAll(this);"></th>
            <th><?php echo Languages::_('THM_ORGANIZER_NAME'); ?></th>
            <th><?php echo Languages::_('THM_ORGANIZER_PROGRAM'); ?></th>
            <th><?php echo Languages::_('JGLOBAL_EMAIL'); ?></th>
            <th><?php echo Languages::_('JSTATUS'); ?></th>
            <th><?php echo Languages::_('THM_ORGANIZER_STATUS_DATE'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->course['participants'] as $participant): ?>
            <tr>
                <td>
                    <input type='checkbox' name='checked[]' value='<?php echo $participant['cid']; ?>'
                           onclick="toggleToggle(this)"/>
                </td>
                <td><?php echo $participant['name']; ?></td>
                <td><?php echo $participant['program']; ?></td>
                <td><?php echo $participant['email']; ?></td>
                <td><?php echo $participant['status'] ? $registeredText : $waitListText; ?></td>
                <td><?php echo HTML::_('date', $participant['status_date'], $dateFormat); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</form>
