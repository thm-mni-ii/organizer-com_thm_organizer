<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        schedule manager default template
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined("_JEXEC") or die;
$orderby = $this->escape($this->state->get('list.ordering'));
$direction = $this->escape($this->state->get('list.direction'));
$search = ($this->state->get('filter.search'))?
        $this->escape($this->state->get('filter.search')) : JText::_('COM_THM_ORGANIZER_SEARCH_CRITERIA');
?>
<form action="<?php echo JRoute::_("index.php?option=com_thm_organizer"); ?>"
      enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
    <fieldset id="filter-bar">
        <div class="filter-search fltlft">
            <input type="text" name="filter_search" id="filter_search" value="<?php echo $search; ?>"
                   title="<?php echo JText::_('COM_THM_ORGANIZER_SEARCH_DESC'); ?>" />
            <button type="submit"><?php echo JText::_('COM_THM_ORGANIZER_SEARCH'); ?></button>
            <button type="button" onclick="document.id('filter_search').value='';this.form.submit();">
                <?php echo JText::_('COM_THM_ORGANIZER_SEARCH_CLEAR'); ?>
            </button>
        </div>
        <div class="filter-select fltrt">
            <select name="filter_institution" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_RMM_SEARCH_INSTITUTIONS'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_RMM_ALL_INSTITUTIONS'); ?></option>
                <?php echo JHtml::_('select.options', $this->institutions, 'id', 'name', $this->state->get('filter.institution'));?>
            </select>
            <?php if(count($this->campuses)): ?>
            <select name="filter_campus" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_RMM_SEARCH_CAMPUSES'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_RMM_ALL_CAMPUSES'); ?></option>
                <?php echo JHtml::_('select.options', $this->campuses, 'id', 'name', $this->state->get('filter.campus'));?>
            </select>
            <?php endif; if(count($this->buildings)): ?>
            <select name="filter_building" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_RMM_SEARCH_BUILDINGS'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_RMM_ALL_BUILDINGS'); ?></option>
                <?php echo JHtml::_('select.options', $this->buildings, 'id', 'name', $this->state->get('filter.building'));?>
            </select>
            <?php endif; ?>
            <select name="filter_type" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_RMM_SEARCH_TYPES'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_RMM_ALL_TYPES'); ?></option>
                <?php echo JHtml::_('select.options', $this->types, 'id', 'name', $this->state->get('filter.type'));?>
            </select>
            <?php if(count($this->details)): ?>
            <select name="filter_detail" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_RMM_SEARCH_DETAILS'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_RMM_ALL_DETAILS'); ?></option>
                <?php echo JHtml::_('select.options', $this->details, 'id', 'name', $this->state->get('filter.detail'));?>
            </select>
            <?php endif; ?>
            <!-- to do put details here dependant upon selected type -->
        </div>
    </fieldset>
    <div class="clr"> </div>
<?php if(!empty($this->rooms)) { $k = 0;?>
    <div>
        <table class="adminlist" cellpadding="0">
            <colgroup>
                <col id="thm_organizer_check_column" />
                <col id="thm_organizer_rmm_name_column" />
                <col id="thm_organizer_rmm_campus_column" />
                <col id="thm_organizer_rmm_building_column" />
                <col id="thm_organizer_rmm_floor_column" />
                <col id="thm_organizer_rmm_description_column" />
            </colgroup>
            <thead>
                <tr>
                    <th class="thm_organizer_sch_th" ></th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_NAME', 'r.name', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_TYPE', 't.name', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_DESC', 'det.name', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_INSTITUTION', 'i.name', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_CAMPUS', 'c.name', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_BUILDING', 'b.name', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JText::_('COM_THM_ORGANIZER_FLOOR'); ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="9">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
            <?php foreach($this->rooms as $k => $room){ ?>
                <tr class="row<?php echo $k % 2;?>">
                    <td><?php echo JHtml::_('grid.id', $k, $schedule->id); ?></td>
                    <td>
                        <a href="<?php echo $room->url; ?>">
                            <?php echo $room->name; ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $room->url; ?>">
                            <?php echo JText::_($room->type); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $room->url; ?>">
                            <?php echo JText::_($room->detail); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $room->url; ?>">
                            <?php echo JText::_($room->institution); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $room->url; ?>">
                            <?php echo $room->campus; ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $room->url; ?>">
                            <?php echo $room->building; ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $room->url; ?>">
                            <?php echo $room->floor; ?>
                        </a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $orderby; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $direction; ?>" />
    <input type="hidden" name="view" value="room_manager" />
    <input type="hidden" name="campus" value="<?php echo $this->state->get('filter.campus'); ?>" />
    <input type="hidden" name="building" value="<?php echo $this->state->get('filter.building'); ?>" />
    <input type="hidden" name="description" value="<?php echo $this->state->get('filter.description'); ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>