<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view degree edit default layout
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
?>
<form
    action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=degree_edit&id=' . (int) $this->item->id); ?>"
    method="post"
    name="adminForm"
    id="adminForm"
    class="form-horizontal">
    <fieldset>
        <legend>
            <?php echo JText::_('COM_THM_ORGANIZER_DEG_PROPERTIES')?>
        </legend>

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
                <?php echo $this->form->getLabel('abbreviation'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('abbreviation'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('lsfDegree'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('lsfDegree'); ?>
            </div>
        </div>

    </fieldset>
    <div>
        <?php echo $this->form->getInput('id'); ?>
        <input type="hidden" name="task" value="degree.save" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
