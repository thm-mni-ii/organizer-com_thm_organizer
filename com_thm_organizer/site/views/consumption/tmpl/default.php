<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view curriculum default
 * @description consumption view default layout
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$selectedType = $this->model->type;
?>
<div id="consumption" class="consumption">
    <form id='statistic-form' name='statistic-form' enctype='multipart/form-data' method='post'
          action='<?php echo JRoute::_("index.php?option=com_thm_organizer&view=consumption"); ?>' >
        <h2 class="componentheading">
            <?php echo JText::_('COM_THM_ORGANIZER_CONSUMPTION_VIEW_TITLE'); ?>
        </h2>
<?php
if (!empty($this->model->schedule))
{
?>
    <div class="button-panel">
        <button type="submit" value="submit"><?php echo JText::_('COM_THM_ORGANIZER_CONSUMPTION_SUBMIT'); ?></button>
        <button onclick="$('#reset').val('1')"><?php echo JText::_('COM_THM_ORGANIZER_RESET'); ?></button>
        <?php echo $this->exportButton; ?>
    </div>
<?php
}
?>
        <div class="filter-bar">
            <div class="filter-header">
                <div class="filter-item">
                    <label for="activated"><?php echo JText::_('COM_THM_ORGANIZER_SCHEDULE')?>:</label>
                    <?php echo $this->scheduleSelectBox; ?>
                </div>
<?php
if (!empty($this->model->schedule))
{
?>
                <div class="filter-item">
                    <label for="type"><?php echo JText::_('COM_THM_ORGANIZER_RESOURCE_TYPES')?>:</label>
                    <?php echo $this->typeSelectBox; ?>
                </div>
                <div class="filter-item">
                    <label for="startDate"><?php echo JText::_('COM_THM_ORGANIZER_STARTDATE')?>:</label>
                    <?php echo $this->startCalendar ?>
                </div>
                <div class="filter-item">
                    <label for="endDate"><?php echo JText::_('COM_THM_ORGANIZER_ENDDATE')?>:</label>
                    <?php echo $this->endCalendar ?>
                </div>
<?php
}
?>
            </div>
<?php
if (!empty($this->model->schedule))
{
    if ($this->model->process['rooms'] AND !empty($this->roomsSelectBox))
    {
?>
            <div class="filter-toggle">
                <a class="toggle-link" onclick="toggleRooms();">
                    <span id="filter-room-toggle-image" class="toggle-button toggle-closed"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_ROOM_FILTER_DISPLAY'); ?>
                </a>
            </div>
            <div id="filter-room" class="filter-resource" style="display: none">
                <div class="filter-resource-item">
                    <label for="roomtypes"><?php echo JText::_('COM_THM_ORGANIZER_ROOMTYPES')?>:</label>
                    <?php echo $this->roomtypesSelectBox; ?>
                </div>
                <div class="filter-resource-item">
                    <label for="rooms"><?php echo JText::_('COM_THM_ORGANIZER_ROOMS')?>:</label>
                    <?php echo $this->roomsSelectBox; ?>
                </div>
            </div>
<?php
    }
    if ($this->model->process['teachers'] AND !empty($this->teachersSelectBox))
    {
?>
            <div class="filter-toggle">
                <a class="toggle-link" onclick="toggleTeachers();">
                    <span id="filter-teacher-toggle-image" class="toggle-button toggle-closed"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_TEACHER_FILTER_DISPLAY'); ?>
                </a>
            </div>
            <div id="filter-teacher" class="filter-resource" style="display: none">
                <div class="filter-resource-item">
                    <label for="fields"><?php echo JText::_('COM_THM_ORGANIZER_FIELDS')?>:</label>
                    <?php echo $this->fieldsSelectBox; ?>
                </div>
                <div class="filter-resource-item">
                    <label for="teachers"><?php echo JText::_('COM_THM_ORGANIZER_TEACHERS')?>:</label>
                    <?php echo $this->teachersSelectBox; ?>
                </div>
            </div>
<?php
    }
}
?>
            <input type="hidden" id="reset" name="reset" value="0" />
        </div>
<?php
    echo $this->roomsTable;
    echo $this->teachersTable;
?>
    </form>
</div>
