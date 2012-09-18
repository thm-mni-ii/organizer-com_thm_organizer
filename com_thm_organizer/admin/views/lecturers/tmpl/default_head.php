<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view lectures default head
 * @description THM_Curriculum component admin view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>

<fieldset id="filter-bar">
	<div class="filter-search fltlft">
		<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
		</label> <input type="text" name="filter_search" id="filter_search"
			value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
			title="<?php echo JText::_('COM_CATEGORIES_ITEMS_SEARCH_FILTER'); ?>" />
		<button type="submit">
			<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
		</button>
		<button type="button"
			onclick="document.id('filter_search').value='';this.form.submit();">
			<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
		</button>
	</div>


</fieldset>
<tr>

	<th width="5%"><input type="checkbox" name="toggle" value=""
		onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>
	<th width="20%"><?php echo JHTML::_('grid.sort', JText::_('com_thm_organizer_SUBMENU_LECTURERS_USERID'), 'userid', $listDirn, $listOrder); ?>
	</th>
	<th width="35%"><?php echo JHTML::_('grid.sort', JText::_('com_thm_organizer_SUBMENU_LECTURERS_SURNAME'), 'surname', $listDirn, $listOrder); ?>
	</th>
	<th width="35%"><?php echo JHTML::_('grid.sort', JText::_('com_thm_organizer_SUBMENU_LECTURERS_FORENAME'), 'forename', $listDirn, $listOrder); ?>
	</th>
	<th width="5%"><?php echo JHTML::_('grid.sort', JText::_('com_thm_organizer_SUBMENU_LECTURERS_ID'), 'id', $listDirn, $listOrder); ?>
	</th>
</tr>
