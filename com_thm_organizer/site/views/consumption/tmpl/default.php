<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view curriculum default
 * @description consumption view default layout
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
?>

<h2 class="componentheading">
    <?php echo JText::_('COM_THM_ORGANIZER_CONSUMPTION_VIEW_TITLE'); ?>
</h2>
<form id='thm_organizer_statistic_form' name='thm_organizer_statistic_form' enctype='multipart/form-data' method='post'
        action='<?php echo JRoute::_("index.php?option=com_thm_organizer&view=consumption"); ?>' >
    <?php echo $this->schedulesSelectBox; ?>
    <input type="hidden" name="task" value="consumption.getConsumption" />
</form>
<?php
echo $this->roomsTable;
echo $this->teachersTable;
