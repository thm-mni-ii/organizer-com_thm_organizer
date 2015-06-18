<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        curriculum view default layout
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once 'panel.php';
require_once 'item.php';
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';
require_once JPATH_COMPONENT_SITE . '/helpers/pool.php';
?>
<div class="componentheader">
    <h1 class="componentheading">
        <?php echo $this->item->name; ?>
    </h1>

    <div class="language-switches">
<?php
foreach ($this->languageSwitches AS $switch)
{
    echo $switch;
}
?>
    </div>
</div>
<div class="curriculum">
<?php
foreach ($this->item->children AS $pool)
{
    THM_OrganizerTemplateCurriculumPanel::render($pool, 'main', $this->maxItems);
}
?>
</div>