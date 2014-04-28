<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view curriculum default
 * @description curriculum view default layout
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

?>

<form id='thm_organizer_statistic_form' name='thm_organizer_statistic_form' enctype='multipart/form-data' method='post'
        action='<?php echo JRoute::_("index.php?option=com_thm_organizer&view=consumption"); ?>' >
        <?php echo $this->schedulesSelectBox; ?>
        <input type="submit" value="<?php echo JTEXT::_("CON_THM_ORGANIZER_CONSUMPTION_GET_STATISTIC");?>" name="get_statistic" id="thm_organizer_get_statistic" />
</form>
        <?php echo $this->consumptionRoomTable; ?>
        <?php echo $this->consumptionTeacherTable;
