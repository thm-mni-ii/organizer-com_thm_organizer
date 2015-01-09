<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        event list default template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$model = $this->getModel();
$showHeading = $model->params->get('show_page_heading', '');
$title = $model->params->get('page_title', '');
$data = array('view' => $this, 'options' => array());
$filters = $this->filterForm->getGroup('filter');
$rowClass = 'row0';
$columnCount = count($this->headers);
?>
<div id="j-main-container" class="span10">
    <form action="index.php?" id="adminForm"  method="post"
          name="adminForm" xmlns="http://www.w3.org/1999/html">
        <?php if (!empty($showHeading)): ?>
            <h2 class="componentheading">
                <?php echo $title; ?>
            </h2>
        <?php endif; ?>
        <div class="btn-toolbar">
            <?php foreach ($this->buttons AS $button): ?>
                <div class="btn-group">
                    <?php  echo $button; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="searchArea">
            <div class="js-stools clearfix">
                <div class="clearfix">
                    <div class="js-stools-container-bar">
                        <label for="filter_search" class="element-invisible">
                            <?php echo JText::_('JSEARCH_FILTER'); ?>
                        </label>
                        <div class="btn-wrapper input-append">
                            <?php echo $filters['filter_search']->input; ?>
                            <button type="submit" class="btn hasTooltip"
                                    title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
                                <i class="icon-search"></i>
                            </button>
                        </div>
                        <div class="btn-wrapper">
                            <button type="button" class="btn hasTooltip js-stools-btn-clear"
                                    title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';">
                                <i class="icon-redo-2"></i>
                            </button>
                        </div>
                    </div>
                    <div class="js-stools-container-list hidden-phone hidden-tablet">
                        <div class="btn-wrapper">
                            <?php echo $filters['filter_category_restriction']->input; ?>
                        </div>
                        <div class="btn-wrapper">
                            <?php echo $filters['filter_from_date']->label; ?>
                            <?php echo $filters['filter_from_date']->input; ?>
                        </div>
                        <div class="btn-wrapper">
                            <?php echo $filters['filter_to_date']->label; ?>
                            <?php echo $filters['filter_to_date']->input; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clr"> </div>
        <table class="table table-striped" id="<?php echo $this->get('name'); ?>-list">
            <thead>
                <tr>
                <?php foreach ($this->headers as $header): ?>
                    <th><?php echo $header; ?></th>
                <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
<?php
foreach ($this->items as $row)
{
    echo "<tr class='$rowClass'>";
    foreach ($row as $column)
    {
        echo "<td>$column</td>";
    }
    echo '</tr>';
    $rowClass = $rowClass == 'row0'? 'row1' : 'row0';
}
?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan='<?php echo $columnCount; ?>'>
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="option" value="com_thm_organizer" />
        <input type="hidden" name="view" value="event_manager" />
        <?php echo JHtml::_('form.token');?>
    </form>
</div>


