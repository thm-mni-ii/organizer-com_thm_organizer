<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view assets default head
 * @description THM_Curriculum component admin view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';


JHTML::_('behavior.modal', 'a.modal-button');
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

	<div class="filter-select fltrt">
		<select name="filter_type" class="inputbox"
			onchange="this.form.submit()">
			<option value="">
				<?php echo JText::_('Type'); ?>
			</option>
			<?php echo JHtml::_('select.options', $this->f_levels, 'value', 'text', $this->state->get('filter.type')); ?>
		</select>

	</div>
</fieldset>
<tr>

	<th width="3%"><input type="checkbox" name="toggle" value=""
		onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>

	<th width="5%"><?php echo JHTML::_('grid.sort',
			JText::_('com_thm_organizer_SUBMENU_ASSETS_CURRICULUM_ID'), 'asset_id', $listDirn, $listOrder
	);?>
	</th>
	<th width="5%"><?php echo JHTML::_('grid.sort',
			JText::_('com_thm_organizer_SUBMENU_ASSETS_CURRICULUM_COURSEID'), 'lsf_course_id', $listDirn, $listOrder
	);?>
	</th>
	<th width="5%"><?php echo JHTML::_('grid.sort',
			JText::_('com_thm_organizer_SUBMENU_ASSETS_CURRICULUM_COURSECODE_HIS'), 'his_course_code', $listDirn, $listOrder
	);?>
	</th>
	<th width="5%"><?php echo JHTML::_('grid.sort',
			JText::_('com_thm_organizer_SUBMENU_ASSETS_CURRICULUM_COURSECODE_CURRICULUM'), 'lsf_course_code', $listDirn, $listOrder
	);?>
	</th>
	<th width="50%"><?php echo JHTML::_('grid.sort',
			JText::_('com_thm_organizer_SUBMENU_ASSETS_CURRICULUM_TITLE_DE'), 'title_de', $listDirn, $listOrder
	);?>
	</th>
	<th width="20%"><?php echo JHTML::_('grid.sort',
			JText::_('com_thm_organizer_SUBMENU_ASSETS_CURRICULUM_SHORT_TITLE_DE'), 'short_title_de', $listDirn, $listOrder
	);?>
	</th>
	<th width="10%"><?php echo JHTML::_('grid.sort',
			JText::_('com_thm_organizer_SUBMENU_ASSETS_CURRICULUM_ASSET_TYPE'), 'asset_type_id', $listDirn, $listOrder
	);?>
	</th>
</tr>
