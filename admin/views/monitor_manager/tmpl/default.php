<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        monitor manager default template
 * @description standard template for the display of registered monitors
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined('_JEXEC') or die('Restricted access');?>
<div id="thm_organizer_mm" >
    <form action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>" method="post" name="adminForm">
        <div id="editcell">
            <table class="adminlist thm_organizer_mm_table">
                <colgroup>
                    <col id="thm_organizer_mm_col_checkbox" />
                    <col id="thm_organizer_mm_col_ip" />
                    <col id="thm_organizer_mm_col_room" />
                    <col id="thm_organizer_mm_col_semester" />
                </colgroup>
                <thead>
                    <tr>
                        <th />
                        <th><?php echo JText::_( 'IP Address' ); ?></th>
                        <th><?php echo JText::_( 'Room' ); ?></th>
                        <th><?php echo JText::_( 'Semester' ); ?></th>
                    </tr>
                </thead>
<?php $k = 0; if(!empty($this->monitors)): foreach($this->monitors as $monitor) :
        $checked = JHTML::_( 'grid.id', $k, $monitor['monitorID'] );
        $class = ($k % 2 == 0)?  'row0' : 'row1';
        $linkstart = "<a href='".$monitor['link']."' >";
        $linkend = "</a>";
        $k++ ?>
	        <tr class="<?php echo $class; ?>">
	            <td class="thm_organizer_mm_checkbox"><?php echo $checked; ?></td>
	            <td class="thm_organizer_mm_ip"><?php echo $linkstart.$monitor['ip'].$linkend; ?></td>
	            <td class="thm_organizer_mm_room"><?php echo $linkstart.$monitor['room'].$linkend; ?></td>
	            <td class="thm_organizer_mm_semester"><?php echo $linkstart.$monitor['semester'].$linkend; ?></td>
	        </tr>
<?php endforeach; endif;?>
	    </table>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
