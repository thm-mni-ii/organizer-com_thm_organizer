<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view degree program edit default layout
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
JHtml::_('behavior.tooltip');
$maxOrdering = max(array_keys($this->children));
?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=degree_program_edit&id=' . (int) $this->item->id); ?>"
	  method="post" name="adminForm" id="modulmapping-form">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_THM_ORGANIZER_DGP_PROPERTIES'); ?></legend>
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
		<legend><?php echo JText::_('COM_THM_ORGANIZER_DGP_CHILDREN'); ?></legend>
        <div class="immediate-children">
            <table class="adminlist">
                <thead>
                    <tr>
                        <th>
                            <?php echo JText::_('COM_THM_ORGANIZER_NAME'); ?>
                        </th>
                        <th width="10%">
                            <?php echo JText::_('JGRID_HEADING_ORDERING'); ?>
                            <a href="submitbutton('degree_program.apply')" class="saveorder"
                               title="<?php echo JText::_('JLIB_HTML_SAVE_ORDER'); ?>">
                            </a>
                        </th>
                        <th width="10%">
                            <?php echo JText::_('JACTION_DELETE'); ?>
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
                    <tr class="row<?php echo $index % 2; ?>">
                        <td id="child<?php echo $index; ?>name">
                            <a id="child<?php echo $index; ?>link"
                                href="<?php echo $editURL; ?>">
                                <?php echo $name ?>
                            </a>
                        </td>
                        <td class="order">
                            <a class="jgrid" href="javascript:void(0);" onclick="return listItemTask('cb2','categories.orderup')" title="Move Up">
                                <span class="state uparrow" onclick="return listItemTask('cb2','categories.orderup')">
                                    <span class="text">Move Up</span>
                                </span>
                            </a>
                            <a class="jgrid" href="javascript:void(0);" onclick="return listItemTask('cb2','categories.orderdown')" title="Move Down">
                                <span class="state downarrow">
                                    <span class="text">Move Down</span>
                                </span>
                            </a>
                            <input type="text" name="order[]" class="text-area-order" size="2"
                                   value="<?php echo $index;?>" />
                            <input type="hidden"
                                   name="child<?php echo $index; ?>"
                                   value="<?php echo $this->children[$index]['id'];?>" />
                        </td>
                        <td class="center">
                            <?php echo 'garbage can'; ?>
                        </td>
                    </tr>
<?php
}
?>
                </tbody>
            </table>
        </div>
    </fieldset>
	<div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
