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
class THM_OrganizerTemplateGroupedList
{
    /**
     * Renders subject information
     *
     * @param   array  &$view   the view context
     * @param   array  $params  the group parameters (order, name, id)
     *
     * @return  void
     */
    public static function render(&$view, $params)
    {
        if (empty($view->items))
        {
            return;
        }

        $groups = self::getGroups($view->items, $params);
        if (empty($groups))
        {
            return;
        }

        foreach ($groups AS $group)
        {
            if (empty($group['items']))
            {
                continue;
            }

            echo '<div class="subject-list-container">';

            $style = '';
            if (!empty($group['bgColor']))
            {
                $style = ' style="background-color: ' . $group['bgColor']. '; color: ' . $group['textColor']. ';"';
            }
            $script = ' onClick="jQuery(\'#' . $params['name'] . '-' . $group['id'] . '\').toggle(\'slide\', 1000);"';
            echo '<h3' . $style . $script . '>' . $group['name'] . '</h3>';
            echo '<div class="subject-list-container" id="' . $params['name'] . '-' . $group['id'] . '">';
            echo '<ul class="subject-list">';
            $displayItems = array();
            foreach ($group['items'] AS $item)
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
            echo '</div>';
            echo '</div>';
        }
    }

    /**
     * Creates an array of groups with the indexes name and id
     *
     * @param   array  &$items  the subjects associated with the degree program
     * @param   array  $params  the group parameters (order, name, id)
     *
     * @return  array  the groups used in the tab
     */
    private static function getGroups($items, $params)
    {
        $groups = array();
        foreach ($items AS $item)
        {
            $order = empty($item->{$params['order']})? 'Empty' : $item->{$params['order']};
            $name = empty($item->{$params['name']})? JText::_('COM_THM_ORGANIZER_UNASSOCIATED') : $item->{$params['name']};
            $groupID = empty($item->{$params['id']})? '0' : $item->{$params['id']};
            if (empty($groups[$order]))
            {
                $groups[$order]['name'] = $name;
                $groups[$order]['id'] = $groupID;
                if (!empty($item->{$params['bgColor']}))
                {
                    $groups[$order]['bgColor'] = $item->{$params['bgColor']};
                    $groups[$order]['textColor'] = THM_OrganizerHelperComponent::getTextColor($item->{$params['bgColor']});
                }
                $groups[$order]['items'] = array();
            }
            if (empty($groups[$order]['items'][$item->id]) OR $item->teacherResp === 1)
            {
                $groups[$order]['items'][$item->id] = $item;
            }
        }
        ksort($groups);
        return $groups;
    }
}