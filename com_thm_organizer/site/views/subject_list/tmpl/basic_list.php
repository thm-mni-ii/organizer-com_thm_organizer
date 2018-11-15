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

/**
 * Displays a filtered set of subjects into the display context.
 */
class THM_OrganizerTemplateBasicList
{
    /**
     * Renders subject information
     *
     * @param array  &$view the view context
     * @param string $sort  the attribute by which the entries are to be sorted
     *
     * @return void
     */
    public static function render(&$view, $sort)
    {
        echo '<div class="subject-list-container">';
        if (count($view->items)) {
            $displayItems = $view->items;

            if ($sort == 'number') {
                usort($displayItems, function ($subjectOne, $subjectTwo) {
                    return $subjectOne->externalID > $subjectTwo->externalID;
                });
            }

            echo '<table class="subject-list">';
            foreach ($displayItems as $item) {
                echo $view->getItemRow($item, $sort);
            }
            echo '</table>';
        }
        echo '</div>';
    }
}
