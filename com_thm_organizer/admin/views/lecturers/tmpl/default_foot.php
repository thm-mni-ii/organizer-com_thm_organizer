<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view lectures default foot
 * @description THM_Curriculum component admin view
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>
<tr>
	<td colspan="5"><?php echo $this->pagination->getListFooter(); ?></td>
</tr>

<input type="hidden"
	name="task" value="" />
<input type="hidden" name="boxchecked"
	value="0" />
<input
	type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
<input
	type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />

<?php echo JHtml::_('form.token');
