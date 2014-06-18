<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        degree program edit view add layout
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
JHtml::_('behavior.tooltip');

?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=program_edit&id=0'); ?>"
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-horizontal">
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_PRM_PROPERTIES'); ?></legend>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('lsfFieldID'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('lsfFieldID'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('degreeID'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('degreeID'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('version'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('version'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('fieldID'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('fieldID'); ?>
            </div>
        </div>

    </fieldset>
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_PROPERTIES_DE'); ?></legend>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('subject_de'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('subject_de'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('description_de'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('description_de'); ?>
            </div>
        </div>
    </fieldset>
    <div>
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
