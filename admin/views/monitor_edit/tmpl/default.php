<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        default template thm_organizer monitor editor view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined('_JEXEC') or die('Restricted access'); ?>
<div id="thm_organizer_me" >
    <form action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>" method="post" name="adminForm">
        <table class="admintable">
            <colgroup>
                <col id="thm_organizer_me_label_column" />
                <col id="thm_organizer_me_data_column" />
            </colgroup>
            <tr>
                <td class="thm_organizer_me_label_data" >
                    <label for="ip"><?php echo JText::_('IP Address:'); ?></label>
                </td>
                <td>
                    <input class="text_area" type="text" name="ip" id="ip" size="6" maxlength="20"
                                    value="<?php echo $this->ip;?>" />
                </td>
            </tr>
<?php if(!empty($this->roombox)) : ?>
            <tr>
                <td class="thm_organizer_me_label_data" >
                    <label for="room"><?php echo JText::_('Room:'); ?></label>
                </td>
                <td>
                    <input class="text_area" type="text" name="room" id="room" size="6" maxlength="6"
                                    value="<?php echo $this->room;?>" />
                </td>
            </tr>
<?php endif; if(!empty($this->semesterbox)) : ?>
            <tr>
                <td class="thm_organizer_me_label_data" >
                    <label for="semester">Semester</label>
                </td>
                <td><?php echo $this->semesterbox;?></td>
            </tr>
<?php endif; ?>
        </table>
        <input type="hidden" name="monitorID" value="<?php echo $this->monitorID; ?>" />
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>