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

?>
<div class="modal fade" id="circular">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="?option=com_thm_organizer&task=course.circular"
                  method="post" id="adminForm" name="adminForm">
                <input type="hidden" name="Itemid" value="<?php echo $this->menu['id']; ?>"/>
                <div class="modal-header">
                    <h3><?php echo $this->lang->_('COM_THM_ORGANIZER_CIRCULAR_HEADER') ?></h3>
                </div>
                <div class="modal-body" style="overflow-y: auto;">
                    <input type="hidden" name="lessonID" value="<?php echo $this->course['id']; ?>"/>
                    <input type="hidden" name="subjectID" value="<?php echo $this->course['subjectID']; ?>"/>
                    <?php foreach ($this->form->getFieldset('circular') as $field) : ?>
                        <div class='control-group'>
                            <div class='control-label'><?php echo $field->label; ?></div>
                            <div class='controls'><?php echo $field->input; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="validate btn btn-mini">
                        <span class="icon-mail"></span><?php echo $this->lang->_('JSUBMIT') ?></button>
                    <button type="button" class="btn btn-mini" data-dismiss="modal">
                        <span class="icon-cancel"></span><?php echo $this->lang->_('COM_THM_ORGANIZER_CLOSE') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
