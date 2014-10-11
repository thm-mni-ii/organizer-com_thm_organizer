<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view room emerge
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
?>
<form action="index.php?option=com_thm_organizer" method="post" name="adminForm">
    <fieldset class="adminform">
        <legend>
            <?php echo JText::_('COM_THM_ORGANIZER_RMM_PROPERTIES')?>
        </legend>
<?php if (count($this->name))
{
    $nameTitle = JText::_('COM_THM_ORGANIZER_NAME') . "::" . JText::_('COM_THM_ORGANIZER_RMM_NAME_DESC');
    echo '<fieldset class="adminform hasTip" title="' . $nameTitle . '">';
    echo '<legend>' . JText::_('COM_THM_ORGANIZER_NAME') . '</legend>';
    echo '<ul class="adminformlist">';
    foreach ($this->name AS $name)
    {
    echo '<li>' . $name . '</li>';
    }
    echo '</ul></fieldset>';
}
if (count($this->longname))
{
    $longnameTitle = JText::_('COM_THM_ORGANIZER_RMM_LONGNAME_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_RMM_LONGNAME_DESC');
    echo '<fieldset class="adminform hasTip" title="' . $longnameTitle . '">';
    echo '<legend>' . JText::_('COM_THM_ORGANIZER_RMM_LONGNAME_TITLE') . '</legend>';
    echo '<ul class="adminformlist">';
    foreach ($this->longname AS $longname)
    {
    echo '<li>' . $longname . '</li>';
    }
    echo '</ul></fieldset>';
}
if (count($this->gpuntisID))
{
    $gpuntisIDTitle = JText::_('COM_THM_ORGANIZER_GPUNTISID') . "::" . JText::_('COM_THM_ORGANIZER_RMM_GPUNTISID_DESC');
    echo '<fieldset class="adminform hasTip" title="' . $gpuntisIDTitle . '">';
    echo '<legend>' . JText::_('COM_THM_ORGANIZER_GPUNTISID') . '</legend>';
    echo '<ul class="adminformlist">';
    foreach ($this->gpuntisID AS $gpuntisID)
    {
    echo '<li>' . $gpuntisID . '</li>';
    }
    echo '</ul></fieldset>';
}
if (count($this->typeID))
{
    $typeIDTitle = JText::_('COM_THM_ORGANIZER_RMM_TYPE_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_RMM_TYPE_DESC');
    echo '<fieldset class="adminform hasTip" title="' . $typeIDTitle . '">';
    echo '<legend>' . JText::_('COM_THM_ORGANIZER_RMM_TYPE_TITLE') . '</legend>';
    echo '<ul class="adminformlist">';
    foreach ($this->typeID AS $typeID)
    {
    echo '<li>' . $typeID . '</li>';
    }
    echo '</ul></fieldset>';
}
?>
    </fieldset>
    <div>
        <?php echo $this->ID; ?>
        <?php echo $this->otherIDs; ?>
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
