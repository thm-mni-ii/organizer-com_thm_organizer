<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

require_once 'panel.php';
require_once 'item.php';
?>
<div class="componentheader">
    <h1 class="componentheading">
        <?php echo $this->item->name; ?>
    </h1>
</div>
<!-- use language_selection layout -->
<div class="curriculum">
    <?php
    foreach ($this->item->children as $pool) {
        THM_OrganizerTemplateCurriculumPanel::render($pool, 'main');
    }
    ?>
    <?php echo $this->disclaimer->render([]); ?>
</div>