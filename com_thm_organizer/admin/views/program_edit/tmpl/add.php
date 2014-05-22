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
    <div>
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
