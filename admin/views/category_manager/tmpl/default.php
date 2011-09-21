<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        category manager default template
 * @description standard template for the display of event categories
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
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
                    <th><?php echo JText::_('COM_THM_ORGANIZER_NAME'); ?></th>
                    <th><?php echo JText::_('COM_THM_ORGANIZER_CAT_GLOBAL'); ?></th>
                    <th><?php echo JText::_( 'COM_THM_ORGANIZER_CAT_RESERVES' ); ?></th>
                    <th><?php echo JText::_( 'COM_THM_ORGANIZER_CAT_CONTENT_CATEGORY' ); ?></th>
                </tr>
            </thead>
            <tbody>
<?php $k = 0; if(!empty($this->categories)): foreach($this->categories as $category) :
        $checked = JHTML::_( 'grid.id', $k, $category['id'] );
        $class = ($k % 2 == 0)?  'row0' : 'row1';
        $k++ ?>
                <tr class="<?php echo $class; ?>">
                    <td class="thm_organizer_ce_checkbox"><?php echo $checked; ?></td>
                    <td class="thm_organizer_ce_name">
                    <?php if($this->access){?>
                        <a href='<?php echo $category['link']; ?>' >
                            <?php echo $category['title']; ?>
                        </a>
                    <?php }else{ ?>
                        <?php echo $category['title']; ?>
                    <?php }?>
                    </td>
                    <td class="thm_organizer_ce_global">
                    <?php if($this->access){?>
                        <a href='<?php echo $category['link']; ?>' >
                            <?php echo ($category['global'])? $this->yes : $this->no; ?>
                        </a>
                    <?php }else{ ?>
                        <?php echo ($category['global'])? $this->yes : $this->no; ?>
                    <?php }?>
                    </td>
                    <td class="thm_organizer_ce_reserve">
                    <?php if($this->access){?>
                        <a href='<?php echo $category['link']; ?>' >
                            <?php echo ($category['reserves'])? $this->yes : $this->no; ?>
                        </a>
                    <?php }else{ ?>
                        <?php echo ($category['reserves'])? $this->yes : $this->no; ?>
                    <?php }?>
                    </td>
                    <td class="thm_organizer_ce_reserve">
                    <?php if($this->access){?>
                        <a href='<?php echo $category['link']; ?>' >
                            <?php echo $category['contentCat']; ?>
                        </a>
                    <?php }else{ ?>
                        <?php echo $category['contentCat']; ?>
                    <?php }?>
                    </td>
                </tr>
<?php endforeach; endif;?>
            </tbody>
        </table>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
