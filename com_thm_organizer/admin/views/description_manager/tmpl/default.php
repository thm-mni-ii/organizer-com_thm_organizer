<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        schedule manager default template
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @author      Daniel Kirsten danielDOTkirstenATmniDOTthmDOTde
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
    </fieldset>
    <div class="clr"> </div>

    
<?php // table ?>    

<?php if(!empty($this->descriptions)) { $k = 0;?>
    <div>
        <table class="adminlist" cellpadding="0">
            <colgroup>
                <col id="thm_organizer_check_column" />
                <col id="thm_organizer_dsm_description_category_column" />
                <col id="thm_organizer_dsm_description_description_column" />
                <col id="thm_organizer_dsm_description_gpuntisid_column" />
            </colgroup>
            <thead>
                <tr>
                    <th class="thm_organizer_sch_th" ></th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_CATEGORY', 'category', $this->direction, $this->orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_DESC', 'description', $this->direction, $this->orderby); ?>
                    </th>
                    <th class="thm_organizer_sch_th" >
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_GPUNTISID', 'gpuntisid', $this->direction, $this->orderby); ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="4">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
            <?php foreach($this->descriptions as $k => $description){ ?>
                <tr class="row<?php echo $k % 2;?>">
                    <td><?php echo JHtml::_('grid.id', $k, $description->id); ?></td>
                    <td>
                        <a href="<?php echo $description->url; ?>">
                            <?php echo JText::_($description->category); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $description->url; ?>">
                            <?php echo JText::_($description->description); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $description->url; ?>">
                            <?php echo JText::_($description->gpuntisID); ?>
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
    <input type="hidden" name="view" value="description_manager" />
    <?php echo JHtml::_('form.token'); ?>
</form>