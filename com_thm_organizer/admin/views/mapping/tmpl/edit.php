<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view mapping edit
 * @description THM_Curriculum component admin view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
$id = JRequest::getVar('id', array(), 'get', 'array');

$multipleEdit = JRequest::getVar('multiple_edit');
?>
<script type="text/javascript">

    /* dsiable semester field validation */
    var multipleEditFlag = "<?php echo $multipleEdit; ?>";

    window.addEvent('domready', function(){
        element = document.getElementById("jformparent_id");
	
        if(element.selectedIndex > 0) {
            document.getElementById("semesters").disabled = true;
            getSemesterFromSelectedPool(element);
        }
	        
        var id_element = document.getElementById('jform_id');
            
<?php if ($id)
{
?>
            id_element.setAttribute('value', "<?php echo $id[0]; ?>");
<?php
}?>     
    })
    Joomla.submitbutton = function(task)
    {
	
        if (task == '')
        {
            return false;
        }
        else
        {

            if(multipleEditFlag == 'true') {
                            
                Joomla.submitform(task);
                return true;
            }
			
            var isValid=null
            var action = task.split('.');
            if (task == 'mapping.cancel')
            {
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
                        
                var isColorEmpty = new InputValidator('required', {
                    errorMsg: 'This field Color is required.',
                    test: function(field){
                        return ((field.selectedIndex != 0) && (field.selectedIndex > -1));
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
                        
                isValidColor = isColorEmpty.test($("color_id"));
	
                if(!isValidColor) {
                    $('color_id').setStyle('border-color', "red");
                }else {
                    $('color_id').setStyle('border-color', "silver");
                }
	
                if ((isValidSemesters && isValidName && isValidColor))
                {
                    Joomla.submitform(task);
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
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
			echo $field->input;
			?>
			</li>
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
