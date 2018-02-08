<?php
/**
 * Created by PhpStorm.
 * User: Florian Fenzl
 * Date: 08.03.2017
 * Time: 11:02
 */

$menuID = JFactory::getApplication()->input->getInt('Itemid');
$task   = 'participant.save';

if (empty($this->course)) {
    $headerText = $this->lang->_("COM_THM_ORGANIZER_USER_PROFILE");
    $lessonID   = 0;
    $message    = '';
    $submitText = $this->lang->_('JSAVE');
} else {
    $headerText = '<div class="header-introtext">' . $this->lang->_("COM_THM_ORGANIZER_PARTICIPANT_EDIT_REGISTER_HEADER") . '</div>';
    $headerText .= $this->course["name"];
    $dateText   = "{$this->course['startDate']} - {$this->course['endDate']}";
    $headerText .= '<div class="header-subtext">' . $dateText . '</div>';

    $lessonID = $this->course["id"];

    $message    = '<div class="tbox-yellow">';
    $message    .= $this->lang->_("COM_THM_ORGANIZER_REGISTRATION_REQUIRED");
    $message    .= '</div>';
    $submitText = $this->lang->_('JLOGIN');
    $task       = 'participant.register';
}

?>
<div class="toolbar">
    <div class="tool-wrapper language-switches">
        <?php
        foreach ($this->languageSwitches as $switch) {
            echo $switch;
        } ?>
    </div>
</div>
<div class="participant-edit">
    <h1><?php echo $headerText; ?></h1>
    <?php echo $message; ?>
    <form action="index.php?option=com_thm_organizer" enctype="multipart/form-data" method="post"
          id="form-participant_edit" class="form-horizontal">
        <input type="hidden" name="option" value="com_thm_organizer">
        <input type="hidden" name="task" value="<?php echo $task; ?>">
        <input type='hidden' name='Itemid' value='<?php echo $menuID; ?>'>
        <input type='hidden' name='lessonID' value='<?php echo $lessonID; ?>'>
        <input type="hidden">
        <div class="form-horizontal">
            <?php foreach ($this->form->getFieldset() as $field): ?>
                <?php if ($field->type == 'Hidden'): ?>
                    <?php echo $field->input; ?>
                <?php else: ?>
                    <div class='control-group'>
                        <div class='control-label'><?php echo $field->label; ?></div>
                        <div class='controls'><?php echo $field->input; ?></div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php echo JHtml::_('form.token'); ?>
        <div class="control-group">
            <div class="controls">
                <button type="submit" class="validate btn btn-primary">
                    <?php echo $submitText; ?>
                </button>
                <a href="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=course_list', false, 2); ?>"
                   class="btn" type="button"><?php echo $this->lang->_("JCANCEL") ?></a>
            </div>
        </div>
    </form>
</div>

