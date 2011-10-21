<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        default template thm_organizer monitor editor view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */

defined('_JEXEC') or die; ?>
<div id="thm_organizer_mon" >
    <form action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>" method="post" name="adminForm">
        <table class="admintable">
            <colgroup>
                <col id="thm_organizer_mon_label_column" />
                <col id="thm_organizer_mon_data_column" />
                <col id="thm_organizer_mon_label_column" />
                <col id="thm_organizer_mon_data_column" />
            </colgroup>
            <tr>
                <td class="thm_organizer_mon_label_data" >
                    <label for="ip"><?php echo JText::_('COM_THM_ORGANIZER_MON_IP'); ?></label>
                </td>
                <td>
                    <input class="text_area" type="text" name="ip" id="ip"
                           size="6" maxlength="20" value="<?php echo $this->ip;?>" />
                </td>
                <td class="thm_organizer_mon_label_data" >
                    <label for="room"><?php echo JText::_('COM_THM_ORGANIZER_MON_ROOM'); ?></label>
                </td>
                <td><?php echo $this->room;?></td>
            </tr>
        </table>
        <input type="hidden" name="monitorID" value="<?php echo $this->monitorID; ?>" />
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>