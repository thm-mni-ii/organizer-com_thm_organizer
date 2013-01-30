<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view degrees default body
 * @description THM_Curriculum component admin view
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die('Restricted access');

foreach ($this->items as $i => $item)
{
	?>
<tr class="row<?php echo $i % 2; ?>">
	<td align="center"><?php echo JHtml::_('grid.id', $i, $item->id);?></td>
	<td><a
		href="index.php?option=com_thm_organizer&task=degree.edit&id==<?php echo $item->id;?>">
		<?php echo $item->name;?>
	</a></td>
	<td><?php echo $item->id;?></td>
</tr>
<?php
}
