<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Toolbar\Toolbar;

$buttons     = Toolbar::getInstance()->getItems();
$columnCount = count($this->headers);
$data        = ['view' => $this, 'options' => []];
$showSearch  = !empty($filters['filter_search']);

$filters     = $this->filterForm->getGroup('filter');
$noFilters   = count($filters) === 0;
$onlySearch  = (count($filters) === 1 and !empty($filters['filter_search']));
$showFilters = !($noFilters or $onlySearch);

?>
<div id="j-main-container">
    <form action="index.php?" id="adminForm" method="post" name="adminForm">
        <div class="js-stools clearfix">
            <div class="clearfix">
                <div class="js-stools-container-bar">
                    <?php if (!$showSearch) : ?>
                        <label for="filter_search" class="element-invisible">
                            <?php echo Languages::_('JSEARCH_FILTER'); ?>
                        </label>
                        <div class="btn-wrapper input-append">
                            <?php echo $filters['filter_search']->input; ?>
                            <button type="submit" class="btn hasTooltip"
                                    title="<?php echo HTML::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
                                <i class="icon-search"></i>
                            </button>
                        </div>
                        <div class="btn-wrapper">
                            <button type="button" class="btn hasTooltip js-stools-btn-clear"
                                    title="<?php echo HTML::tooltipText('JSEARCH_FILTER_CLEAR'); ?>">
                                <i class="icon-refresh"></i>
                                <?php echo Languages::_('JSEARCH_RESET'); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                    <?php echo LayoutHelper::render('joomla.searchtools.default.list', $data); ?>
                    <?php foreach ($buttons as $button): ?>
                        <?php echo $toolbar->renderButton($button); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="clr"></div>
        <table class="table table-striped" id="<?php echo $this->get('name'); ?>-list">
            <thead>
            <tr>';
                <?php foreach ($this->headers as $header): ?>
                    <th><?php echo $header; ?></th>
                <?php endforeach; ?>
            </tr>
            <?php if ($showFilters): ?>
            <tr>
                <?php foreach (array_keys($this->headers) as $name) :?>
                <th>
                <?php if ($name === 'checkbox') : ?>
                        <div class="js-stools-field-filter">
                            <?php echo HTML::_('grid.checkall'); ?>
                        </div>
                    <?php elseif (isset($filters["filter_$name"])) : ?>
                        <div class="js-stools-field-filter">
                            <?php echo $filters["filter_$name"]->input; ?>
                        </div>
                    <?php endif; ?>
                </th>
                <?php endforeach; ?>
            </tr>
            <?php endif; ?>
            </thead>
            <tbody>
            <?php foreach ($items as $row) : ?>
                <tr>
                    <?php foreach ($row as $column) : ?>
                        <td><?php echo $column; ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="<?php echo $columnCount; ?>">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
            </tfoot>
        </table>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="option" value="com_thm_organizer"/>
        <input type="hidden" name="view" value="<?php echo $this->get('name'); ?>"/>
        <input type="hidden" name="tmpl" value="component"/>
        <?php echo HTML::_('form.token'); ?>
    </form>
</div>
