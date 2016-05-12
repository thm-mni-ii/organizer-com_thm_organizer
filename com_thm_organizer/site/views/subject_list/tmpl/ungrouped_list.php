<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organiezr.site
 * @name        THM_OrganizerTemplateUngroupedList
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once 'item.php';

/**
 * Displays event information
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerTemplateUngroupedList
{
    /**
     * Renders subject information
     *
     * @param   array  &$view  the view context
     *
     * @return  void
     */
    public static function render(&$view)
    {
        echo '<div class="subject-list-container">';
        if (count($view->items))
        {
            echo '<ul class="subject-list">';
            $displayItems = array();
            foreach ($view->items AS $item)
            {
                if (!empty($displayItems[$item->id]) AND $item->teacherResp == 2)
                {
                    continue;
                }
                $displayItems[$item->id] = THM_OrganizerTemplateItem::render($item);
            }
            echo implode($displayItems);
            echo '</ul>';
        }
        echo '</div>';
    }
}
 