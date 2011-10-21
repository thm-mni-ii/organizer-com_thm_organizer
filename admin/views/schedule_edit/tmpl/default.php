<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        schedule editor default template
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined("_JEXEC") or die;?>
<script language="javascript" type="text/javascript">
    var categories = new Array;

<?php
$i = 0;
foreach($this->categories as $category)
{
    echo 'categories['.$category['id'].'] = new Array( "'.mysql_real_escape_string($category['description']).'", "'.addslashes($category['display']).'",  "'.addslashes($category['contentCat']).'", "'.addslashes($category['contentCatDesc']).'", "'.addslashes($category['access']).'" );';
}
?>

function getRecType()
{
    for(var i=0; i < document.eventForm.rec_type.length; i++)
    {
        if(document.eventForm.rec_type[i].checked)
        {
            return document.eventForm.rec_type[i].value;
        }
    }
}

function getResources(resourceID)
{
    var resourceObject = document.getElementById(resourceID);
    var selectedResources = new Array();
    var index;
    var count = 0;
    for (index = 0; index < resourceObject.options.length; index++)
    {
        if (resourceObject.options[index].selected) {
            selectedResources[count] = resourceObject.options[index].value;
            count++;
        }
    }
    if(count)return selectedResources.toString();
    else return '';
}

/**
 * was not moved to edit_event.js because of use of joomla language support in
 * alert output
 */
Joomla.submitbutton = function(task)
{
    if (task == '') { return false; }
    else
    {
        var isValid=true;
        var action = task.split('.');
        if (action[1] != 'cancel' && action[1] != 'close')
        {
            var forms = $$('form.form-validate');
            for (var i=0;i<forms.length;i++)
            {
                if (!document.formvalidator.isValid(forms[i]))
                {
                    isValid = false;
                    break;
                }
            }
        }
        if (isValid)
        {
            Joomla.submitform(task, document.eventForm);
        }
        else
        {
            alert('<?php echo addslashes(JText::_('COM_THM_ORGANIZER_EE_INVALID_FORM')); ?>');
            return false;
        }
    }
}
</script>
<form action="<?php echo JRoute::_("index.php?option=com_thm_organizer"); ?>"
      enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
    <div id="thm_organizer_se" class="width-60 fltlft">
        <fieldset class="adminform">
            <legend><?php echo JText::_('COM_THM_ORGANIZER_EDIT')." ".$this->form->getValue('plantypeID'); ?></legend>
            <table class="adminformtable">
                <tr>
                    <td><?php echo $this->form->getLabel('filename'); ?></td>
                    <td><?php echo $this->form->getInput('filename'); ?></td>
                </tr>
                <tr>
                    <td><?php echo $this->form->getLabel('creationdate'); ?></td>
                    <td><?php echo $this->form->getValue('creationdate'); ?></td>
                </tr>
                <tr>
                    <td><?php echo $this->form->getLabel('description'); ?></td>
                    <td><?php echo $this->form->getInput('description'); ?></td>
                </tr>
                <tr>
                    <td><?php echo $this->form->getLabel('sid'); ?></td>
                    <td><?php echo $this->form->getInput('sid'); ?></td>
                </tr>
                <tr>
                    <td><?php echo $this->form->getLabel('startdate'); ?></td>
                    <td><?php echo $this->form->getInput('startdate'); ?></td>
                </tr>
                <tr>
                    <td><?php echo $this->form->getLabel('enddate'); ?></td>
                    <td><?php echo $this->form->getInput('enddate'); ?></td>
                </tr>
            </table>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="scheduleID" value="<?php echo $this->form->getValue('id'); ?>" />
</form>