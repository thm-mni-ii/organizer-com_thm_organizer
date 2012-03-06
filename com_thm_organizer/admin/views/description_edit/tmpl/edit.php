<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        description managaer edit template
 * @author      Markus Bader markusDOTbaderATmniDOTthmDOTcom
 * @author      Daniel Kirsten danielDOTkirstenATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined("_JEXEC") or die;
JHtml::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=description_edit&layout=edit&id='.(int) $this->item->id); ?>"
      method="post" name="adminForm" id="description_edit-form">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'COM_THM_ORGANIZER_DS_DETAILS' ); ?></legend>
		<ul class="adminformlist">
<?php foreach($this->form->getFieldset() as $field): ?>
			<li><?php echo $field->label;echo $field->input;?></li>
<?php endforeach; ?>
		</ul>
	</fieldset>
	<div>
		<input type="hidden" name="task" value="description_edit.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>