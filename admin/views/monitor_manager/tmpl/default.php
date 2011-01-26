<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="adminForm" style="width:20%;">
	<div id="editcell">
	    <table class="adminlist">
	    <thead>
	        <tr>
	            <th width="10%" />
	            <th width="40%">
	                <?php echo JText::_( 'Raum' ); ?>
	            </th>
	            <th width="50%">
	                <?php echo JText::_( 'IP Adresse' ); ?>
	            </th>
	            <th width="50%">
	                <?php echo JText::_( 'Fachsemester' ); ?>
	            </th>
	        </tr>            
	    </thead>
	    <?php
	    $k = 0;
	    for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	    {
	        $row =& $this->items[$i];
	        $checked = JHTML::_( 'grid.id', $i, $row->ip ); 
	        ?>
	        <tr class="<?php echo "row$k"; ?>">
	            <td align="center">
	                <?php echo $checked; ?>
	            </td>
	            <td align="center">
	                <?php echo $row->room; ?>
	            </td>
	            <td align="center">
	                <?php echo $row->ip; ?>
	            </td>
	            <td align="center">
	                <?php echo $row->orgunit."-".$row->semester; ?>
	            </td>
	        </tr>
	        <?php
	        $k = 1 - $k;
	    }
	    ?>
	    </table>
	</div>
	<input type="hidden" name="option" value="com_thm_organizer" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="room_ip" />
 
</form>
