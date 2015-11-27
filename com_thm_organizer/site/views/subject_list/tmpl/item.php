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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Displays event information
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerTemplateItem
{
    /**
     * Renders subject information
     *
     * @param   array   &$item  the item to be displayed
     * @param   string  $type   the type of group
     *
     * @return  void
     */
    public static function render(&$item, $type = '')
    {
        // Entry already exists and the teacher is not responsible for the subject being iterated
        if (!empty($displayItems['id']) AND $item->teacherResp == 2)
        {
            return;
        }

        $displayItem = '';
        $moduleNr = empty($item->externalID)? '' : '<span class="module-id" >(' . $item->externalID . ')';
        $link = empty($item->subjectLink)? 'XXXX' : '<a href="' . $item->subjectLink . '" target="_blank">XXXX</a>';
        $style = ' style="border-left: 8px solid ';
        $style .= empty($item->subjectColor)? 'transparent' : $item->subjectColor;
        $style .= ';."';

        $displayItem .= '<li ' . $style . '>';
        $displayItem .= '<span class="subject-name">' . str_replace('XXXX', $item->subject . $moduleNr, $link) . '</span>';
        if ($type != 'teacher')
        {
            $displayItem .= '<span class="subject-teacher">' . $item->teacherName . '</span>';
        }
        $displayItem .= '<span class="subject-crp">' . str_replace('XXXX', $item->creditpoints, $link) . ' CrP</span>';
        $displayItem .= '</li>';
        return $displayItem;
    }
}