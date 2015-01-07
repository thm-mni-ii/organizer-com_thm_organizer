<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        default template for the event view
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
?>
<div class="organizer-item">
    <h2 class="componentheading">
        <?php echo $this->item->title; ?>
    </h2>
    <div class="btn-toolbar">
        <?php foreach ($this->buttons AS $button): ?>
        <div class="btn-group">
            <?php  echo $button; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="item-content">
<?php
    echo $this->item->introtext;
    echo $this->item->fulltext;
?>
    </div>
</div>
    
