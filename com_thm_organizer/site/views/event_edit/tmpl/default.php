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
$showListLink = (isset($this->listLink) and $this->listLink != "")? true : false;
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
    var categories = [], invalidFormText = '<?php echo addslashes(JText::_('COM_THM_ORGANIZER_EE_INVALID_FORM'));?>'';
<?php
foreach ($this->categories as $category)
{
    echo $category['javascript'];
}
?>
</script>
<div id="thm_organizer_ee" class='thm_organizer_ee'>
    <form enctype="multipart/form-data"
          action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=event_edit'); ?>"
          method="post"
          name="eventForm"
          id="eventForm"
          class="eventForm form-validate">
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
{
?>
                <span class="thm_organizer_divider_span"></span>
<?php
}
?>
                <a  class="hasTip thm_organizer_action_link"
                    title="<?php echo JText::_('COM_THM_ORGANIZER_SAVE_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_SAVE_DESCRIPTION');?>"
                    onclick="Joomla.submitbutton('event.save')">
                    <span id="thm_organizer_save_span" class="thm_organizer_save_span thm_organizer_action_span"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_SAVE_TITLE'); ?>
                </a>
                <a  class="hasTip thm_organizer_action_link"
                    title="<?php echo JText::_('COM_THM_ORGANIZER_SAVE_NEW_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_SAVE_NEW_DESCRIPTION');?>"
                    onclick="Joomla.submitbutton('event.save2new')">
                    <span id="thm_organizer_save_new_span" class="thm_organizer_save_new_span thm_organizer_action_span"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_SAVE_NEW'); ?>
                </a>
                <a  class="hasTip thm_organizer_action_link_preview"
                    title="<?php echo JText::_('COM_THM_ORGANIZER_PREVIEW_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_PREVIEW_DESCRIPTION');?>"
                    onclick="Joomla.submitbutton('event.preview')">
                    <span id="thm_organizer_preview_span" class="thm_organizer_preview_span thm_organizer_action_span"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_PREVIEW'); ?>
                </a>
                <a  class="hasTip thm_organizer_action_link"
                    title="<?php echo $cancelTip;?>"
                    onclick="window.history.back()">
                    <span id="thm_organizer_cancel_span" class="thm_organizer_cancel_span thm_organizer_action_span"></span>
                    <?php echo $cancelText; ?>
                </a>
                <div id="preview" class="Popup">
                    <div class="loader"></div>
                    <h1><?php echo JText::_('COM_THM_ORGANIZER_PREVIEW_HEADER');?></h1>
                    <div id="thm_organizer_ee_preview_event"><?php sleep(4); ?></div>
                    <a href="#" class="closePopup" onclick="closePopup();">
                        <?php echo JText::_('COM_THM_ORGANIZER_PREVIEW_CLOSE');?>
                    </a>
                </div>
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
        <input type='hidden' name='Itemid' value="<?php echo JFactory::getApplication()->input->get('Itemid', 0); ?>" />
        <input type='hidden' name='task' value='event.save' />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
<div class="loader"></div>
