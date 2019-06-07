<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use Joomla\CMS\Layout\LayoutHelper;
use Organizer\Helpers\HTML;

if (!empty($this->submenu)) {
    echo '<div id="j-sidebar-container" class="span2">' . $this->submenu . '</div>';
}
$items       = $this->items;
$iteration   = 0;
$columnCount = count($this->headers);
?>
<div id="j-main-container" class="span10">
    <form action="?" id="adminForm" method="post" name="adminForm">
        <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
        <table class="table table-striped" id="<?php echo $this->get('name'); ?>-list">
            <thead>
            <tr>
                <?php foreach ($this->headers as $header) : ?>
                    <th><?php echo $header; ?></th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody <?php echo $this->getAttributesOutput($items); ?>>
            <?php foreach ($items as $row) : ?>
                <tr <?php echo $this->getAttributesOutput($row); ?>>
                    <?php
                    foreach ($row as $column) {
                        $colAttributes = $this->getAttributesOutput($column);
                        $colValue      = is_array($column) ? $column['value'] : $column;
                        echo "<td $colAttributes>$colValue</td>";
                    }
                    ?>
                </tr>
            <?php endforeach; ?>
            <tfoot>
            <tr>
                <td colspan="<?php echo $columnCount; ?>">
                    <?php echo $this->pagination->getListFooter(); ?>
            </tr>
            </tfoot>
            <?php
            if (isset($this->batch) && !empty($this->batch)) {
                foreach ($this->batch as $filename) {
                    foreach ($this->_path['template'] as $path) {
                        $exists = file_exists("$path$filename.php");
                        if ($exists) {
                            require_once "$path$filename.php";
                            break;
                        }
                    }
                }
            }
            ?>
        </table>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="option" value="com_thm_organizer"/>
        <input type="hidden" name="view" value="<?php echo $this->get('name'); ?>"/>
        <?php echo HTML::_('form.token'); ?>
    </form>
</div>


