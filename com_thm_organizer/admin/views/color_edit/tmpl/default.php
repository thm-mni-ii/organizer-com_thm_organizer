<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view color edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=color_edit&id=' . (int) $this->item->id); ?>"
      method="post"
      name="adminForm"
      id="adminForm">
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_CLM_PROPERTIES'); ?></legend>
        <ul class="adminformlist">
            <li>
                <?php echo $this->form->getLabel('name'); ?>
                <?php echo $this->form->getInput('name'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('color'); ?>
                <?php echo $this->form->getInput('color'); ?>
            </li>
        </ul>
    </fieldset>
    <div>
        <?php echo $this->form->getInput('id'); ?>
        <?php echo JHtml::_('form.token'); ?>
        <input type="hidden" name="task" value="color.save" />
    </div>
</form>
