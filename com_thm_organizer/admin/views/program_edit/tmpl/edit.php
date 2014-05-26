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
      method="post" name="adminForm" id="adminForm">
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_PRM_PROPERTIES'); ?></legend>
        <ul class="adminformlist">
            <li>
                <?php echo $this->form->getLabel('lsfFieldID'); ?>
                <?php echo $this->form->getInput('lsfFieldID'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('degreeID'); ?>
                <?php echo $this->form->getInput('degreeID'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('version'); ?>
                <?php echo $this->form->getInput('version'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('fieldID'); ?>
                <?php echo $this->form->getInput('fieldID'); ?>
            </li>
        </ul>
    </fieldset>
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_PROPERTIES_DE'); ?></legend>
        <ul class="adminformlist">
            <li>
                <?php echo $this->form->getLabel('subject_de'); ?>
                <?php echo $this->form->getInput('subject_de'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('description_de'); ?>
                <?php echo $this->form->getInput('description_de'); ?>
            </li>
        </ul>
    </fieldset>
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_PROPERTIES_EN'); ?></legend>
        <ul class="adminformlist">
            <li>
                <?php echo $this->form->getLabel('subject_en'); ?>
                <?php echo $this->form->getInput('subject_en'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('description_en'); ?>
                <?php echo $this->form->getInput('description_en'); ?>
            </li>
        </ul>
    </fieldset>
    <?php    include JPATH_COMPONENT_ADMINISTRATOR . '/templates/children.php'; ?>
    <div>
        <?php echo $this->form->getInput('id'); ?>
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
