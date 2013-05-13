<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view field edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=field_edit&id=' . (int) $this->item->id); ?>"
      method="post"
	  name="adminForm"
	  id="field-form">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_THM_ORGANIZER_FLM_PROPERTIES'); ?></legend>
		<ul class="adminformlist">
			<li>
				<?php echo $this->form->getLabel('field'); ?>
				<?php echo $this->form->getInput('field'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('gpuntisID'); ?>
				<?php echo $this->form->getInput('gpuntisID'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('colorID'); ?>
				<?php echo $this->form->getInput('colorID'); ?>
			</li>
		</ul>
	</fieldset>
	<div>
		<?php echo $this->form->getInput('id'); ?>
		<?php echo JHtml::_('form.token'); ?>
		<input type="hidden" name="task" value="field.save" />
	</div>
</form>
