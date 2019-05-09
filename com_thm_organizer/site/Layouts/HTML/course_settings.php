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

?>
<form action="" method="post" id="adminForm" name="adminForm">
    <input type="hidden" name="option" value="com_thm_organizer"/>
    <?php echo $this->form->getField('id')->input; ?>
    <input type="hidden" name="subjectID" value="<?php echo $this->course['subjectID']; ?>"/>
    <input type="hidden" name="task" value="course.save"/>
    <input type="hidden" name="Itemid" value="<?php echo $this->menu['id']; ?>"/>
    <div class="section">
        <div class="left form-header">
            <h4><?php echo Languages::_('THM_ORGANIZER_COURSE_SETTINGS'); ?></h4>
        </div>
        <div class="clear"></div>
        <?php foreach ($this->form->getFieldset('course_settings') as $field) : ?>
            <div class='control-group'>
                <div class='control-label'><?php echo $field->label; ?></div>
                <div class='controls'><?php echo $field->input; ?></div>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="btn">
            <span class="icon-forward-2"></span> <?php echo Languages::_('JSAVE'); ?>
        </button>
        <div class="clear"></div>
    </div>
</form>
