<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view pool_manager default
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=pool_manager&id=' . JRequest::getVar('id')) ?>"
	method="post" name="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search">
				<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
			</label>
			<input type="text" name="filter_search" id="filter_search"
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
	<table class="adminlist">
		<thead>
			<tr>
				<th width="2%"/>
				<th>
                    <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_NAME'), 'name', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
                    <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_POM_LSFID_TITLE'), 'lsfID', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
                    <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_POM_HISID_TITLE'), 'hisID', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
                    <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_POM_EXTERNALID_TITLE'), 'externalID', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
                    <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_POM_MAXCRP_TITLE'), 'maxCrP', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
                    <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_POM_MINCRP_TITLE'), 'minCrP', $listDirn, $listOrder); ?>
				</th>
				<th>
                    <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_POM_FIELD_TITLE'), 'field', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tbody>
<?php
$i = 0;
foreach ($this->pools as $pool)
{
?>
			<tr class="row<?php echo $i % 2; ?>">
				<td align="center">
                    <?php echo JHtml::_('grid.id', $i, $pool->id); ?>
				</td>
				<td>
                    <a href="index.php?option=com_thm_organizer&view=pool_edit&layout=edit&id=<?php echo $pool->id; ?>">
						<?php echo $pool->name; ?>
                    </a>
				</td>
				<td>
                    <a href="index.php?option=com_thm_organizer&view=pool_edit&layout=edit&id=<?php echo $pool->id; ?>">
						<?php echo $pool->lsfID; ?>
                    </a>
				</td>
				<td>
                    <a href="index.php?option=com_thm_organizer&view=pool_edit&layout=edit&id=<?php echo $pool->id; ?>">
						<?php echo $pool->hisID; ?>
                    </a>
				</td>
				<td>
                    <a href="index.php?option=com_thm_organizer&view=pool_edit&layout=edit&id=<?php echo $pool->id; ?>">
						<?php echo $pool->externalID; ?>
                    </a>
				</td>
				<td>
                    <a href="index.php?option=com_thm_organizer&view=pool_edit&layout=edit&id=<?php echo $pool->id; ?>">
						<?php echo $pool->maxCrP; ?>
                    </a>
				</td>
				<td>
                    <a href="index.php?option=com_thm_organizer&view=pool_edit&layout=edit&id=<?php echo $pool->id; ?>">
						<?php echo $pool->minCrP; ?>
                    </a>
				</td>
				<td>
                    <a href="index.php?option=com_thm_organizer&view=pool_edit&layout=edit&id=<?php echo $pool->id; ?>">
						<?php echo $pool->field; ?>
                    </a>
				</td>
			</tr>
<?php
    $i++;
}
?>
		</tbody>
	</table>
	<div>
		<input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
