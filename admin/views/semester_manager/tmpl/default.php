<?php defined('_JEXEC') or die('Restricted access');?>
<form action="index.php"  name="adminForm" method="post" >
<div  id="editcell" style="width:100%;">
    <table class="adminlist">
    <thead>
        <tr>
        	<th width="5" />
            <th width="15">
                <?php echo JText::_( 'Organization' ); ?>
            </th>
            <th width="15">
                <?php echo JText::_( 'Semester' ); ?>
            </th>
            <th width="15">
                <?php echo JText::_( 'Verantwortliche' ); ?>
            </th>
        </tr>            
    </thead>
    <?php
    $k = 0;
    for ($i=0, $n=count( $this->items ); $i < $n; $i++)
    {
        $row =& $this->items[$i];
        $checked = JHTML::_( 'grid.id', $i, $row->sid );
        $link = JRoute::_('index.php?option=com_thm_organizer&controller=semester&task=edit&cid[]='. $row->sid ); 
        ?>
        <tr class="<?php echo "row$k"; ?>">
            <td align="center">
                <?php echo $checked; ?>
            </td>
            <td align="center">
	            <a href='<?php echo $link; ?>'><?php echo $row->orgunit; ?></a>
            </td>
            <td align="center">
	            <a href='<?php echo $link; ?>'><?php echo $row->semester; ?></a>
            </td>
            <td align="center">
	            <a href='<?php echo $link; ?>'><?php echo $row->author; ?></a>
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
<input type="hidden" name="controller" value="semester" />
 
</form>
