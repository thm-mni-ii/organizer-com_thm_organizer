<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        edit event default template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$showEventLink = (isset($this->eventLink) and $this->eventLink != "")? true : false;
$eventID = $this->form->getValue('id');
if (!empty($eventID))
{
    $cancelText = JText::_('COM_THM_ORGANIZER_CANCEL');
    $cancelTip = JText::_('COM_THM_ORGANIZER_CANCEL_TOOLTIP');
}
else
{
    $cancelText = JText::_('COM_THM_ORGANIZER_CLOSE');
    $cancelTip = JText::_('COM_THM_ORGANIZER_CLOSE_TOOLTIP');
}
?>
<script type="text/javascript">
    var categories = new Array;
    var jq = jQuery.noConflict();
 
    jq("body").on({
        ajaxStart: function() {
            jq(this).addClass("loading");
        },
        ajaxStop: function() {
            jq(this).removeClass("loading");
        }
    });

    jq(document).ready( function() { 
       jq('.closePopup').live("click", function() {
           jq(".Popup").fadeOut("slow");
           jq(".overlay").fadeOut("slow", remove_preview_content());
           return false;
       });
    });

    function preview_content(response) {
        var json = jq.parseJSON(response);
        jq('#thm_organizer_ee_preview_event').append("<div id='thm_organizer_e_preview_div' class='thm_organizer_e_preview_div' >\
                                                        <div class='thm_organizer_e_title'>"           + json.title        + "</div>\
                                                        <div class='thm_organizer_e_publish_up'>"      + json.created_at   + "</div>\
                                                        <div class='thm_organizer_e_author'>"          + json.username     + "</div>\
                                                        "                                              + json.introtext    + "\
                                                        <div class='thm_organizer_e_description'>"     + json.description  + "</div>\
                                                      </div>");
    }
 
    function remove_preview_content() {
        var d = document.getElementById('thm_organizer_ee_preview_event');
        var olddiv = document.getElementById('thm_organizer_e_preview_div');
        d.removeChild(olddiv);
    }
 
    function build_url() {        
        var url = "<?php echo $this->baseurl; ?>";
        url = url + "/index.php?option=com_thm_organizer&view=event_ajax&format=raw&eventID=";;
        url = url + jq('#jform_id').val() + "&title=";
        url = url + jq('#jform_title').val() + "&id=";
        url = url + jq('#jform_id').val() + "&startdate=";
        url = url + jq('#jform_startdate').val() + "&enddate=";
        url = url + jq('#jform_enddate').val() + "&starttime=";
        url = url + jq('#jform_starttime').val() + "&endtime=";
        url = url + jq('#jform_endtime').val() + "&category=";
        url = url + jq('#category').val() + "&rec_type=";
        url = url + getRecType() + "&teachers[]=";
        url = url + getResources('#teachers') + "&rooms[]=";
        url = url + getResources('#rooms') + "&groups[]=";
        url = url + getResources('#groups');
        return url;
    }
 
<?php
foreach ($this->categories as $category)
{
    echo $category['javascript'];
}
?>

/**
 * was not moved to edit_event.js because of use of joomla language support in
 * alert output
 */
