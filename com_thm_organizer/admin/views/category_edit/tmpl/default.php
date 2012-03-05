<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        template category editor view
 * @description default template for the category editor view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die;?>
<script type="text/javascript">
    var categories = new Array;
<?php
$i = 0;
foreach($this->contentCategories as $category)
{
    echo "categories[{$category['id']}] = new Array(
        '".addslashes($category['description'])."',
        '".addslashes($category['actions'])."',
        '".addslashes($category['view_level'])."' );\n";
}
?>
    /**
    * Changes a dynamically generated list
    * @param string The name of the list to change
    * @param array A javascript array of list options in the form [key,value,text]
    * @param string The key to display
    * @param string The original key that was selected
    * @param string The original item value that was selected
    */
    function changeCategoryInformation()
    {
        var index = document.getElementById('contentCat').selectedIndex;
        var id = document.getElementById('contentCat').options[index].value;
        document.getElementById('thm_organizer_cat_content_description').innerHTML = categories[id][0];
        document.getElementById('thm_organizer_cat_content_permissions').innerHTML = categories[id][1];
    }

</script>
<div id="thm_organizer_cat" >
    <form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>"
          method="post" name="adminForm" id="adminForm">
        <div id="thm_organizer_cat_ecat">
            <div id="thm_organizer_cat_ecat_name_div">
                <div class="thm_organizer_cat_label">
                    <label for="name"><?php echo JText::_('COM_THM_ORGANIZER_NAME').":"; ?></label>
                </div>
                <div class="thm_organizer_cat_data">
                    <input type="text" name="title" size="25" maxlength="100" value="<?php echo $this->title;?>" />
                </div>
            </div>
            <div id="thm_organizer_cat_ecat_desc_div">
                <div class="thm_organizer_cat_label">
                    <label for="description"><?php echo JText::_('COM_THM_ORGANIZER_DESC').":";?></label>
                </div>
                <div class="thm_organizer_cat_data">
                    <textarea name='description' rows='5' cols='35' id='description'><?php
                        echo $this->description;
                    ?></textarea>
                </div>
            </div>
            <div class="thm_organizer_cat_ecat_display_div">
                <div class="thm_organizer_cat_label">
                    <label for="global"><?php echo JText::_('COM_THM_ORGANIZER_CAT_GLOBAL');?></label>
                </div>
                <div class="thm_organizer_cat_data">
                    <table>
                        <tr>
                            <td><?php echo JText::_('COM_THM_ORGANIZER_YES');?></td>
                            <td>
                                <input class="thm_organizer_radio_button"
                                       type="radio" name="global" value="1"
                                       <?php if($this->global) echo 'checked="checked"';?> >

                            </td>
                        </tr>
                        <tr>
                            <td><?php echo JText::_('COM_THM_ORGANIZER_NO');?></td>
                            <td>
                                <input class="thm_organizer_radio_button"
                                       type="radio" name="global" value="0"
                                       <?php if(!$this->global) echo 'checked="checked"';?> >
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="thm_organizer_cat_ecat_display_div">
                <div class="thm_organizer_cat_label">
                    <label for="reserves"><?php echo JText::_('COM_THM_ORGANIZER_CAT_RESERVES');?></label>
                </div>
                <div class="thm_organizer_cat_data">
                    <table>
                        <tr>
                            <td><?php echo JText::_('COM_THM_ORGANIZER_YES');?></td>
                            <td>
                                <input class="thm_organizer_radio_button"
                                       type="radio" name="reserves" value="1"
                                       <?php if($this->reserves) echo 'checked="checked"';?> >

                            </td>
                        </tr>
                        <tr>
                            <td><?php echo JText::_('COM_THM_ORGANIZER_NO');?></td>
                            <td>
                                <input class="thm_organizer_radio_button"
                                       type="radio" name="reserves" value="0"
                                       <?php if(!$this->reserves) echo 'checked="checked"';?> >

                            </td>
                        </tr>
                    </table>
                     </div>
            </div>
        </div>
        <div id="thm_organizer_cat_ccat">
            <div id="thm_organizer_cat_ccat_name_div">
                <div class="thm_organizer_cat_label">
                    <label for="name"><?php echo JText::_('COM_THM_ORGANIZER_CAT_CONTENT_CATEGORY');?></label>
                </div>
                <div class="thm_organizer_cat_data">
                    <?php echo $this->contentCatBox;?>
                </div>
            </div>
            <div id="thm_organizer_cat_ccat_desc_div">
                <div class="thm_organizer_cat_label">
                    <label><?php echo JText::_('COM_THM_ORGANIZER_CAT_CONTENT_CATEGORY_DESC');?></label>
                </div>
                <div class="thm_organizer_cat_data" id="thm_organizer_cat_content_description">
                <?php foreach($this->contentCategories as $category): if($category['id'] == $this->contentCat): ?>
                        <?php echo $category['description']; break; ?>
                <?php endif; endforeach; ?>
                </div>
            </div>
            <div id="thm_organizer_cat_ccat_viewlevel_div">
                <div class="thm_organizer_cat_label">
                    <label><?php echo JText::_('COM_THM_ORGANIZER_CAT_CONTENT_CATEGORY_VIEW_LEVELS');?></label>
                </div>
                <div class="thm_organizer_cat_data" id="thm_organizer_cat_content_view_level">
                <?php foreach($this->contentCategories as $category): if($category['id'] == $this->contentCat): ?>
                        <?php echo $category['view_level']; break; ?>
                <?php endif; endforeach; ?>
                </div>
            </div>
            <div class="thm_organizer_cat_ccat_actions_div">
                <div class="thm_organizer_cat_label">
                    <label><?php echo JText::_('COM_THM_ORGANIZER_CAT_CONTENT_CATEGORY_PERM');?></label>
                </div>
                <div class="thm_organizer_cat_data" id="thm_organizer_cat_content_permissions">
                <?php foreach($this->contentCategories as $category): if($category['id'] == $this->contentCat): ?>
                        <?php echo $category['actions']; break; ?>
                <?php endif; endforeach; ?></div>
            </div>
        </div>
        <input type="hidden" name="id" value="<?php echo $this->id; ?>" />
        <input type="hidden" name="task" value="" />
    </form>
</div>
