<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view semesters default body
 * @description THM_Curriculum component admin view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
?>
<?php
foreach ($this->items as $i => $item)
{
?>
<tr class="row<?php echo $i % 2; ?>">

	<td><?php echo JHtml::_('grid.id', $i, $item->id); ?>
	</td>
	<td><a
		href="index.php?option=com_thm_organizer&task=semester.edit&id=<?php echo $item->id;?>">
		<?php echo $item->name; ?>
	</td>
	<td><a
		href="index.php?option=com_thm_organizer&task=semester.edit&id=<?php echo $item->id;?>">
		<?php echo $item->short_title_de; ?>
	</td>
	<td><a
		href="index.php?option=com_thm_organizer&task=semester.edit&id=<?php echo $item->id;?>">
		<?php echo $item->short_title_en; ?>
	</td>
	<td align="center"><?php echo $item->id; ?></td>
</tr>

<?php
}
