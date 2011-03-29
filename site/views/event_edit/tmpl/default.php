<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
if(count($this->categories))
{
$event = $this->event; ?>
<script language="javascript" type="text/javascript">
    var categories = new Array;
<?php
$i = 0;
foreach($this->categories as $category)
{
    echo "\tcategories[{$category['id']}] = new Array(
        '".addslashes($category['description'])."',
        '".addslashes($category['display'])."',
        '".addslashes($category['contentCat'])."',
        '".addslashes($category['contentCatDesc'])."',
        '".addslashes($category['access'])."' );\n";
}
?>

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
            Joomla.submitform(task, document.getElementById('eventForm'));
            return true;
        }
        else
        {
            alert('<?php echo addslashes(JText::_('COM_THM_ORGANIZER_EE_INVALID_FORM')); ?>');
            return false;
        }
    }
}
</script>
<div id="thm_organizer_ee">
    <form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>"
          method="post" name="eventForm" id="eventForm" class="eventForm form-validate">
        <div id="thm_organizer_ee_head_div">
            <span id="thm_organizer_ee_title">
                <?php echo ($this->event['id'] == 0)? JText::_('COM_THM_ORGANIZER_EE_NEW') : JText::_('COM_THM_ORGANIZER_EE_EDIT'); ?>
            </span>
            <div id="thm_organizer_ee_button_div">
                <a  class="hasTip thm_organizer_ee_action_link"
                    title="<?php echo JText::_('COM_THM_ORGANIZER_EE_SAVE_TITLE')."::".JText::_('COM_THM_ORGANIZER_EE_SAVE_DESCRIPTION');?>"
                    onclick="Joomla.submitbutton('events.save')">
                    <span id="thm_organizer_ee_save_span" class="thm_organizer_el_action_span"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_EE_SAVE'); ?>
                </a>
                <a  class="hasTip thm_organizer_ee_action_link"
                    title="<?php echo JText::_('COM_THM_ORGANIZER_EE_SAVE_NEW_TITLE')."::".JText::_('COM_THM_ORGANIZER_EE_SAVE_NEW_DESCRIPTION');?>"
                    onclick="Joomla.submitbutton('events.save2new')">
                    <span id="thm_organizer_ee_save_new_span" class="thm_organizer_el_action_span"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_EE_SAVE_NEW'); ?>
                </a>
            </div>
        </div>
        <div id="thm_organizer_ee_category_div">
            <div id="thm_organizer_ee_category_select_div">
                <div class="thm_organizer_ee_label_div" >
                    <?php echo $this->form->getLabel('categoryDummy'); ?>
                </div>
                <div class="thm_organizer_ee_data_div" >
                    <?php echo $this->categoryselect; ?>
                </div>
            </div>
            <div id="thm_organizer_ee_event_cat_desc_div" >
                <p><?php echo $this->categories[$this->event['categoryID']]['description']; ?></p>
            </div>
            <div id="thm_organizer_ee_event_cat_disp_div" >
                <p><?php echo $this->categories[$this->event['categoryID']]['display']; ?></p>
            </div>
            <div id="thm_organizer_ee_content_cat_name_div" >
                <p><?php echo $this->categories[$this->event['categoryID']]['contentCat']; ?></p>
            </div>
            <div id="thm_organizer_ee_content_cat_desc_div" >
                <p><?php echo $this->categories[$this->event['categoryID']]['contentCatDesc']; ?></p>
            </div>
            <div id="thm_organizer_ee_content_cat_access_div" >
                <p><?php echo $this->categories[$this->event['categoryID']]['access']; ?></p>
            </div>
        </div>
        <div id="thm_organizer_ee_name_div">
            <div class="thm_organizer_ee_label_div" >
                <?php echo $this->form->getLabel('title'); ?>
            </div>
            <div class="thm_organizer_ee_data_div" >
                <?php echo $this->form->getInput('title'); ?>
            </div>
        </div>
        <div id="thm_organizer_ee_desc_div">
            <div class="thm_organizer_ee_label_div" >
                <?php echo $this->form->getLabel('description'); ?>
            </div>
            <div class="thm_organizer_ee_data_div" >
                <?php echo $this->form->getInput('description'); ?>
            </div>
        </div>
        <div id="thm_organizer_ee_time_div">
            <table>
                <tr>
                    <td>
                        <?php echo $this->form->getLabel('startdate'); ?>
                    </td>
                    <td>
                        <?php echo $this->form->getInput('startdate'); ?>
                    </td>
                    <td>
                        <?php echo $this->form->getLabel('starttime'); ?>
                    </td>
                    <td>
                        <?php echo $this->form->getInput('starttime'); ?>
                    </td>
                    <td>
                        <label for="rec_type_block">
                            <span class="hasTip" title="Durchgehend::Der Termin beginnt am Startdatum zur Startzeit und endet am Enddatum zur Endzeit.">
                                <?php echo JText::_('Durchgehend:'); ?>
                            </span>
                        </label>
                    </td>
                    <td>
                        <input type="radio" id="rec_type_block" name="rec_type" <?php echo $this->blockchecked;?> value="0">
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo $this->form->getLabel('enddate'); ?>
                    </td>
                    <td>
                        <?php echo $this->form->getInput('enddate'); ?>
                    </td>
                    <td>
                        <?php echo $this->form->getLabel('endtime'); ?>
                    </td>
                    <td>
                        <?php echo $this->form->getInput('endtime'); ?>
                    </td>
                    <td>
                        <label for="rec_type_daily">
                           <span class="hasTip" title="T&auml;glich::Der Termin findet t&auml;glich zwischen Start- und Endzeit statt, an allen Tagen zwischen Start- und Enddatum.">
                                <?php echo JText::_('Täglich:'); ?>
                            </span>
                        </label>
                    </td>
                    <td>
                        <input type="radio" id="rec_type_daily" name="rec_type" <?php echo $this->dailychecked;?> value="1">
                    </td>
                </tr>
            </table>
        </div>
        <div id="thm_organizer_ee_resource_selection_div" >
            <table>
                <tr>
                    <td align="center">
                        <?php echo $this->form->getLabel('teacherDummy'); ?>
                    </td>
                    <td align="center">
                        <?php echo $this->form->getLabel('roomDummy'); ?>
                    </td>
                    <td align="center">
                        <?php echo $this->form->getLabel('groupDummy'); ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $this->teacherselect; ?></td>
                    <td><?php echo $this->roomselect; ?></td>
                    <td><?php echo $this->groupselect; ?></td>
                </tr>
            </table>
        </div>
        <input type='hidden' name='eventID' value='<?php echo $this->event['id']; ?>' />
        <input type='hidden' name='task' value='event_edit.save' />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
<?php }else{ ?>
<span id="thm_organizer_el_noauth" ><?php echo JText::_('COM_THM_ORGANIZER_EE_NOAUTH'); ?></span>
<?php }
