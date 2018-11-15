<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

/**
 * Class provides a standardized display of listed resources in a modal context. Elements normally displayed by the
 * framework such as buttons and filters are explicitly a part of the template.
 */
class THM_OrganizerTemplateList_Modal
{
    /**
     * Method to create a list output
     *
     * @param object &$view the view context calling the function
     *
     * @return void
     * @throws Exception
     */
    public static function render(&$view)
    {
        $data    = ['view' => $view, 'options' => []];
        $filters = $view->filterForm->getGroup('filter');
        ?>
        <div id="j-main-container">
            <form action="index.php?" id="adminForm" method="post" name="adminForm">
                <div class="js-stools clearfix">
                    <div class="clearfix">
                        <div class="js-stools-container-bar">
                            <?php self::renderSearch($filters); ?>
                            <?php echo JLayoutHelper::render('joomla.searchtools.default.list', $data); ?>
                            <?php self::renderButtons(); ?>
                        </div>
                    </div>
                </div>
                <div class="clr"></div>
                <table class="table table-striped" id="<?php echo $view->get('name'); ?>-list">
                    <?php
                    echo '<thead>';
                    self::renderHeader($view->headers);
                    self::renderHeaderFilters($view->headers, $filters);
                    echo '</thead>';
                    self::renderBody($view->items);
                    self::renderFooter($view);
                    ?>
                </table>
                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="boxchecked" value="0"/>
                <input type="hidden" name="option"
                       value="<?php echo JFactory::getApplication()->input->get('option'); ?>"/>
                <input type="hidden" name="view" value="<?php echo $view->get('name'); ?>"/>
                <input type="hidden" name="tmpl" value="component"/>
                <?php self::renderHiddenFields($view) ?>
                <?php echo JHtml::_('form.token'); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Renders the search input group if set in the filter xml
     *
     * @param array &$filters the filters set for the view
     *
     * @return void
     */
    private static function renderSearch(&$filters)
    {
        $showSearch = !empty($filters['filter_search']);
        if (!$showSearch) {
            return;
        }
        ?>
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
                    title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>">
                <i class="icon-refresh"></i>
                <?php echo JText::_('JSEARCH_RESET'); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Renders any buttons appended by the view
     *
     * @return void
     */
    private static function renderButtons()
    {
        $toolbar = JToolbar::getInstance();
        $buttons = $toolbar->getItems();
        foreach ($buttons as $button) {
            echo $toolbar->renderButton($button);
        }
    }


    /**
     * Renders the table head
     *
     * @param array &$headers an array containing the table headers
     *
     * @return void
     */
    private static function renderHeader(&$headers)
    {
        echo '<tr>';
        foreach ($headers as $header) {
            echo "<th>$header</th>";
        }
        echo '</tr>';
    }

    /**
     * Renders the table head
     *
     * @param array &$headers an array containing the table headers
     * @param array &$filters the filters set for the view
     *
     * @return void
     */
    private static function renderHeaderFilters(&$headers, &$filters)
    {
        $noFilters   = count($filters) === 0;
        $onlySearch  = (count($filters) === 1 and !empty($filters['filter_search']));
        $dontDisplay = ($noFilters or $onlySearch);
        if ($dontDisplay) {
            return;
        }
        $template = '<th><div class="js-stools-field-filter">XXXX</div></th>';

        $headerNames = array_keys($headers);
        echo '<tr>';
        foreach ($headerNames as $name) {
            if ($name == 'checkbox') {
                echo str_replace('XXXX', JHtml::_('grid.checkall'), $template);
                continue;
            }
            $found      = false;
            $searchName = "filter_$name";
            foreach ($filters as $fieldName => $field) {
                if ($fieldName == $searchName) {
                    echo str_replace('XXXX', $field->input, $template);
                    $found = true;
                    break;
                }
            }
            if ($found) {
                continue;
            }
            echo '<th></th>';
        }
        echo '</tr>';
    }

    /**
     * Renders the table head
     *
     * @param array &$items an array containing the table headers
     *
     * @return void
     */
    private static function renderBody(&$items)
    {
        $rowClass = 'row0';
        echo '<tbody>';
        foreach ($items as $row) {
            echo "<tr class='$rowClass'>";
            foreach ($row as $column) {
                echo "<td>$column</td>";
            }
            echo '</tr>';
            $rowClass = $rowClass == 'row0' ? 'row1' : 'row0';
        }
        echo '</thead>';
    }

    /**
     * Renders the table foot
     *
     * @param object &$view the view context calling the function
     *
     * @return void
     */
    private static function renderFooter(&$view)
    {
        $columnCount = count($view->headers);
        echo '<tfoot><tr>';
        echo "<td colspan='$columnCount'>";
        echo $view->pagination->getListFooter();
        echo '</td></tr></tfoot>';
    }

    /**
     * Renders hidden fields
     *
     * @param object &$view the view object
     *
     * @return void  outputs hidden fields html
     */
    protected static function renderHiddenFields(&$view)
    {
        if (isset($view->hiddenFields) && !empty($view->hiddenFields)) {
            foreach ($view->hiddenFields as $field) {
                echo $field;
            }
        }
    }
}
