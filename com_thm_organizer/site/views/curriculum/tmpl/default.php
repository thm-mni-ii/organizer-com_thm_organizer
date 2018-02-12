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
require_once 'panel.php';
require_once 'item.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_COMPONENT_SITE . '/helpers/pool.php';
?>
<div class="componentheader">
    <h1 class="componentheading">
        <?php echo $this->item->name; ?>
    </h1>

    <div class="language-switches">
        <?php
        foreach ($this->languageSwitches as $switch) {
            echo $switch;
        }
        ?>
    </div>
</div>
<div class="curriculum">
    <?php
    foreach ($this->item->children as $pool) {
        THM_OrganizerTemplateCurriculumPanel::render($pool, 'main');
    }
    ?>
    <?php echo $this->disclaimer->render($this->disclaimerData); ?>
</div>