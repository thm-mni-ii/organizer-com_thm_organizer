<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        default template for the event view
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
$event = $this->event;
$showListLink = (isset($this->listLink) and $this->listLink != "")? true : false;
?>
<div id="thm_organizer_e">
    <div id="thm_organizer_e_header" class='thm_organizer_e_header'>
        <div id="thm_organizer_e_headerlinks" class='thm_organizer_e_headerlinks'>
<?php
if ($showListLink)
{
?>
            <a  class="hasTip thm_organizer_action_link"
                title="<?php echo JText::_('COM_THM_ORGANIZER_LIST_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_LIST_DESCRIPTION');?>"
                href="<?php echo $this->listLink ?>">
                <span id="thm_organizer_list_span" class="thm_organizer_list_span thm_organizer_action_span"></span>
                <?php echo JText::_('COM_THM_ORGANIZER_LIST'); ?>
            </a>
<?php
}
if ($showListLink and ($event['access'] or $this->canWrite))
{
?>
            <span class="thm_organizer_divider_span"></span>
<?php
}
if ($this->canWrite)
{
?>
            <a  class="hasTip thm_organizer_action_link"
                title="<?php echo JText::_('COM_THM_ORGANIZER_NEW_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_NEW_DESCRIPTION');?>"
                href="<?php echo $this->baseurl; ?>/index.php?&option=com_thm_organizer&view=event_edit&Itemid=
                <?php echo JRequest::getInt('Itemid'); ?>"  >
                <span id="thm_organizer_new_span" class="thm_organizer_new_span thm_organizer_action_span"></span>
                <?php echo JText::_('COM_THM_ORGANIZER_NEW'); ?>
            </a>
<?php
}
if ($event['access'])
{
?>
            <a  class="hasTip thm_organizer_action_link"
                title="<?php echo JText::_('COM_THM_ORGANIZER_EDIT_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_EDIT_DESCRIPTION');?>"
                href="<?php echo JRoute::_("index.php?option=com_thm_organizer&task=event.edit&eventID={$this->event['id']}&Itemid=$this->itemID");
                ?>">
                <span id="thm_organizer_edit_span" class="thm_organizer_edit_span thm_organizer_action_span"></span>
                <?php echo JText::_('COM_THM_ORGANIZER_EDIT'); ?>
            </a>
            <a  class="hasTip thm_organizer_action_link"
                title="<?php echo JText::_('COM_THM_ORGANIZER_DELETE_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_DELETE_DESCRIPTION');?>"
                href="<?php echo JRoute::_("index.php?option=com_thm_organizer&task=event.delete&eventID={$this->event['id']}&Itemid=$this->itemID");
                ?>">
                <span id="thm_organizer_delete_span" class="thm_organizer_delete_span thm_organizer_action_span"></span>
                <?php echo JText::_('COM_THM_ORGANIZER_DELETE'); ?>
            </a>
<?php
}
?>
        </div>
    </div>
    <div class="thm_organizer_e_block_div" >        
        <div id="thm_organizer_e_title" class="thm_organizer_e_title">
            <p><?php echo $event['title']; ?></p>
        </div>
        <div class="thm_organizer_e_publish_up">
            <p><?php echo JText::_('COM_THM_ORGANIZER_PREVIEW_CREATED') . $event['publish_up']; ?></p>
        </div>
        <div class="thm_organizer_e_author">
            <p><?php echo JText::_('COM_THM_ORGANIZER_E_WRITTEN_BY') . $event['author']; ?></p>
        </div>        
        <div id="thm_organizer_e_time">
            <p><?php echo $this->dateTimeText; ?></p>
        </div>

        
<?php
if ($this->teachers or $this->rooms or $this->groups)
{
?>
        
<?php
}
if ($this->groups)
{
?>
            <p>
                <?php echo $this->groupsLabel; ?>
                <?php echo $this->groups; ?>
            </p>
<?php
}
if ($this->teachers)
{
?>
            <p>
                <?php echo $this->teachersLabel; ?>
                <?php echo $this->teachers; ?>
            </p>
<?php
}
if ($this->rooms)
{
?>
            <p>
                <?php echo $this->roomsLabel; ?>
                <?php echo $this->rooms; ?>
            </p>

        
<?php
}
?>
        
            <p><?php echo JText::_('COM_THM_ORGANIZER_E_INTROTEXT_FURTHER_INFORMATIONS'); ?></p>
        
<?php
if (!empty($event['description']))
{
?>
        <div id="thm_organizer_e_description" class='thm_organizer_e_description'>
            <?php echo $event['description']; ?>
        </div>
<?php
}
?>
    </div>
</div>
	