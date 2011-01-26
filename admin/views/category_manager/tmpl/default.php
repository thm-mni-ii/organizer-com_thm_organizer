<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php"  name="adminForm" method="post" >
<div  id="editcell" style="width:100%;">
    <table class="adminlist">
    <thead>
        <tr>
        	<th width="5" />
            <th width="10">
                <?php echo JText::_( 'Kategorie' ); ?>
            </th>
            <th>
                <?php echo JText::_( 'Beschreibung' ); ?>
            </th>
            <th width="50px"></th>
        </tr>            
    </thead>
    <?php
    $k = 0;
    for ($i=0, $n=count( $this->items ); $i < $n; $i++)
    {
        $row =& $this->items[$i];
        $checked = JHTML::_( 'grid.id', $i, $row->ecid );
        $link = JRoute::_('index.php?option=com_thm_organizer&controller=category&task=edit&cid[]='. $row->ecid ); 
        ?>
        <tr class="<?php echo "row$k"; ?>">
            <td align="center">
                <?php echo $checked; ?>
            </td>
            <td align="center">
	            <a href='<?php echo $link; ?>'><?php echo $row->ecname; ?></a>
            </td>
            <td align="center">
                <?php echo $row->ecdescription; ?>
            </td>
            <td align="center">
                <?php 
                if($row->ecimage)
                {
                	echo '<image src="../images/thm_organizer/categories/'.$row->ecimage.'" width="100px" height="100px"/>';
                }
                ?>
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
<input type="hidden" name="controller" value="category" />
 
</form>
