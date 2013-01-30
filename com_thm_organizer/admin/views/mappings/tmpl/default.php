<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view mappings default
 * @description THM_Organizer component admin view
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Load tooltip behavior
JHtml::_('behavior.tooltip');

$user = JFactory::getUser();
$userId = $user->get('id');
$extension = $this->escape($this->state->get('filter.extension'));

$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');

$ordering = ($listOrder == 'a.lft');
$saveOrder = ($listOrder == 'a.lft');
?>

<form
	action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=mappings&id=' . JRequest::getVar('id')) ?>"
	method="post" name="adminForm">

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

		<div class="filter-select">
			<select name="filter_level" class="inputbox"
				onchange="this.form.submit()">
				<option value="">

					<?php echo JText::_('COM_THM_ORGANIZER_OPTION_SELECT_LEVEL'); ?>
				</option>
				<?php echo JHtml::_('select.options', $this->f_levels, 'value', 'text', $this->state->get('filter.level')); ?>
			</select> <select name="filter_published" class="inputbox"
				onchange="this.form.submit()">
				<option value="">

					<?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?>
				</option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text',
						$this->state->get('filter.published'), true
						); ?>
			</select>


		</div>
	</fieldset>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="20"><input type="checkbox" name="toggle" value=""
					onclick="checkAll(<?php echo count($this->assets) ?>)" />
				</th>

				<th><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUBMENU_MODULMAPPINGS_NAME'), 'title_de', $listDirn, $listOrder); ?>
				</th>
				<th width="10%"><?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'a.lft', $listDirn, $listOrder); ?>

					<?php if ($saveOrder)
					{
						echo JHtml::_('grid.order', $this->assets, 'filesave.png', 'mappings.saveorder');
}
					else
					{

					}
					?>
				</th>
				<th><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUBMENU_MODULMAPPINGS_DEPTH'), 'depth', $listDirn, $listOrder); ?>
				
				<th width="5%"><?php echo JHtml::_('grid.sort', 'JSTATUS', 'published', $listDirn, $listOrder); ?>
				</th>
				<th width="5%"></th>

				<th><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUBMENU_MODULMAPPINGS_MIN_CRP'),
						'min_creditpoints', $listDirn, $listOrder
				); ?></th>
				<th><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUBMENU_MODULMAPPINGS_MAX_CRP'),
						'max_creditpoints', $listDirn, $listOrder
				); ?></th>
				<th><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUBMENU_MODULMAPPINGS_TYPE'),
						'asset_type', $listDirn, $listOrder
				); ?></th>
				<th><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUBMENU_MODULMAPPINGS_SEMESTER'),
						'semester_name', $listDirn, $listOrder
				); ?></th>

				<th><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUBMENU_MODULMAPPINGS_COLOR'), 'color', $listDirn, $listOrder); ?>
				</th>
				<th><?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_SUBMENU_MODULMAPPINGS_ID'), 'asset_id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$originalOrders = array();

			$k = 0;
			$i = 0;
			$n = count($this->assets);
			$rows = $this->assets;

			foreach ($rows as $item)
			{
				$checked = JHTML::_('grid.checkedout', $item, $i);
				$orderkey = array_search($item->asset_id, $this->ordering[$item->parent_id]);
				?>
			<tr class="row<?php echo $i % 2; ?>">

				<td align="center"><?php echo JHtml::_('grid.id', $i, $item->asset_id); ?>
				</td>



				<td><a
					href="index.php?option=com_thm_organizer&task=mapping.edit&id=<?php echo $item->asset_id; ?>">
						<?php echo $item->treename; ?>
				
				</td>
				<td class="order"><?php if ($saveOrder)
				{
					?> <span><?php echo $this->pagination->orderUpIcon(
							$i, isset($this->ordering[$item->parent_id][$orderkey - 1]),
							'mappings.orderup', 'JLIB_HTML_MOVE_UP', $ordering
					); ?> </span> <span><?php echo $this->pagination->orderDownIcon(
							$i, $this->pagination->total,
							isset($this->ordering[$item->parent_id][$orderkey + 1]), 'mappings.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering
					); ?> </span>
					<?php $disabled = $saveOrder ? '' : 'disabled="disabled"'; ?>
					<input type="text" name="order[]" size="5"
					value="<?php echo $item->ordering; ?>" <?php echo $disabled ?>
					class="text-area-order" /> <?php $originalOrders[] = $orderkey + 1; ?>
					<?php
}
				else
				{ ?> <?php echo $item->ordering; ?> <?php
				}
				?>
				</td>
				<td><?php echo $item->depth; ?></td>
				<td class="center"><?php echo JHtml::_('jgrid.published', $item->published, $i, 'mapping.'); ?>
				</td>

				<?php
				$task = null;

				if ($item->asset_type_id == 1)
				{
					$task = "course";
				}
				elseif ($item->asset_type_id == 2)
				{
					$task = "coursepool";
				}
				else
				{
					$task = "dummy";
				}
				?>

				<td class="center"><a title="Show Asset"
					href="index.php?option=com_thm_organizer&task=<?php echo $task; ?>.edit&id=
					<?php echo $item->asset; ?>"><img
						src="/administrator/components/com_thm_organizer/assets/images/asset_config.png">
				</a>
				</td>
				<td><?php echo $item->min_creditpoints; ?></td>
				<td><?php echo $item->max_creditpoints; ?></td>
				<td><?php echo $item->asset_type; ?></td>
				<td><?php echo $item->semester_id; ?></td>
				<td><a style="width:30px;height:20px;display:block;background-color: #<?php echo $item->color; ?>"
				href="index.php?option=com_thm_organizer&task=color.edit&id=
				<?php echo $item->color_id; ?>" class="rahmen"><div></div>
				</td>
				<td><?php echo $item->asset_id; ?></td>
			</tr>
			<?php
			$i++;
			}
			?>

		</tbody>
	</table>

	<div>
		<tr>
			<div align="center">

				<?php echo $this->pagination->getListFooter(); ?>
			</div>
		</tr>

		<input type="hidden" name="task" value="" /> <input type="hidden"
			name="boxchecked" value="0" /> <input type="hidden"
			name="filter_order" value="<?php echo $listOrder; ?>" /> <input
			type="hidden" name="filter_order_Dir"
			value="<?php echo $listDirn; ?>" /> <input type="hidden"
			name="original_order_values"
			value="<?php echo implode($originalOrders, ','); ?>" />
		<?php echo JHtml::_('form.token'); ?>

	</div>
</form>
