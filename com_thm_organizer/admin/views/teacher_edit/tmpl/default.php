<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view lecturer edit
 * @description THM_Curriculum component admin view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=teacher_edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="abschluss-form">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_THM_ORGANIZER_TRM_PROPERTIES'); ?></legend>
		<ul class="adminformlist">
<?php
foreach ($this->form->getFieldset() as $field)
{
	echo '<li>';
	echo $field->label;
	echo $field->input;
	echo '</li>';

}
?>
		</ul>
	</fieldset>
	<div>
		<input type="hidden" name="task" value="teacher.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