Joomla.submitbutton =  function(task){
    if (task === '') { return false; }
    else
    {
        var isValid = true;
        var action = task.split('.');
 
        if (action[1] !== 'cancel' && action[1] !== 'close')
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

        var requrl = build_url();
        if (isValid && task === 'event.preview')
        {
            var description = document.getElementById("jform_description_ifr").contentWindow.document.getElementById("tinymce").innerHTML;
            var descriptionString = String(description);
            description = descriptionString.indexOf("data-mce-bogus") != -1? '' : description;
            requrl = requrl + "&description=" + description  + "&task=preview";
            jq.ajax( {
                type    : "GET",
                url     : requrl,
                success : function(response) {
                            preview_content(response);
                            jq('.Popup').fadeIn("slow");
                            jq('.overlay').fadeIn("slow");
                            return false;
                        },
                failure : function() {
                          return false;
                }
            });
        }
        else if (isValid)
        {
            requrl = requrl + "&task=booking";
            jq.ajax( {
                type    : "GET",
                url     : requrl,
                success : function(response) {
                    var confirmed = true;
                    if(response){ confirmed = confirm(response); }
                    if(confirmed){Joomla.submitform(task, document.eventForm); }
                    return false;
                },
                failure : function() {
                    return false;
                }
            });
        }
        else
        {
            alert('<?php echo addslashes(JText::_('COM_THM_ORGANIZER_EE_INVALID_FORM')); ?>');
            return false;
        }
    }
}
</script>
<div id="thm_organizer_ee" class='thm_organizer_ee'>
    <form enctype="multipart/form-data"
          action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=event_edit'); ?>"
          method="post"
          name="eventForm"
          id="eventForm"
          class="eventForm form-validate">
          <?php echo $this->getToolbar(); ?>
        <div id="thm_organizer_ee_head_div" class='thm_organizer_ee_head_div'>
            <span id="thm_organizer_ee_title" class='thm_organizer_ee_title'>
                <?php echo ($this->event['id'] == 0)? JText::_('COM_THM_ORGANIZER_EE_NEW') : JText::_('COM_THM_ORGANIZER_EE_EDIT'); ?>
            </span>
            <div id="thm_organizer_ee_button_div" class='thm_organizer_ee_button_div'>
