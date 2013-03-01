<?php
/**
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view assets default body
 * @description THM_Curriculum component admin view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
switch ($item->asset_type_id)
{
	case 1:
		$task = 'course.edit';
		break;
	case 2:
		$task = 'coursepool.edit';
		break;
	default:
		$task = 'dummy.edit';
		break;
}
foreach ($this->items as $i => $item)
{
?>
<tr class="row<?php echo $i % 2; ?>">
	<td align="center"><?php echo JHtml::_('grid.id', $i, $item->asset_id) ?></td>
	<td align="center"><?php echo $item->asset_id; ?></td>
	<td align="center"><?php echo $item->lsf_course_id; ?></td>
	<td align="center"><?php echo $item->his_course_code; ?></td>
	<td align="center"><?php echo $item->lsf_course_code; ?></td>
	<td>
		<a href="index.php?option=com_thm_organizer&task=<?php echo "$task&id=$item->asset_id"; ?>">
			<?php echo $item->title_de; ?>
		</a>
	</td>
	<td><?php echo $item->short_title_de; ?></td>
	<td align="center"><?php echo $item->coursetype; ?></td>
</tr>
<?php
}
