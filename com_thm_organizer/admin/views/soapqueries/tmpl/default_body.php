<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view soapqueries default body
 * @description THM_Curriculum component admin view
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;
?>
<?php
foreach ($this->items as $i => $item)
{
	?>
<tr class="row<?php echo $i % 2; ?>">
	<td><?php echo JHtml::_('grid.id', $i, $item->id); ?>
	</td>
	<td><a
		href="<?php echo JRoute::_('index.php?option=com_thm_organizer&task=soapquery.edit&id=' . $item->id); ?>">
			<?php echo $item->name; ?>
	</a>
	</td>
	<td align="center"><?php echo $item->lsf_object; ?></td>
	<td align="center"><?php echo $item->lsf_study_path; ?></td>
	<td align="center"><?php echo $item->lsf_degree; ?></td>
	<td align="center"><?php echo $item->lsf_pversion; ?></td>
	<td align="center"><?php echo $item->id; ?></td>
</tr>

<?php
}
