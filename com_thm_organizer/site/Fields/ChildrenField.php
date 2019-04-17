<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

defined('_JEXEC') or die;

/**
 * Class creates a box for managing subordinated curriculum elements. Change order, remove, add empty element.
 */
class ChildrenField extends \Joomla\CMS\Form\FormField
{
    /**
     * Type
     *
     * @var    String
     */
    protected $type = 'Children';

    /**
     * Generates a text for the management of child elements
     *
     * @return string  the HTML for the input
     */
    public function getInput()
    {
        $children = $this->getChildren();

        $document = \Factory::getDocument();
        $document->addStyleSheet(\JUri::root() . 'components/com_thm_organizer/css/children.css');
        $document->addScript(\JUri::root() . 'components/com_thm_organizer/js/children.js');

        return $this->getHTML($children);
    }

    /**
     * Retrieves child mappings for the resource being edited
     *
     * @return array  empty if no child data exists
     */
    private function getChildren()
    {
        $resourceID   = $this->form->getValue('id');
        $contextParts = explode('.', $this->form->getName());

        // Option.View
        $resourceType = str_replace('_edit', '', $contextParts[1]);

        $dbo     = \Factory::getDbo();
        $idQuery = $dbo->getQuery(true);
        $idQuery->select('id')->from('#__thm_organizer_mappings');
        $idQuery->where("{$resourceType}ID = '$resourceID'");

        /**
         * Subordinate structures are the same for every parent mapping,
         * therefore only the first mapping needs to be found
         */
        $dbo->setQuery($idQuery, 0, 1);

        $parentID = \OrganizerHelper::executeQuery('loadResult');

        if (empty($parentID)) {
            return [];
        }

        $childMappingQuery = $dbo->getQuery(true);
        $childMappingQuery->select('poolID, subjectID, ordering');
        $childMappingQuery->from('#__thm_organizer_mappings');
        $childMappingQuery->where("parentID = '$parentID'");
        $childMappingQuery->order('lft ASC');
        $dbo->setQuery($childMappingQuery);

        $children = \OrganizerHelper::executeQuery('loadAssocList', [], 'ordering');
        if (empty($children)) {
            return [];
        }

        $this->setTypeData($children);

        return $children;
    }

    /**
     * Sets mapping data dependent upon the resource type
     *
     * @param array &$children the subordinate resource data
     *
     * @return void  adds data to the &$children array
     */
    private function setTypeData(&$children)
    {
        $poolEditLink    = 'index.php?option=com_thm_organizer&view=pool_edit&id=';
        $subjectEditLink = 'index.php?option=com_thm_organizer&view=subject_edit&id=';
        foreach ($children as $ordering => $mapping) {
            if (!empty($mapping['poolID'])) {
                $children[$ordering]['id']   = $mapping['poolID'] . 'p';
                $children[$ordering]['name'] = $this->getResourceName($mapping['poolID'], 'pool');
                $children[$ordering]['link'] = $poolEditLink . $mapping['poolID'];
            } else {
                $children[$ordering]['id']   = $mapping['subjectID'] . 's';
                $children[$ordering]['name'] = $this->getResourceName($mapping['subjectID'], 'subject');
                $children[$ordering]['link'] = $subjectEditLink . $mapping['subjectID'];
            }
        }
    }