<?php
                if ($showListLink)
                {
                    $listTitle = JText::_('COM_THM_ORGANIZER_LIST_TITLE');
                    $listTitle .= "::" . JText::_('COM_THM_ORGANIZER_LIST_DESCRIPTION')
?>
                <a  class="hasTip thm_organizer_action_link"
                    title="<?php echo $listTitle;?>"
                    href="<?php echo $this->listLink ?>">
                    <span id="thm_organizer_list_span" class="thm_organizer_list_span thm_organizer_action_span"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_LIST'); ?>
                </a>
<?php
                }
                if ($showEventLink)
                {
                    $eventTitle = JText::_('COM_THM_ORGANIZER_EVENT_TITLE');
                    $eventTitle .= "::" . JText::_('COM_THM_ORGANIZER_EVENT_DESCRIPTION');
?>
                <a  class="hasTip thm_organizer_action_link"
                    title="<?php echo $eventTitle;?>"
                    href="<?php echo $this->eventLink ?>">
                    <span id="thm_organizer_event_span" class="thm_organizer_event_span thm_organizer_action_span"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_EVENT'); ?>
                </a>
                <?php
                }
                if ($showListLink or $showEventLink)
                {?>
                <span class="thm_organizer_divider_span"></span>
                <?php
                } ?>
                <div class="Popup">
                    <div class="loader"></div>
                    <h1><?php echo JText::_('COM_THM_ORGANIZER_PREVIEW_HEADER');?></h1>
                    <div id="thm_organizer_ee_preview_event"><?php sleep(4); ?></div>
                    <a href="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=event_edit'); ?>" class="closePopup">
                        <?php echo JText::_('COM_THM_ORGANIZER_PREVIEW_CLOSE');?>
                    </a>
                </div>
                <div id="overlay" class="overlay closePopup"></div>
            </div>
        </div>
        <div id="thm_organizer_ee_name_div" class='thm_organizer_ee_name_div'>
            <div class="thm_organizer_ee_label_div" >
                <?php echo $this->form->getLabel('title'); ?>
            </div>
            <div class="thm_organizer_ee_data_div" >
                <?php echo $this->form->getInput('title'); ?>
            </div>
        </div>
        <div id="thm_organizer_ee_desc_div" class='thm_organizer_ee_desc_div'>
            <div class="thm_organizer_ee_label_div" >
                <?php echo $this->form->getLabel('description'); ?>
            </div>
            <div class="thm_organizer_ee_data_div" >
                <?php echo $this->form->getInput('description'); ?>
            </div>
        </div>
        <div id="thm_organizer_ee_time_div" class='thm_organizer_ee_time_div'>
            <table class="thm_organizer_ee_table">
                <tr>
                    <td><?php echo $this->form->getLabel('startdate'); ?></td>
                    <td><?php echo $this->form->getInput('startdate'); ?></td>
                    <td><?php echo $this->form->getLabel('starttime'); ?></td>
                    <td><?php echo $this->form->getInput('starttime'); ?></td>
                    <td>
                        <label for="rec_type_block">
                            <span class="hasTip" title="<?php echo JText::_('COM_THM_ORGANIZER_EE_BLOCK_TITLE_ATTR'); ?>">
                                <?php echo JText::_('COM_THM_ORGANIZER_CONTINUOUS') . ":"; ?>
                            </span>
                        </label>
                    </td>
                    <td>
                        <input type="radio" id="rec_type_block" name="rec_type" <?php echo $this->blockchecked;?> value="0">
                    </td>
                </tr>
                <tr>
                    <td><?php echo $this->form->getLabel('enddate'); ?></td>
                    <td><?php echo $this->form->getInput('enddate'); ?></td>
                    <td><?php echo $this->form->getLabel('endtime'); ?></td>
                    <td><?php echo $this->form->getInput('endtime'); ?></td>
                    <td>
                        <label for="rec_type_daily">
                           <span class="hasTip" title="<?php echo JText::_('COM_THM_ORGANIZER_EE_DAILY_TITLE_ATTR'); ?>">
                                <?php echo JText::_('COM_THM_ORGANIZER_DAILY') . ":"; ?>
                            </span>
                        </label>
                    </td>
                    <td>
                        <input type="radio" id="rec_type_daily" name="rec_type" <?php echo $this->dailychecked;?> value="1">
                    </td>
                </tr>
            </table>
        </div>
        <div id="thm_organizer_ee_category_div" class='thm_organizer_ee_category_div'>
            <div id="thm_organizer_ee_category_select_div" class='thm_organizer_ee_category_select_div'>
                <div class="thm_organizer_ee_label_div" >
                    <?php echo $this->form->getLabel('categories'); ?>
                </div>
                <div class="thm_organizer_ee_data_div" >
                    <?php echo $this->categoryselect; ?>
                </div>
            </div>
            <div id="thm_organizer_ee_event_cat_desc_div" >
                <div><?php echo $this->categories[$this->event['categoryID']]['description']; ?></div>
            </div>
            <div id="thm_organizer_ee_event_cat_disp_div" >
                <div><?php echo $this->categories[$this->event['categoryID']]['display']; ?></div>
            </div>
            <div id="thm_organizer_ee_content_cat_name_div" >
                <div><?php echo $this->categories[$this->event['categoryID']]['contentCat']; ?></div>
            </div>
            <div id="thm_organizer_ee_content_cat_desc_div" >
                <div><?php echo $this->categories[$this->event['categoryID']]['contentCatDesc']; ?></div>
            </div>
            <div id="thm_organizer_ee_content_cat_access_div" >
                <div><?php echo $this->categories[$this->event['categoryID']]['access']; ?></div>
            </div>
        </div>
        <div id="thm_organizer_ee_resource_selection_div" class='thm_organizer_ee_resource_selection_div'>
            <table class="thm_organizer_ee_table">
                <tr>
                    <td>
                        <?php echo $this->form->getLabel('teachers'); ?>
                    </td>
                    <td>
                        <?php echo $this->form->getLabel('rooms'); ?>
                    </td>
                    <td>
                        <?php echo $this->form->getLabel('groups'); ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $this->teachersselect; ?></td>
                    <td><?php echo $this->roomsselect; ?></td>
                    <td><?php echo $this->groupsselect; ?></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <?php echo $this->form->getLabel('emailNotification'); ?>
                    </td>
                    <td>
                        <?php echo $this->form->getInput('emailNotification'); ?>
                    </td>
                </tr>
            </table>
        </div>
        <?php echo $this->form->getInput('id'); ?>
        <input type='hidden' name='Itemid' value="<?php echo JRequest::getVar('Itemid'); ?>" />
        <input type='hidden' name='task' value='event.save' />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
<div class="loader"></div>
