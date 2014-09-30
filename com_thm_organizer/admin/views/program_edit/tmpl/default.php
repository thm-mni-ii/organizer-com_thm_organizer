<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view degree program edit view edit layout
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
JHtml::_('behavior.tooltip');
if (!empty($this->children))
{
    $maxOrdering = max(array_keys($this->children));
}
$rawPoolURL = 'index.php?option=com_thm_organizer&view=pool_manager';
$poolURL = JRoute::_($rawPoolURL, false);
$rawSubjectURL = 'index.php?option=com_thm_organizer&view=subject_manager';
$subjectURL = JRoute::_($rawSubjectURL, false);
?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=program_edit&id=' . $this->form->getValue('id')); ?>"
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
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_PROPERTIES_EN'); ?></legend>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('subject_en'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('subject_en'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('description_en'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('description_en'); ?>
            </div>
        </div>

    </fieldset>
    <?php include_once JPATH_COMPONENT_ADMINISTRATOR . '/templates/children.php'; ?>
    <div>
        <?php echo $this->form->getInput('id'); ?>
        <?php echo JHtml::_('form.token'); ?>
        <input type="hidden" name="task" value="" />
    </div>
</form>
