<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        schedule manager default template
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined("_JEXEC") or die;
$search = ($this->state->get('filter.search'))?
        $this->escape($this->state->get('filter.search')) : JText::_('COM_THM_ORGANIZER_SEARCH_CRITERIA');
?>

<?php // filter ?>

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
        	<?php if (count($this->departments)): ?>
            <select name="filter_department" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_TRM_SEARCH_DEPARTMENTS'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_TRM_ALL_DEPARTMENTS'); ?></option>
                <?php echo JHtml::_('select.options', $this->departments, 'id', 'name', $this->state->get('filter.department'));?>
            </select>
            <?php endif; if(count($this->campuses)): ?>
            <select name="filter_campus" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_RMM_SEARCH_CAMPUSES'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_RMM_ALL_CAMPUSES'); ?></option>
                <?php echo JHtml::_('select.options', $this->campuses, 'name', 'name', $this->state->get('filter.campus'));?>
            </select>
            <?php endif; if(count($this->institutions)): ?>
            <select name="filter_institution" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_RMM_SEARCH_INSTITUTIONS'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_RMM_ALL_INSTITUTIONS'); ?></option>
                <?php echo JHtml::_('select.options', $this->institutions, 'name', 'name', $this->state->get('filter.institution'));?>
            </select>
            <?php endif; ?>
        </div>
    </fieldset>
    
    <div class="clr"> </div>
	
    
<?php // table ?>    

<?php if(!empty($this->teachers)) { $k = 0;?>
    <div>
        <table class="adminlist" cellpadding="0">
            <colgroup>
                <col id="thm_organizer_check_column" />
                <col id="thm_organizer_rmm_gpuntis_id_column" />
                <col id="thm_organizer_rmm_name_column" />
                <col id="thm_organizer_rmm_username_column" />
                <col id="thm_organizer_rmm_department_column" />
                <col id="thm_organizer_rmm_campus_column" />
                <col id="thm_organizer_rmm_institution_column" />
            </colgroup>
            <thead>
                <tr>
                    <th class="thm_organizer_sch_th" ></th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_RM_GPUNTISID', 't.gpuntisID', $this->direction, $this->orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_NAME', 't.name', $this->direction, $this->orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_USER_NAME', 't.username', $this->direction, $this->orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_DP', 'd.department', $this->direction, $this->orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_CAMPUS', 'd.campus', $this->direction, $this->orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_INSTITUTION', 'd.institution', $this->direction, $this->orderby); ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="7">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
            <?php foreach($this->teachers as $k => $teacher){ ?>
                <tr class="row<?php echo $k % 2;?>">
                    <td><?php echo JHtml::_('grid.id', $k, $teacher->id); ?></td>
                     <td>
                        <a href="<?php echo $teacher->url; ?>">
                            <?php echo JText::_($teacher->gpuntisID); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $teacher->url; ?>">
                            <?php echo JText::_($teacher->name); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $teacher->url; ?>">
                            <?php echo JText::_($teacher->username); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $teacher->url; ?>">
                        	<?php
	                        	if ($teacher->subdepartment)
	                        	{
	                        		echo JText::_("$teacher->department ($teacher->subdepartment)");
	                        	}
	                        	else 
	                        	{
	                            	echo JText::_($teacher->department);
	                            }
                            ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $teacher->url; ?>">
                            <?php echo JText::_($teacher->campus); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $teacher->url; ?>">
                            <?php echo JText::_($teacher->institution); ?>
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
    <input type="hidden" name="filter_order" value="<?php echo $this->orderby; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->direction; ?>" />
    <input type="hidden" name="view" value="teacher_manager" />
    <input type="hidden" name="campus" value="<?php echo $this->state->get('filter.department'); ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>