<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view degree program edit view edit layout
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
JHtml::_('behavior.tooltip');
if (!empty($this->children))
{
    $maxOrdering = max(array_keys($this->children));
}
$rawPoolURL = 'index.php?option=com_thm_organizer&view=pool_manager';
$poolURL = JRoute::_($rawPoolURL, false);
$rawSubjectURL = 'index.php?option=com_thm_organizer&view=subject_manager';
$subjectURL = JRoute::_($rawSubjectURL, false);
?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=program_edit&id=' . (int) $this->item->id); ?>"
	  method="post" name="adminForm" id="modulmapping-form">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_THM_ORGANIZER_PRM_PROPERTIES'); ?></legend>
		<ul class="adminformlist">
<?php
foreach ($this->form->getFieldset() as $field)
{
	echo '<li>';
	echo $field->label;
	echo $field->input;
	echo '</li>';
}
?>
		</ul>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_THM_ORGANIZER_CHILDREN'); ?></legend>
        <div class="thm_organizer_children">
<?php
if (!empty($this->children))
{
?>
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
            if (!empty($this->children[$index]['poolID']))
            {
                $rawEditURL = 'index.php?option=com_thm_organizer&view=pool_edit&id=' . $this->children[$index]['poolID'];
            }
            else
            {
                $rawEditURL = 'index.php?option=com_thm_organizer&view=subject_edit&id=' . $this->children[$index]['subjectID'];
            }
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
<?php
}
else echo "<span class='thm_organizer_no_children'>" . JText::_('COM_THM_ORGANIZER_NO_CHILDREN') . "</span>";
?>
        </div>
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
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
