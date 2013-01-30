<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view assets default body
 * @description THM_Curriculum component admin view
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;
?>

<?php foreach ($this->items as $i => $item)
{
	?>
<tr class="row<?php echo $i % 2; ?>">
	<td align="center"><?php echo JHtml::_('grid.id', $i, $item->asset_id) ?>
	</td>
	<td align="center"><?php echo $item->asset_id; ?></td>
	<td align="center"><?php echo $item->lsf_course_id; ?></td>
	<td align="center"><?php echo $item->his_course_code; ?></td>
	<td align="center"><?php echo $item->lsf_course_code; ?></td>
	<td><?php if ($item->asset_type_id == 1)
	{
		?> <a
		href="index.php?option=com_thm_organizer&task=course.edit&id=<?php echo $item->asset_id; ?>">
		<?php echo $item->title_de; ?>
	</a> <?php
}
	elseif ($item->asset_type_id == 2)
	{
		?> <a
		href="index.php?option=com_thm_organizer&task=coursepool.edit&id=<?php echo $item->asset_id; ?>">
		<?php echo $item->title_de; ?>
	</a> <?php
	}
	else
	{
		?> <a
		href="index.php?option=com_thm_organizer&task=dummy.edit&id=<?php echo $item->asset_id; ?>">
		<?php echo $item->title_de; ?>
	</a> <?php
	}
	?>
	</td>
	<td><?php echo $item->short_title_de; ?></td>
	<td align="center"><?php echo $item->coursetype; ?></td>
</tr>
<?php
}
