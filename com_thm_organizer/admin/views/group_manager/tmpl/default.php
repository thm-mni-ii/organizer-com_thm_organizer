<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @description default view template file for group lists
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined("_JEXEC") or die;
$orderby = $this->escape($this->state->get('list.ordering'));
$direction = $this->escape($this->state->get('list.direction'));
?>
<form action="index.php?option=com_thm_organizer"
      enctype="multipart/form-data"
      method="post"
      name="adminForm"
      id="adminForm">
    <div id="filter-bar" class='filter-bar'>
        <div class="filter-select fltrt pull-right">
            <label title="<?php echo JText::_('COM_THM_ORGANIZER_GROUP') . '::' . JText::_('COM_THM_ORGANIZER_GPM_GROUP_SELECT_DESC'); ?>"
                   for="group"><?php echo JText::_('COM_THM_ORGANIZER_GROUP'); ?></label>
            <select name="filter_state" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_GPM_SELECT_GROUP'); ?></option>
                <option value="1"><?php echo JText::_('COM_THM_ORGANIZER_GPM_PROGRAM_MANAGER'); ?></option>
                <option value="2"><?php echo JText::_('COM_THM_ORGANIZER_GPM_PLANNER'); ?></option>
            </select>
            <div class="clr"> </div>
        </div>
    </div>
    <div class="clr"> </div>
    <div>
        <table class="table table-striped" cellpadding="0">
        </table>
    </div>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $orderby; ?>" />
    <input type="hidden" name="filter_order_dir" value="<?php echo $direction; ?>" />
    <input type="hidden" name="view" value="group_manager" />
    <?php echo JHtml::_('form.token'); ?>
</form>