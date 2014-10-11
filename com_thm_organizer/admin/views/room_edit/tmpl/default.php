<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view room edit default layout
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
?>
<form
    action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=room_edit&id=' . (int) $this->item->id); ?>"
    method="post"
    name="adminForm"
    id="adminForm"
    class="form-horizontal">
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_RMM_PROPERTIES'); ?></legend>
<?php
foreach ($this->form->getFieldset() as $field)
{
    echo "<div class=\"control-group\">";
    echo "<div class=\"control-label\">";
    echo $field->label;
    echo "</div>";
    echo "<div class=\"controls\">";
    echo $field->input;
    echo "</div>";
    echo "</div>";
}
?>
    </fieldset>
    <div>
        <?php echo JHtml::_('form.token'); ?>
        <input type="hidden" name="task" value="lecturer.edit" />
    </div>
</form>
