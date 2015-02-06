<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        curriculum view item panel layout
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_COMPONENT_SITE . '/helpers/pool.php';

class THM_OrganizerTemplateCurriculumItemPanel
{
    /**
     * Generates the HTML output for a main panel element
     *
     * @param   object  &$element  the element to be rendered
     *
     * @return  void  generates HTML output
     */
    public static function render(&$element)
    {
        $externalID = empty($element->externalID)? '' : $element->externalID;
        $crpText = $element->type == 'subject'? $element->CrP . ' CrP' : THM_OrganizerHelperPool::getCrPText($element);
        $headStyle = '';
        if (!empty($element->bgColor))
        {
            $textColor = THM_OrganizerHelperComponent::getTextColor($element->bgColor);
            $headStyle .= ' style="background-color: ' . $element->bgColor . '; color: ' . $textColor . '"';
        }
        echo '<div class="item">';
        echo '<div class="item-head"' . $headStyle. '>';
        echo '<span class="item-code">' .  $externalID . '</span>';
        echo '<span class="item-crp">' .  $crpText . '</span>';
        echo '</div>';
        echo '<span class="item-name">' . $element->name . '</span>';
        echo '<div class="item-tools">';
        if (!empty($element->teacherName))
        {
            echo '<a class="btn hasTooltip" href="#" title="' . $element->teacherName . '"><icon class="icon-user"></icon></a>';
        }
        if (!empty($element->children))
        {
            $script = 'onclick="toggleContainer(\'#panel-' . $element->mapping . '\')"';
            echo '<a class="btn hasTooltip" ' . $script . ' title="' . JText::_('COM_THM_ORGANIZER_ACTION_OPEN_POOL') . '"><icon class="icon-grid-2"></icon></a>';
            THM_OrganizerTemplateCurriculumPanel::render($element);
        }
        echo '</div>';
        echo '</div>';
    }
}