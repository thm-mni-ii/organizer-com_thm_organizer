<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.view
 * @name        virtual schedule edit default template
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined("_JEXEC") or die;
?>
<form action="index.php?"
      enctype="multipart/form-data"
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-horizontal">
    <div id="thm_organizer_se" class="width-60 fltlft thm_organizer_se">
        <fieldset class="adminform">
            <legend><?php echo $this->legend; ?></legend>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('name'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('name'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('type'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('type'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('semester'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('semester'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('responsible'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('responsible'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('TeacherDepartment'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('TeacherDepartment'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('RoomDepartment'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('RoomDepartment'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('ClassDepartment'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('ClassDepartment'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('Teachers'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('Teachers'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('Rooms'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('Rooms'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('Classes'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('Classes'); ?>
                </div>
            </div>
        </fieldset>
    </div>
    <?php echo $this->form->getInput('vid'); ?>
    <?php echo $this->form->getInput('id'); ?>
    <?php echo JHtml::_('form.token'); ?>
    <input type="hidden" name="task" value="" />
</form>