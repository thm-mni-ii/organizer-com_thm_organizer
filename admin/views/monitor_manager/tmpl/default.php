<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        monitor manager default template
 * @description standard template for the display of registered monitors
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die;?>
<div id="thm_organizer_mm" >
    <form action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>" method="post" name="adminForm">
        <div id="editcell">
            <table class="adminlist thm_organizer_mm_table">
                <colgroup>
                    <col id="thm_organizer_mm_col_checkbox" />
                    <col id="thm_organizer_mm_col_ip" />
                    <col id="thm_organizer_mm_col_room" />
                </colgroup>
                <thead>
                    <tr>
                        <th />
                        <th><?php echo JText::_('COM_THM_ORGANIZER_MON_IP'); ?></th>
                        <th><?php echo JText::_('COM_THM_ORGANIZER_MON_ROOM'); ?></th>
                    </tr>
                </thead>
<?php $k = 0; if(!empty($this->monitors)): foreach($this->monitors as $monitor) :
        $checked = JHTML::_( 'grid.id', $k, $monitor['monitorID'] );
        $class = ($k % 2 == 0)?  'row0' : 'row1'; $k++ ?>
	        <tr class="<?php echo $class; ?>">
	            <td class="thm_organizer_mm_checkbox">
                        <?php echo $checked; ?>
                    </td>
	            <td class="thm_organizer_mm_ip">
                    <?php if($this->access){ ?>
                        <a href='<?php echo $monitor['link']; ?>' >
                            <?php echo $monitor['ip']; ?>
                        </a>
                    <?php }else{ ?>
                        <?php echo $monitor['ip']; ?>
                    <?php } ?>
                    </td>
	            <td class="thm_organizer_mm_room">
                    <?php if($this->access){ ?>
                        <a href='<?php echo $monitor['link']; ?>' >
                            <?php echo $monitor['room']; ?>
                        </a>
                    <?php }else{ ?>
                        <?php echo $monitor['room']; ?>
                    <?php } ?>
                    </td>
	        </tr>
<?php endforeach; endif;?>
	    </table>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
