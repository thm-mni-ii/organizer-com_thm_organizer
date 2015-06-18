<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerTemplateCurriculumPanel
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

class THM_OrganizerTemplateCurriculumPanel
{
    /**
     * Generates the HTML output for a main panel element
     *
     * @param   object  &$pool     the element to be rendered
     * @param   string  $type      the pool display type
     *
     * @return  void  generates HTML output
     */
    public function render(&$pool, $type = 'modal')
    {
        $crpText = THM_OrganizerHelperPool::getCrPText($pool);
        $headStyle = '';
        if (!empty($pool->bgColor))
        {
            $textColor = THM_OrganizerHelperComponent::getTextColor($pool->bgColor);
            $headStyle .= ' style="background-color: ' . $pool->bgColor . '; color: ' . $textColor . '"';
        }
        $displayHead = ($type == 'modal')? 'hidden' : 'shown';
        $script = ($type=='main')? ' onclick="toggleGroupDisplay(\'#main-panel-items-' .$pool->mapping . '\')"' : '';
        $displayBody = ($type == 'main')? 'hidden' : 'shown';
        $mainID = ($type=='main')? 'id="main-panel-items-'.$pool->mapping.'"' : '';
        $maxItems = (int) JFactory::getApplication()->getMenu()->getActive()->params->get('maxItems', 5);
        $itemWidth = 100 / $maxItems - 2;
        $childIndex = $childNumber = 1;
        $childCount = count($pool->children);
?>
    <div id="panel-<?php echo $pool->mapping; ?>" class="<?php echo $type; ?>-panel <?php echo $displayHead; ?>">
        <div class="<?php echo $type; ?>-panel-head" <?php echo $headStyle . $script; ?>'>
            <div class="<?php echo $type; ?> panel-title">
                <span class="<?php echo $type; ?>-panel-name">
                    <?php echo $pool->name; ?>
                </span>
                <span class="<?php echo $type; ?>-panel-crp">(<?php echo $crpText; ?>)</span>
            </div>
            <?php if (!empty($pool->enable_desc) AND !empty($pool->description)): ?>
            <div class="<?php echo $type; ?>-panel-description">
                <?php echo $pool->description; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="<?php echo $type; ?>-panel-items <?php echo $displayBody; ?>" <?php echo $mainID; ?>>
<?php
foreach ($pool->children AS $element)
{
    if ($childIndex === 1)
    {
?>
            <div class="panel-row">
<?php
    }
    $itemPanel = new THM_OrganizerTemplateCurriculumItemPanel;
    $itemPanel->render($element, $itemWidth);
    $isRowEnd = $childIndex === $maxItems;
    $isLastChild = $childNumber === $childCount;
    if ($isRowEnd OR $isLastChild)
    {
?>
            </div>
<?php
    }
    if ($isRowEnd)
    {
        $childIndex = 0;
    }
    $childIndex++;
    $childNumber++;
}
?>
        </div>
    </div>
<?php
    }
}