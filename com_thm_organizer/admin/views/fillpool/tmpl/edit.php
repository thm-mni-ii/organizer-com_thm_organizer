<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view fillpool edit
 * @description THM_Curriculum component admin view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=fillpool&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="farbe-form">
	<fieldset class="adminform">
		<legend>Details</legend>
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
		<input type="hidden" name="task" value="farbe.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
