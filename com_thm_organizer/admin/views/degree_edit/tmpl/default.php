<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view degree edit default layout
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=degree_edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="degree-form">
	<fieldset class="adminform">
		<legend>
			<?php echo JText::_('COM_THM_ORGANIZER_DEG_PROPERTIES')?>
		</legend>
		<ul class="adminformlist">
			<li>
				<?php echo $this->form->getLabel('name'); ?>
				<?php echo $this->form->getInput('name'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('abbreviation'); ?>
				<?php echo $this->form->getInput('abbreviation'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('lsfDegree'); ?>
				<?php echo $this->form->getInput('lsfDegree'); ?>
			</li>
		</ul>
	</fieldset>
	<div>
		<?php echo $this->form->getInput('id'); ?>
		<input type="hidden" name="task" value="degree.save" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
