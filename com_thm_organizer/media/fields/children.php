<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldColor
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class JFormFieldColor for component com_thm_organizer
 *
 * Class provides methods to create a form field that contains the colors
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldChildren extends JFormField
{
    /**
     * Type
     *
     * @var    String
     */
    protected $type = 'children';

    /**
     * Returns a select box which contains the colors
     *
     * @return Select box
     */
    public function getInput()
    {
        $children = $this->getChildren();

        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/children.css');
        $document->addScript(JURI::root() . "media/com_thm_organizer/js/children.js");

        return $this->getHTML($children);
    }

    /**
     * Retrieves child mappings for the resource being edited
     *
     * @return  array  empty if no child data exists
     */
    private function getChildren()
    {
        $resourceID = $this->form->getValue('id');
        $contextParts = explode('.', $this->form->getName());

        // Option.View
        $resourceType = str_replace('_edit', '', $contextParts[1]);

        $dbo = JFactory::getDbo();
        $idQuery = $dbo->getQuery(true);
        $idQuery->select('id')->from('#__thm_organizer_mappings');
        $idQuery->where("{$resourceType}ID = '$resourceID'");

        /**
         * Subordinate structures are the same for every parent mapping,
         * therefore only the first mapping needs to be found
         */
        $dbo->setQuery((string) $idQuery, 0, 1);

        try
        {
            $parentID = $dbo->loadResult();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return array();
        }

        if (empty($parentID))
        {
            return array();
        }

        $childMappingQuery = $dbo->getQuery(true);
        $childMappingQuery->select('poolID, subjectID, ordering');
        $childMappingQuery->from('#__thm_organizer_mappings');
        $childMappingQuery->where("parentID = '$parentID'");
        $childMappingQuery->order('lft ASC');
        $dbo->setQuery((string) $childMappingQuery);

        try
        {
            $children = $dbo->loadAssocList('ordering');
        }
        catch (runtimeException $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return array();
        }

        if (empty($children))
        {
            return array();
        }

        $this->setTypeData($children);

        return $children;
    }

    /**
     * Sets mapping data dependent upon the resource type
     *
     * @param   array  &$children  the subordinate resource data
     *
     * @return  void  adds data to the &$children array
     */
    private function setTypeData(&$children)
    {
        $poolEditLink = 'index.php?option=com_thm_organizer&view=pool_edit&id=';
        $subjectEditLink = 'index.php?option=com_thm_organizer&view=subject_edit&id=';
        foreach ($children as $ordering => $mapping)
        {
            if (!empty($mapping['poolID']))
            {
                $children[$ordering]['id'] = $mapping['poolID'] . 'p';
                $children[$ordering]['name'] = $this->getResourceName($mapping['poolID'], 'pool');
                $children[$ordering]['link'] = $poolEditLink . $mapping['poolID'];
            }
            else
            {
                $children[$ordering]['id'] = $mapping['subjectID'] . 's';
                $children[$ordering]['name'] = $this->getResourceName($mapping['subjectID'], 'subject');
                $children[$ordering]['link'] = $subjectEditLink . $mapping['subjectID'];
            }
        }
    }

    /**
     * Retrieves the child's name from the database
     *
     * @param   string  $resourceID    the id used for the child element
     * @param   string  $resourceType  the child element's type
     *
     * @return  string  the name of the child element
     */
    private function getResourceName($resourceID, $resourceType)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $language = explode('-', JFactory::getLanguage()->getTag());

        $query->select("name_{$language[0]}");
        $query->from("#__thm_organizer_{$resourceType}s");
        $query->where("id = '$resourceID'");
        $dbo->setQuery((string) $query);

        try
        {
            return $dbo->loadResult();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return '';
        }
    }

    /**
     * Generates the HTML Output for the children field
     *
     * @param   array  &$children  the children of the resource being edited
     *
     * @return  string  the HTML string for the children field
     */
    private function getHTML(&$children)
    {
        $html = '<table id="childList" class="table table-striped">';
        $html .= '<thead><tr>';
        $html .= '<th>' . JText::_('COM_THM_ORGANIZER_NAME') . '</th>';
        $html .= '<th class="thm_organizer_pools_ordering">' . JText::_('COM_THM_ORGANIZER_ORDER') . '</th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        $moveUp = JText::_('JLIB_HTML_MOVE_UP');
        $moveDown = JText::_('JLIB_HTML_MOVE_DOWN');
        $addSpace = JText::_('COM_THM_ORGANIZER_ACTION_ADD_SPACE');
        $makeLast = JText::_('COM_THM_ORGANIZER_ACTION_MAKE_LAST');

        $rowClass = 'row0';
        if (!empty($children))
        {
            $maxOrdering = max(array_keys($children));
            for ($ordering = 1; $ordering <= $maxOrdering; $ordering++)
            {
                if (isset($children[$ordering]))
                {
                    $childID = $children[$ordering]['id'];
                    $name = $children[$ordering]['name'];
                    $link = JRoute::_($children[$ordering]['link'], false);
                }
                else
                {
                    $link = $name = $childID = '';
                }

                $icon = empty($children[$ordering]['subjectID'])? 'list' : 'book';

                $html .= '<tr id="childRow' . $ordering . '" class="' . $rowClass . '">';
                $html .= '<td class="child-name">';
                $html .= '<a id="child' . $ordering . 'link" href="' . $link . '">';
                $html .= '<span id="child' . $ordering . 'icon" class="icon-' . $icon . '"></span>';
                $html .= '<span id="child' . $ordering . 'name">' . $name . '</span>';
                $html .= '</a>';
                $html .= '<input type="hidden" name="child' . $ordering . '" id="child' . $ordering . '" value="' . $childID . '" />';
                $html .= '</td>';
                $html .= '<td class="child-order">';
                $html .= '<button class="btn btn-small" onclick="moveUp(\'' . $ordering . '\');" title="' . $moveUp . '">';
                $html .= '<span class="icon-previous"></span>';
                $html .= '</button>';
                $html .= '<input type="text" title="Ordering" name="child' . $ordering . 'order" id="child' . $ordering . 'order" ';
                $html .= 'size="2" value="' . $ordering . '" class="text-area-order" onChange="orderWithNumber(' . $ordering . ');"/>';
                $html .= '<button class="btn btn-small" onclick="setEmptyElement(\'' . $ordering . '\');" title="' . $addSpace . '">';
                $html .= '<span class="icon-add-Space"></span>';
                $html .= '</button>';
                $html .= '<button class="btn btn-small" onClick="removeRow(' . $ordering . ');" title="' . JText::_('JTOOLBAR_DELETE') . '" >';
                $html .= '<span class="icon-trash"></span>';
                $html .= '</button>';
                $html .= '<button class="btn btn-small" onclick="moveDown(\'' . $ordering . '\');" title="' . $moveDown . '">';
                $html .= '<span class="icon-next"></span>';
                $html .= '</button>';
                $html .= '<button class="btn btn-small" onclick="setElementOnLastPosition(\'' . $ordering . '\');" title="' . $makeLast . '">';
                $html .= '<span class="icon-last"></span>';
                $html .= '</button>';
                $html .= '</td>';
                $html .= '</tr>';
                $rowClass = $rowClass == 'row0'? 'row1' : 'row0';
            }
        }
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<div class="btn-toolbar" id="children-toolbar"></div>';

        return $html;
    }
}
