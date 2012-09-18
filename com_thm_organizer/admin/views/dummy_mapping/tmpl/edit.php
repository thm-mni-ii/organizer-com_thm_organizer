<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view dummy_mapping edit
 * @description THM_Curriculum component admin view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
?>

<script type="text/javascript">
window.addEvent('domready',function(){
	element = document.getElementById("jformparent_id");

	if(element.selectedIndex > 0) {
		document.getElementById("semesters").disabled = true;
		getSemesterFromSelectedPool(element);
	}

})


Joomla.submitbutton = function(task)
{

	if (task == '')
	{
		return false;
	}
	else
	{
		var isValid=null;
		var action = task.split('.');
		if (task == 'mapping.cancel')
		{
			//alert("asd");
			Joomla.submitform(task);
		} else {

		var isNameEmpty = new InputValidator('required', {
		    errorMsg: 'This field Name is required.',
		    test: function(field){
		        return ((field.selectedIndex != 0) && (field.selectedIndex > -1));
		    }
		});

		var isSemestersEmpty = new InputValidator('required', {
		    errorMsg: 'This field Semesters is required.',
		    test: function(field){
		        return ((field.selectedIndex > -1) || (field.disabled == true  ));
		    }
		});

		
		isValidName = isNameEmpty.test($("jformasset"));

		if(!isValidName) {
			$('jformasset').setStyle('border-color', "red");
		} else {
			$('jformasset').setStyle('border-color', "silver");
		}

		
		isValidSemesters = isSemestersEmpty.test($("semesters"));

		if(!isValidSemesters) {
			$('semesters').setStyle('border-color', "red");
		}else {
			$('semesters').setStyle('border-color', "silver");
		}

		if ((isValidSemesters && isValidName))
		{
			Joomla.submitform(task);
			return true;
		}
		else
		{
			return false;
		}
	}}
}
</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=mapping&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="modulmapping-form">
	<fieldset class="adminform">
		<legend>Details</legend>
		<ul class="adminformlist">
			<?php foreach ($this->form->getFieldset() as $field)
			{
				?>
			<li><?php echo $field->label;
			echo $field->input;?></li>
			<?php
}
			?>
		</ul>
	</fieldset>
	<div>
		<input type="hidden" name="task" value="mapping.edit" />

		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
