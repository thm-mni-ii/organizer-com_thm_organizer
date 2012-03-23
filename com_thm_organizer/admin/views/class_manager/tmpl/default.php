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
$search = ($this->state->get('filter.search'))
			?
        	$this->escape($this->state->get('filter.search')) 
			: 
			JText::_('COM_THM_ORGANIZER_SEARCH_CRITERIA');
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
            <select name="filter_manager" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_CLM_SEARCH_MANAGERS'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_CLM_ALL_MANAGERS'); ?></option>
                <?php echo JHtml::_('select.options', $this->managers, 'id', 'name', $this->state->get('filter.manager'));?>
            </select>
            <select name="filter_semester" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_CLM_SEARCH_SEMESTERS'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_CLM_ALL_SEMESTERS'); ?></option>
                <?php echo JHtml::_('select.options', $this->semesters, 'id', 'name', $this->state->get('filter.semester'));?>
            </select>
            <select name="filter_major" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_CLM_SEARCH_MAJORS'); ?></option>
                <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_CLM_ALL_MAJORS'); ?></option>
                <?php echo JHtml::_('select.options', $this->majors, 'id', 'name', $this->state->get('filter.major'));?>
            </select>
        </div>
    </fieldset>
    <div class="clr"> </div>

    
<?php // table ?>    

<?php if(!empty($this->classes)) { $k = 0;?>
    <div>
        <table class="adminlist" cellpadding="0">
            <colgroup>
                <col id="thm_organizer_check_column" />
                <col id="thm_organizer_clm_gpuntis_id_column" />
                <col id="thm_organizer_clm_class_name_column" />
                <col id="thm_organizer_clm_alias_column" />
                <col id="thm_organizer_clm_manager_name_column" />
                <col id="thm_organizer_clm_semester_column" />
                <col id="thm_organizer_clm_major_column" />
            </colgroup>
            <thead>
                <tr>
                    <th class="thm_organizer_sch_th" ></th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_GPUNTISID', 'c.gpuntisID', $this->direction, $this->orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_NAME', 'c.name', $this->direction, $this->orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_ALIAS', 'c.alias', $this->direction, $this->orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MANAGER', 't.name', $this->direction, $this->orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_SEMESTER', 'c.semester', $this->direction, $this->orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MAJOR', 'c.major', $this->direction, $this->orderby); ?>
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
            <?php foreach($this->classes as $k => $class){ ?>
                <tr class="row<?php echo $k % 2;?>">
                    <td><?php echo JHtml::_('grid.id', $k, $class->id); ?></td>
                     <td>
                        <a href="<?php echo $class->url; ?>">
                            <?php echo JText::_($class->gpuntisID); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $class->url; ?>">
                            <?php echo JText::_($class->name); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $class->url; ?>">
                            <?php echo JText::_($class->alias); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $class->url; ?>">
                            <?php echo JText::_($class->c_manager); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $class->url; ?>">
                            <?php echo JText::_($class->semester); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $class->url; ?>">
                            <?php echo JText::_($class->major); ?>
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
    <input type="hidden" name="view" value="class_manager" />
    <input type="hidden" name="manager" value="<?php echo $this->state->get('filter.manager'); ?>" />
    <input type="hidden" name="semester" value="<?php echo $this->state->get('filter.semester'); ?>" />
    <input type="hidden" name="major" value="<?php echo $this->state->get('filter.major'); ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>