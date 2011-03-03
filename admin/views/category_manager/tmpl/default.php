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
                    <col id="thm_organizer_ce_checkbox_column" />
                    <col id="thm_organizer_ce_name_column" />
                    <col id="thm_organizer_ce_global_column" />
                    <col id="thm_organizer_ce_reserves_column" />
                </colgroup>
                <thead>
                    <tr>
                        <th align="left">
                            <input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
                        </th>
                        <th><?php echo JText::_( 'Name'); ?></th>
                        <th><?php echo JText::_( 'Global' ); ?></th>
                        <th><?php echo JText::_( 'Reserves' ); ?></th>
                    </tr>
                </thead>
                <tbody>
<?php $k = 0; if(!empty($this->categories)): foreach($this->categories as $category) :
        $checked = JHTML::_( 'grid.id', $k, $category['id'] );
        $class = ($k % 2 == 0)?  'row0' : 'row1';
        $linkstart = "<a href='".$category['link']."' >";
        $linkend = "</a>";
        $k++ ?>
                    <tr class="<?php echo $class; ?>">
                        <td class="thm_organizer_ce_checkbox"><?php echo $checked; ?></td>
                        <td class="thm_organizer_ce_name"><?php echo $category['id']; ?></td>
                        <td class="thm_organizer_ce_global"><?php echo $linkstart.$category['globaldisplay'].$linkend; ?></td>
                        <td class="thm_organizer_ce_reserve"><?php echo $linkstart.$category['reservesobjects'].$linkend; ?></td>
                    </tr>
<?php endforeach; endif;?>
                </tbody>
	    </table>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