    /**
     * Retrieves the child's name from the database
     *
     * @param string $resourceID   the id used for the child element
     * @param string $resourceType the child element's type
     *
     * @return string  the name of the child element
     */
    private function getResourceName($resourceID, $resourceType)
    {
        $dbo      = \Factory::getDbo();
        $query    = $dbo->getQuery(true);
        $language = explode('-', \Factory::getLanguage()->getTag());

        $query->select("name_{$language[0]}");
        $query->from("#__thm_organizer_{$resourceType}s");
        $query->where("id = '$resourceID'");
        $dbo->setQuery($query);

        return (string)\OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Generates the HTML Output for the children field
     *
     * @param array &$children the children of the resource being edited
     *
     * @return string  the HTML string for the children field
     */
    private function getHTML(&$children)
    {
        $html = '<table id="childList" class="table table-striped">';
        $html .= '<thead><tr>';
        $html .= '<th>' . \JText::_('COM_THM_ORGANIZER_NAME') . '</th>';
        $html .= '<th class="thm_organizer_pools_ordering">' . \JText::_('COM_THM_ORGANIZER_ORDER') . '</th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        $addSpace = \JText::_('COM_THM_ORGANIZER_ACTION_ADD_SPACE');
        \JText::script('COM_THM_ORGANIZER_ACTION_ADD_SPACE');
        $makeFirst = \JText::_('COM_THM_ORGANIZER_ACTION_MAKE_FIRST');
        \JText::script('COM_THM_ORGANIZER_ACTION_MAKE_FIRST');
        $makeLast = \JText::_('COM_THM_ORGANIZER_ACTION_MAKE_LAST');
        \JText::script('COM_THM_ORGANIZER_ACTION_MAKE_LAST');
        $moveChildUp = \JText::_('JLIB_HTML_MOVE_UP');
        \JText::script('JLIB_HTML_MOVE_UP');
        $moveChildDown = \JText::_('JLIB_HTML_MOVE_DOWN');
        \JText::script('JLIB_HTML_MOVE_DOWN');
        \JText::script('JTOOLBAR_DELETE');

        $rowClass = 'row0';
        if (!empty($children)) {
            $maxOrdering = max(array_keys($children));
            for ($ordering = 1; $ordering <= $maxOrdering; $ordering++) {
                if (isset($children[$ordering])) {
                    $childID = $children[$ordering]['id'];
                    $name    = $children[$ordering]['name'];
                    $link    = \JRoute::_($children[$ordering]['link'], false);
                } else {
                    $link = $name = $childID = '';
                }

                $icon = '';
                if (!empty($children[$ordering])) {
                    $icon = empty($children[$ordering]['subjectID']) ? 'icon-list' : 'icon-book';
                }

                $html .= '<tr id="childRow' . $ordering . '" class="' . $rowClass . '">';

                $visualDetails = '<td class="child-name">';
                $visualDetails .= '<a id="child' . $ordering . 'link" href="' . $link . '" target="_blank">';
                $visualDetails .= '<span id="child' . $ordering . 'icon" class="' . $icon . '"></span>';
                $visualDetails .= '<span id="child' . $ordering . 'name">' . $name . '</span>';
                $visualDetails .= '</a>';
                $visualDetails .= '<input type="hidden" name="child' . $ordering . '" id="child' . $ordering . '" value="' . $childID . '" />';
                $visualDetails .= '</td>';

                $orderingToolbar = '<td class="child-order">';

                $first = '<button class="btn btn-small" onclick="setFirst(\'' . $ordering . '\');" title="' . $makeFirst . '">';
                $first .= '<span class="icon-first"></span>';
                $first .= '</button>';

                $previous = '<button class="btn btn-small" onclick="moveChildUp(\'' . $ordering . '\');" title="' . $moveChildUp . '">';
                $previous .= '<span class="icon-previous"></span>';
                $previous .= '</button>';

                $order = '<input type="text" title="Ordering" name="child' . $ordering . 'order" id="child' . $ordering . 'order" ';
                $order .= 'size="2" value="' . $ordering . '" class="text-area-order" onChange="moveChildToIndex(' . $ordering . ');"/>';

                $blank = '<button class="btn btn-small" onclick="addBlankChild(\'' . $ordering . '\');" title="' . $addSpace . '">';
                $blank .= '<span class="icon-download"></span>';
                $blank .= '</button>';

                $trash = '<button class="btn btn-small" onClick="trash(' . $ordering . ');" title="' . \JText::_('JTOOLBAR_DELETE') . '" >';
                $trash .= '<span class="icon-trash"></span>';
                $trash .= '</button>';

                $next = '<button class="btn btn-small" onclick="moveChildDown(\'' . $ordering . '\');" title="' . $moveChildDown . '">';
                $next .= '<span class="icon-next"></span>';
                $next .= '</button>';

                $last = '<button class="btn btn-small" onclick="setLast(\'' . $ordering . '\');" title="' . $makeLast . '">';
                $last .= '<span class="icon-last"></span>';
                $last .= '</button>';

                $orderingToolbar .= $first . $previous . $order . $blank . $trash . $next . $last . '</td>';

                $html     .= $visualDetails . $orderingToolbar . '</tr>';
                $rowClass = $rowClass == 'row0' ? 'row1' : 'row0';
            }
        }
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<div class="btn-toolbar" id="children-toolbar"></div>';

        return $html;
    }
}
