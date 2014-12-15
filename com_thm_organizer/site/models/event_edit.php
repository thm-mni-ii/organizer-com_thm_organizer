<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelEvent_Edit
 * @description create/edit appointment/event model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('thm_core.edit.model');

/**
 * Retrieves persistent data for output in the event edit view.
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelEvent_Edit extends THM_CoreModelEdit
{
    public $event = null;

    public $categories = null;

    public $eventLink = "";

    public $listLink = "";

    /**
     * calls functions to set model data
     */
    public function __construct()
    {
        parent::__construct();
        //$this->loadCategories();
        //$this->setLinks();
    }


    /**
     * loads the categories for which the current user has write/edit access
     *
     * @return void
     */
    private function loadCategories()
    {
        $emptyCategory = array( 'id' => '-1',
                                        'title' => JText::_('COM_THM_ORGANIZER_CATEGORY_SELECT'),
                                        'description' => JText::_('COM_THM_ORGANIZER_CATEGORY_SELECT_DESC'),
                                        'display' => '',
                                        'contentCat' => '',
                                        'access' => '' );

        $dbo = JFactory::getDbo();
        $user = JFactory::getUser();
        $query = $dbo->getQuery(true);

        $select = 'oc.id AS id, oc.title AS title, global, ';
        $select .= 'reserves, oc.description as description, ';
        $select .= 'c.id AS contentCatID, c.title AS contentCat, ';
        $select .= 'vl.title AS access ';
        $query->select($select);

        $query->from('#__thm_organizer_categories AS oc');
        $query->innerJoin('#__categories AS c ON oc.contentCatID = c.id');
        $query->innerJoin('#__viewlevels AS vl ON c.access = vl.id');
        $query->order('oc.title ASC');
        $dbo->setQuery((string) $query);echo "<pre>" . print_r((string) $query, true) . "</pre>";

        try
        {
            $results = $dbo->loadAssocList();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            $this->categories = array();
            return;
        }

        if (count($results))
        {
            $userID = JFactory::getUser()->id;
            $isAuthor = ($this->event['created_by'] == $userID)? true : false;
            foreach ($results as $k => $v)
            {
                $asset = 'com_content.category.' . $v['contentCatID'];
                if ($this->event['id'] == 0)
                {
                    $access = $user->authorise('core.create', $asset);
                }
                elseif ($this->event['id'] > 0)
                {
                    $canEditOwn = false;
                    if ($isAuthor)
                    {
                        $canEditOwn = $user->authorise('core.edit.own', $asset);
                    }
                    $canEdit = $user->authorise('core.edit', $asset);
                    $access = $canEdit or $canEditOwn;
                }
                if (empty($access))
                {
                    unset($results[$k]);
                }
            }
            if (count($results))
            {
                $categories = array();
                $categories[-1] = $emptyCategory;
                $initialID = '-1';
                foreach ($results as $k => $v)
                {
                    if ($v['global'] and $v['reserves'])
                    {
                        $display = '<p>' . JText::_('COM_THM_ORGANIZER_EE_GLOBALRESERVES_EXPLANATION') . '</p>';
                    }
                    elseif ($v['global'])
                    {
                        $display = '<p>' . JText::_('COM_THM_ORGANIZER_EE_GLOBAL_EXPLANATION') . '</p>';
                    }
                    elseif ($v['reserves'])
                    {
                        $display = '<p>' . JText::_('COM_THM_ORGANIZER_EE_RESERVES_EXPLANATION') . '</p>';
                    }
                    else
                    {
                        $display = '<p>' . JText::_('COM_THM_ORGANIZER_EE_NOGLOBALRESERVES_EXPLANATION') . '</p>';
                    }
                    $v['display'] = $display;

                    $contentCat = '<p>' . JText::_('COM_THM_ORGANIZER_EE_CATEGORY_EXPLANATION');
                    $contentCat .= "<span class='thm_organizer_ee_highlight'>&quot;" . $v['contentCat'] . "&quot;</span>.</p>";
                    $v['contentCat'] = $contentCat;

                    $access = '<p>' . JText::_('COM_THM_ORGANIZER_EE_CONTENT_EXPLANATION_START');
                    $access .= $v['access'] . JText::_('COM_THM_ORGANIZER_EE_CONTENT_EXPLANATION_END') . '</p>';
                    $v['access'] = $access;

                    $v['description'] = str_replace("\r", "", str_replace("\n", "", $v['description']));
                    $v['description'] = addslashes($v['description']);

                    $v['display'] = addslashes($v['display']);
                    $v['contentCat'] = addslashes($v['contentCat']);
                    $v['access'] = addslashes($v['access']);

                    $categories[$v['id']] = $v;
                }
                if (!$this->event['categoryID'])
                {
                    $this->event['categoryID'] = $initialID;
                }
                $this->categories = $categories;
            }
            else
            {
                $this->categories = array();
            }
        }
        else
        {
            $this->categories = array();
        }
    }

    /**
     * Sets links if the item id belongs to a menu type of event manager and/or if the
     * event is not new.
     *
     * @return void  sets object variables
     */
    private function setLinks()
    {
        $app = JFactory::getApplication();
        $menuID = $app->input->getInt('Itemid', 0);
        $eventID = $this->getForm()->getValue('id', 0);
        if ($eventID)
        {
            $eventLink = "index.php?option=com_thm_organizer&view=event_details&eventID=$eventID";
            $eventLink .= empty($menuID)? '' : "&Itemid=$menuID";
            $this->eventLink = JRoute::_($eventLink);
        }

        $dbo = JFactory::getDbo();

        $query = $dbo->getQuery(true);
        $query->select("link");
        $query->from("#__menu AS eg");
        $query->where("id = '$menuID''");
        $query->where("link LIKE '%event_manager%'");
        $dbo->setQuery((string) $query);
        
        try
        {
            $result = $dbo->loadResult();
            $this->listLink = empty($result)? '' : JRoute::_($result);
        }
        catch (Exception $exc)
        {
            $app->enqueueMessage($exc->getMessage(), 'error');
            $this->listLink = '';
        }
    }
}
