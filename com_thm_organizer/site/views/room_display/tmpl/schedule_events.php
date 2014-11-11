<?php
/**
 * @category    Joomla <extension type>
 * @package     THM_<extension family>
 * @subpackage  <extension name>.<admin/site>
 * @name        <class name>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Displays event information
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerTemplateSchedule_Events
{
    /**
     * Renders event information
     *
     * @param   array   &$events   the events to diplay
     * @param   string  $title     the title for the type of events
     * @param   int     &$metric   the current number of events displayed for all types
     * @param   int     $maxItems  the maximum number of events that can be displayed
     */
    public static function render(&$events, $title, &$metric, $maxItems)
    {
        if (count($events))
        {
            echo '<div class="event-section">' . $title . '</div>';
            echo "<ul>";
            foreach ($events as $event)
            {
                if ($metric < $maxItems)
                {
                    echo "<li>";
                    echo '<div class="event-title">' .$event['title'] . '</div>';
                    echo '<div class="event-dates">' . $event['displayDates'] . '</div>';
                    echo '<div class="event-info">' . $event['description'] . '</div>';
                    echo "</li>";
                    }
                    $metric++;
                }
            echo "</ul>";
        }
    }
}
 