<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        curriculum view main panel layout
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
     * @param   object  &$element  the element to be rendered
     *
     * @return  void  generates HTML output
     */
    public static function render(&$pool, $type = 'modal')
    {
        $crpText = THM_OrganizerHelperPool::getCrPText($pool);
        $headStyle = '';
        if (!empty($pool->bgColor))
        {
            $textColor = THM_OrganizerHelperComponent::getTextColor($pool->bgColor);
            $headStyle .= ' style="background-color: #' . $pool->bgColor . '; color: ' . $textColor . '"';
        }
        $display = ($type == 'modal')? 'hidden' : 'shown';
        echo '<div id="panel-' . $pool->mapping . '" class="' . $type . '-panel ' . $display . '">';
        $script = ($type=='main')? ' onclick="toggleGroupDisplay(\'#main-panel-items-' .$pool->mapping . '\')"' : '';
        echo '<div class="' . $type . '-panel-head"' . $headStyle . $script .'>';
        echo '<div class="' . $type . ' panel-title">';
        echo '<span class="' . $type . '-panel-name">'. $pool->name . '</span>';
        echo '<span class="' . $type . '-panel-crp">('. $crpText . ')</span>';
        echo '</div>';
        if (!empty($pool->enable_desc) AND !empty($pool->description))
        {
            echo '<div class="' . $type . '-panel-description">' . $pool->description . '</div>';
        }
        echo '</div>';
        $display = ($type == 'main')? 'hidden' : 'shown';
        $mainID = ($type=='main')? 'id="main-panel-items-'.$pool->mapping.'"' : '';
        echo '<div class="' . $type . '-panel-items ' . $display .'" '.$mainID .'">';
        foreach ($pool->children AS $item)
        {
            THM_OrganizerTemplateCurriculumItemPanel::render($item);
        }
        echo '</div>';
        echo '</div>';
    }
}