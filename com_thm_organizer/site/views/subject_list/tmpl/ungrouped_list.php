<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organiezr.site
 * @name        THM_OrganizerTemplateUngroupedList
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2015 TH Mittelhessen
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
class THM_OrganizerTemplateUngroupedList
{
    /**
     * Renders subject information
     *
     * @param   array   &$view   the view context
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
                // entry already exists and the teacher for the subject being iterated is not responsible
                if (!empty($displayItems['id']) AND $item->teacherResp == 2)
                {
                    continue;
                }

                $displayItem = '';
                $moduleNr = empty($item->externalID)? '' : '<span class="module-id" >(' . $item->externalID . ')';
                $link = empty($item->subjectLink)? 'XXXX' : '<a href="' . $item->subjectLink . '">XXXX</a>';

                $displayItem .= '<li>';
                $displayItem .= '<span class="subject-name">' . str_replace('XXXX', $item->subject . $moduleNr, $link) . '</span>';
                $displayItem .= '<span class="subject-teacher">' . str_replace('XXXX', $item->teacherName, $link) . '</span>';
                $displayItem .= '<span class="subject-crp">' . str_replace('XXXX', $item->creditpoints, $link) . '</span>';
                $displayItem .= '</li>';
                $displayItems[$item->id] = $displayItem;
            }
            echo implode($displayItems);
            echo '</ul>';
        }
        echo '</div>';
    }
}
 