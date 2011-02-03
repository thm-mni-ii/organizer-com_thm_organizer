<?php defined('_JEXEC') or die('Restricted access');?>
<div id="thm_organizer_mm" >
    <form action="index.php" method="post" name="adminForm">
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
        $class = ($k % 2 == 0)?  'thm_organizer_mm_row_even' : 'thm_organizer_monitor_row_odd';
        $linkstart = "<a href='".$monitor['link']."' >";
        $linkend = "</a>";
        $k++ ?>
	        <tr class="<?php echo $class; ?>">
	            <td class="thm_organizer_mm_checkbox"><?php echo $checked; ?></td>
	            <td class="thm_organizer_mm_ip"><?php echo $linkstart.$monitor['ip'].$linkend; ?></td>
	            <td class="thm_organizer_mm_room"><?php echo $linkstart.$monitor['room'].$linkend; ?></td>
	            <td class="thm_organizer_mm_semester"><?php echo $linkstart.$monitor['orgunit']."-".$monitor['semester'].$linkend; ?></td>
	        </tr>
<?php endforeach; endif;?>
	    </table>
	</div>
	<input type="hidden" name="option" value="com_thm_organizer" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="monitor" />
    </form>
</div>
