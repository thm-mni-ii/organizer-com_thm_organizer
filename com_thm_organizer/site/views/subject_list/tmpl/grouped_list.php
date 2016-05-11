<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organiezr.site
 * @name        THM_OrganizerTemplateUngroupedList
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
require_once 'item.php';

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
     * @param   array   &$view   the view context
     * @param   array   $params  the group parameters (order, name, id)
     * @param   string  $type    the type of group
     *
     * @return  void
     */
    public static function render(&$view, $params, $type)
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

            $borderStyle = ' style="border-left: 8px solid';
            $borderStyle .= empty($group['bgColor'])? 'transparent' : $group['bgColor'];
            $borderStyle .= ';"';

            $field = empty($group['field'])? '' : $group['field'];
            $fieldStyle = ' style="height: 19px; width: 12px !important; position: relative; left: -21px;';
            $fieldStyle .= empty($group['field'])? ' cursor: default;"' : ' cursor: help;"';

            echo '<a name="pool' . $group['id'] . '" class="pool-anchor"></a>';
            echo '<h5' . $borderStyle . '>';
            echo '<span class="subject-field hasTooltip" ';
            echo $fieldStyle . ' title="' . $field . '">&nbsp;&nbsp;&nbsp;</span>';
            echo $group['name'] . '</h5>';
            echo '<div class="subject-list-container hidden" id="' . $params['name'] . '-' . $group['id'] . '">';
            echo '<ul class="subject-list">';

            $displayItems = self::getDisplayItems($group, $type);

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
                switch ($params['order'])
                {
                    case 'lft':
                        $groups[$order]['rgt'] = $item->rgt;
                        $groups[$order]['field'] = $item->poolField;
                        break;
                    case 'teacherName':
                        $groups[$order]['field'] = $item->teacherField;
                        break;
                    case 'field':
                    default:
                        $groups[$order]['field'] = $item->field;
                        break;

                }
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
        if ($params['order'] == 'lft')
        {
            $groups = self::processPoolGroups($groups);
        }
        return $groups;
    }

    /**
     * Further processes pool groups to ensure single output and links from parent groups
     *
     * @param   array  $groups  the already processed groups
     *
     * @return  void  alters &$groups
     */
    private function processPoolGroups($groups)
    {
        // Stores the child groups
        $childGroups = array();
        foreach ($groups as $outerLFT => $outerGroup)
        {
            $groups[$outerLFT]['children'] = array();

            // Because of the previous sorting the children will always come after the parent
            foreach ($groups as $lft => $innerGroup)
            {
                if ($outerLFT == $lft)
                {
                    continue;
                }
                $rgt = $innerGroup['rgt'];

                // Inner lft/rgt are a superset
                $isDescendant = ($outerLFT < $lft AND $outerGroup['rgt'] > $rgt);
                if ($isDescendant)
                {
                    // Children already exist
                    if (count($groups[$outerLFT]['children']))
                    {
                        $lastChild = end($groups[$outerLFT]['children']);
                        $isDescendantDescendant = ($lastChild['lft'] < $lft AND $lastChild['rgt'] > $rgt);

                        // Only immediate children should be considered for further processing
                        if ($isDescendantDescendant)
                        {
                            continue;
                        }
                    }
                    $groups[$outerLFT]['children'][$innerGroup['id']] = array('lft' => $lft, 'rgt' => $rgt);

                    $itemID = 'pool' . $innerGroup['id'];
                    $item = new stdClass;
                    $item->id = $itemID;
                    $item->subject = $innerGroup['name'];
                    $item->subjectLink = '#' . $itemID;
                    $item->subjectColor = $innerGroup['bgColor'];
                    $item->field = $innerGroup['field'];
                    $groups[$outerLFT]['items']['pool' . $innerGroup['id']]= $item;

                    /**
                     * Save the child group index for further processing. Overwriting is ok because newer objects will
                     * have a smaller 'lft' value.
                     */
                    $childGroups[$innerGroup['id']] = false;
                }
            }
        }
        $childIDs = array_keys($childGroups);
        foreach ($groups as $lft => $group)
        {
            if (in_array($group['id'], $childIDs))
            {
                if (empty($childGroups[$group['id']]))
                {
                    $childGroups[$group['id']] = $group;
                }
                unset($groups[$lft]);
            }
        }
        $groups = array_merge(array_values($groups), array_values($childGroups));
        return $groups;
    }

    /**
     * Gets the items to be displayed for the group being currently iterated
     *
     * @param   array   &$group  the group being iterated
     * @param   string  $type    the type of group
     *
     * @return  array  the items to be displayed in the group
     */
    private static function getDisplayItems(&$group, $type)
    {
        $displayItems = array();
        foreach ($group['items'] AS $item)
        {
            // Entry already exists and the teacher is not responsible for the subject being iterated
            if (!empty($displayItems['id']) AND $item->teacherResp == 2)
            {
                continue;
            }
            $displayItems[$item->id] = THM_OrganizerTemplateItem::render($item, $type);
        }
        return $displayItems;
    }
}