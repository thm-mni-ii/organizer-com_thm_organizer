<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @description template for the uploading of new schedules
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Daniel Kirsten, <daniel.kirsten@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined("_JEXEC") or die;?>
<form action="index.php?option=com_thm_organizer"
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
                    <label class="thm_organizer_label" for="file">
                        <?php echo JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_TITLE"); ?>
                    </label>
                </div>
                <div class="controls">
                    <input name="file" type="file" />
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('rooms_assignment_required'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('rooms_assignment_required'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('description'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('description'); ?>
                </div>
            </div>

        </fieldset>
    </div>
    <input type="hidden" name="scheduleID" value="<?php echo $this->form->getValue('id'); ?>" />
    <?php echo JHtml::_('form.token'); ?>
    <input type="hidden" name="task" value="" />
</form>