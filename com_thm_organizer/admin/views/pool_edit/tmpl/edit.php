<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view pool edit template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('jquery.jquery');
$poolID = empty($this->item->id)? 0 : $this->item->id;
if (!empty($this->children))
{
    $maxOrdering = max(array_keys($this->children));
}
$rawPoolURL = 'index.php?option=com_thm_organizer&view=pool_manager';
$poolURL = JRoute::_($rawPoolURL, false);
$rawSubjectURL = 'index.php?option=com_thm_organizer&view=subject_manager';
$subjectURL = JRoute::_($rawSubjectURL, false);
?>
<script type="text/javascript">
var jq = jQuery.noConflict();
jq(document).ready(function(){
    jq('#jformprogramID').change(function(){
        var selectedPrograms = jq('#jformprogramID').val();
        if (selectedPrograms === null)
        {
            selectedPrograms = '';
        }
        else
        {
            selectedPrograms = selectedPrograms.join(',');
        }
        var oldSelectedParents = jq('#jformparentID').val();
        if (jq.inArray('-1', selectedPrograms) != '-1'){
            jq("#jformprogramID").find('option').removeAttr("selected");
            return false;
        }
        var poolUrl = "<?php echo $this->baseurl; ?>/index.php?option=com_thm_organizer";
        poolUrl += "&view=pool_ajax&format=raw&task=poolDegreeOptions";
        poolUrl += "&ownID=<?php echo $this->form->getValue('id'); ?>";
        poolUrl += "&programID=" + selectedPrograms;
        jq.get(poolUrl, function(options){
            jq('#jformparentID').html(options);
            var newSelectedParents = jq('#jformparentID').val();
            var selectedParents = new Array();
            if (newSelectedParents !== null && newSelectedParents.length)
            {
                if (oldSelectedParents !== null && oldSelectedParents.length)
                {
                    selectedParents = jq.merge(newSelectedParents, oldSelectedParents);
                }
                else
                {
                    selectedParents = newSelectedParents;
                }
            }
            else if (oldSelectedParents !== null && oldSelectedParents.length)
            {
                selectedParents = oldSelectedParents;
            }
            jq('#jformparentID').val(selectedParents);
        });
    });
});
</script>
<form action="<?php echo JRoute::_("index.php?option=com_thm_organizer&view=pool_edit&id=$poolID"); ?>"
      method="post" name="adminForm" id="modul-form">
	<fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_POM_PROPERTIES_DE'); ?></legend>
		<ul class="adminformlist">
            <li>
                <?php echo $this->form->getLabel('name_de'); ?>
                <?php echo $this->form->getInput('name_de'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('short_name_de'); ?>
                <?php echo $this->form->getInput('short_name_de'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('abbreviation_de'); ?>
                <?php echo $this->form->getInput('abbreviation_de'); ?>
            </li>
        </ul>
	</fieldset>
	<fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_POM_PROPERTIES_EN'); ?></legend>
		<ul class="adminformlist">
            <li>
                <?php echo $this->form->getLabel('name_en'); ?>
                <?php echo $this->form->getInput('name_en'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('short_name_en'); ?>
                <?php echo $this->form->getInput('short_name_en'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('abbreviation_en'); ?>
                <?php echo $this->form->getInput('abbreviation_en'); ?>
            </li>
        </ul>
	</fieldset>
	<fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_POM_PROPERTIES'); ?></legend>
		<ul class="adminformlist">
            <li>
                <?php echo $this->form->getLabel('lsfID'); ?>
                <?php echo $this->form->getInput('lsfID'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('hisID'); ?>
                <?php echo $this->form->getInput('hisID'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('externalID'); ?>
                <?php echo $this->form->getInput('externalID'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('minCrP'); ?>
                <?php echo $this->form->getInput('minCrP'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('maxCrP'); ?>
                <?php echo $this->form->getInput('maxCrP'); ?>
            </li>
        </ul>
	</fieldset>
	<fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_POM_PROPERTIES_MAPPING'); ?></legend>
		<ul class="adminformlist">
            <li>
                <?php echo $this->form->getLabel('programID'); ?>
                <?php echo $this->form->getInput('programID'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('parentID'); ?>
                <?php echo $this->form->getInput('parentID'); ?>
            </li>
        </ul>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_THM_ORGANIZER_CHILDREN'); ?></legend>
<?php
if (!empty($this->children))
{
?>
        <div class="thm_organizer_children">
            <table id="childList" class="adminlist">
                <thead>
                    <tr>
                        <th>
                            <?php echo JText::_('COM_THM_ORGANIZER_NAME'); ?>
                        </th>
                        <th width="20%">
                            <?php echo JText::_('COM_THM_ORGANIZER_CHILD_ORDER'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
<?php
    for ($index = 1; $index <= $maxOrdering; $index++)
    {
        if (isset($this->children[$index]))
        {
            $name = $this->children[$index]['name'];
            $id = $this->children[$index]['id'];
            $rawEditURL = 'index.php?option=com_thm_organizer&view=pool_edit&id=' . $this->children[$index]['poolID'];
            $editURL = JRoute::_($rawEditURL, false);
        }
        else
        {
            $editURL = $name = $id = '';
        }
?>
                    <tr id="childRow<?php echo $index; ?>"
                        class="row<?php echo $index % 2; ?>">
                        <td>
                            <a id="child<?php echo $index; ?>link"
                                href="<?php echo $editURL; ?>">
                                <span id="child<?php echo $index; ?>name">
                                    <?php echo $name ?>
                                </span>
                            </a>
                            <input type="hidden"
                                   name="child<?php echo $index; ?>"
                                   id="child<?php echo $index; ?>"
                                   value="<?php echo $id;?>" />
                        </td>
                        <td class="order">
                            <span>
                                <a class="jgrid" href="javascript:void(0);"
                                   onclick="moveUp('<?php echo $index; ?>')" title="Move Up">
                                    <span class="state uparrow">
                                        <span class="text"><?php echo JText::_('JLIB_HTML_MOVE_UP'); ?></span>
                                    </span>
                                </a>
                            </span>
                            <span>
                                <a class="jgrid" href="javascript:void(0);"
                                   onclick="moveDown('<?php echo $index; ?>')" title="Move Down">
                                    <span class="state downarrow">
                                        <span class="text"><?php echo JText::_('JLIB_HTML_MOVE_DOWN'); ?></span>
                                    </span>
                                </a>
                            </span>
							<input type="text"
                                   name="child<?php echo $index; ?>order"
                                   id="child<?php echo $index; ?>order"
                                   size="2" value="<?php echo $index;?>" class="text-area-order"
                                   onChange="order(<?php echo $index; ?>)"/>
                            <a class="thm_organizer_delete_child" href="javascript:void(0);"
                               title="<?php echo JText::_('COM_THM_ORGANIZER_MAPPING_DELETE'); ?>"
                               onClick="remove(<?php echo $index; ?>)">
                            </a>
						</td>
                    </tr>
<?php
    }
?>
                </tbody>
            </table>
        </div>
<?php
}
?>
        <div class="thm_organizer_pools">
            <a href="<?php echo $poolURL; ?>">
                <?php echo JText::_('COM_THM_ORGANIZER_ADD_POOLS'); ?>
            </a>
        </div>
        <div class="thm_organizer_subjects">
            <a href="<?php echo $subjectURL; ?>">
                <?php echo JText::_('COM_THM_ORGANIZER_ADD_SUBJECTS'); ?>
            </a>
        </div>
	</fieldset>
	<div>
        <?php echo $this->form->getInput('id'); ?>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
