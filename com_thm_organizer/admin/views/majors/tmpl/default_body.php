<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view majors default body
 * @description THM_Curriculum component admin view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

foreach ($this->items as $i => $item)
{
	?>
<tr class="row<?php echo $i % 2; ?>">

	<td align="center"><?php echo JHtml::_('grid.id', $i, $item->id) ?></td>

	<td><?php echo $item->degree; ?></td>
	<td><a
		href="index.php?option=com_thm_organizer&task=major.edit&id=<?php echo $item->id; ?>">
		<?php echo $item->subject; ?>
	</a></td>
	<td><a
		href="index.php?option=com_thm_organizer&task=major.edit&id=<?php echo $item->id; ?>">
		<?php echo $item->po; ?>
	</a></td>
	<?php
	if (!empty($item->lsf_object) && !empty($item->lsf_study_path) && !empty($item->lsf_degree))
	{
		?>
	<td align="center"><img
		src="templates/thmstylebackend/images/admin/tick.png" /></td>
	<?php
	}
	else
	{
	?>
	<td></td>
	<?php
	}
	?>
	<td><a
		title="<?php echo JText::_('com_thm_organizer_SHOW_CONTENT'); ?>"
		href="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=mappings&id=' . $item->id) ?>"><img
			src="components/com_thm_organizer/assets/images/list.png" /> </a></td>
	<td><?php echo $item->id; ?></td>

</tr>

<?php
}
