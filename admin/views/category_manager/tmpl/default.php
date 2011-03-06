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
<div id="thm_organizer_cm" >
    <form action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>" method="post" name="adminForm">
        <table class="adminlist" id="thm_organizer_cm_table">
            <colgroup>
                <col id="thm_organizer_cm_checkbox_column" align="center" />
                <col id="thm_organizer_cm_title_column" />
                <col id="thm_organizer_cm_global_column" />
                <col id="thm_organizer_cm_reserves_column" />
                <col id="thm_organizer_cm_content_cat_column" />
            </colgroup>
            <thead>
                <tr>
                    <th align="left">
                        <input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
                    </th>
                    <th><?php echo JText::_( 'Name'); ?></th>
                    <th><?php echo JText::_( 'Global' ); ?></th>
                    <th><?php echo JText::_( 'Reserves' ); ?></th>
                    <th><?php echo JText::_( 'Content Category' ); ?></th>
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
                    <td class="thm_organizer_ce_name"><?php echo $linkstart.$category['title'].$linkend; ?></td>
                    <td class="thm_organizer_ce_global">
                        <?php echo $linkstart; ?>
                        <?php echo ($category['global'] == 1)? $this->yes : $this->no; ?>
                        <?php echo $linkend; ?>
                    </td>
                    <td class="thm_organizer_ce_reserve">
                        <?php echo $linkstart; ?>
                        <?php echo ($category['reserves'] == 1)? $this->yes : $this->no; ?>
                        <?php echo $linkend; ?>
                    </td>
                    <td class="thm_organizer_ce_reserve"><?php echo $linkstart.$category['contentCat'].$linkend; ?></td>
                </tr>
<?php endforeach; endif;?>
            </tbody>
        </table>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
