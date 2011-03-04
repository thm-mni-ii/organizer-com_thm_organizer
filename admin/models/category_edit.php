<?php
defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class thm_organizersModelcategory_edit extends JModel
{
    public $id = 0;
    public $title = '';
    public $description = '';
    public $global = false;
    public $reserves = false;

    public $contentCat = 0;
    public $contentCategories = null;
    public $userGroups = null;

    public $temp = array();


    public function __construct()
    {
        parent::__construct();
        $this->loadCategory();
        $this->loadUserGroups();
        $this->loadContentCategories();
    }

    private function loadCategory()
    {
        $ids = JRequest::getString('cid',  0, '', 'array');
        $id = (int)$ids[0];
        if($id)
        {
            $dbo = JFactory::getDbo();
            $query = $dbo->getQuery(true);
            $query->select("*");
            $query->from("#__thm_organizer_categories");
            $query->where("id = '$id'");
            $dbo->setQuery((string)$query);
            $result = $dbo->loadAssoc();
            if(count($result))
            {
                $this->id = $result['id'];
                $this->title = $result['title'];
                $this->description = $result['description'];
                $this->global = $result['globaldisplay'];
                $this->reserves = $result['reservesobjects'];
                $this->contentCat = $result['contentCatID'];
            }
        }
    }

    private function loadContentCategories()
    {
        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("c.id, c.title, c.description, REPLACE ( rules, 'core.', '' ) AS rules");
        $query->from("#__categories AS c");
        $query->innerJoin("#__assets AS a ON c.asset_id = a.id");
        $query->where("extension = 'com_content'");
        $query->where("published = '1'");
        $query->order("c.title ASC");
        $dbo->setQuery((string)$query);
        $this->temp = (string) $query;
        $contentCategories = $dbo->loadObjectList();
        if(count($contentCategories))
        {
            foreach($contentCategories as $k => $v)
            {
                $contentCategories[$k] = (array) $v;
                $contentCategories[$k]['rules'] = (array) json_decode($contentCategories[$k]['rules']);
                foreach($contentCategories[$k]['rules'] as $permissionName => $usergroups)
                    $contentCategories[$k]['rules'][$permissionName] = (array) $usergroups;
                $contentCategories[$k]['actiontable'] = $this->resolvePermissions($contentCategories[$k]['rules']);
            }
            $this->contentCategories = $contentCategories;
        }
    }

    private function loadUserGroups()
    {
        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("id, title");
        $query->from("#__usergroups");
        $dbo->setQuery((string)$query);
        $results = $dbo->loadAssocList();
        if(count($results))
        {
            $userGroups = array();
            foreach($results as $k => $v) $userGroups[$v['id']] = $v['title'];
            $this->userGroups = $userGroups;
        }
    }

    private function resolvePermissions($permissions)
    {
        //create an array of actions (create => CREATE)
        $permissionNames = array_keys($permissions);
        $permissionNames = array_flip($permissionNames);
        foreach($permissionNames as $k => $v)
        {
            $name = strtoupper($k);
            $name = str_replace(".", "", $name);
            $permissionNames[$k] = $name;
        }

        //collects the ids and group names of groups with permissions granted
        //other groups do not show up here
        $permissionGroups = array();
        foreach($permissions as $k => $v)
        {
            foreach($v as $groupKey)
                $permissionGroups[$groupKey] = $this->userGroups[$groupKey];
            $permissions[$k] = (array) $v;
        }
        $this->temp = array_merge($this->temp, $permissionGroups);

        $table = "<table id='com_thm_organizer_ce_permissions'>";

        $columngroup = "<colgroup>";
        $columngroup .= "<col id='com_thm_organizer_ce_usergroups' />";
        foreach($permissionNames as $name)
            $columngroup .= "<col id='com_thm_organizer_action_column' />";
        $columngroup .= "</colgroup>";

        $tablehead = "<thead>";
        $tablehead .= "<td>".JText::_('COM_THM_ORGANIZER_CE_USERGROUP_ACTIONS')."</td>";
        foreach($permissionNames as $name)
            $tablehead .= "<td>".JText::_($name)."</td>";
        $tablehead .= "</thead>";

        $tablebody = "<tbody>";
        foreach($permissionGroups as $groupKey => $groupName)
        {
            $tablerow = "<tr>";
            $tabledata = "<td align='right'>".$v."</td>";
            $tablerow .= $tabledata;
            $groupname = $this->userGroups[$groupKey];
            $groupPermissions = array();
            foreach($permissionNames as $permissionKey => $permissionValue)
            {
                $tabledata = "<td align='center'>";
                if(key_exists($k, $permissions[$permissionKey]))
                {
                   $tabledata .= JHTML::_('image', 'administrator/templates/bluestork/images/admin/tick.png',
                                            JText::_( 'Active' ), array( 'class' => 'thm_organizer_se_tick'));
                }
                else
                {
                   $tabledata .= JHTML::_('image', 'administrator/templates/bluestork/images/admin/tick.png',
                                            JText::_( 'Active' ), array( 'class' => 'thm_organizer_se_tick'));
                }
                $tabledata .= "</td>";
            }
            $tablerow .= $tabledata."</tr>";
            $tablebody .= $tablerow;
        }
        $table .= $columngroup.$tablehead.$tablebody."</table>";
        return $table;
    }

    public function store()
    {
        $post = print_r($_POST, true);

        $id = JRequest::getVar('id');
        $title = trim(JRequest::getString('title'));
        $alias = str_replace(' ', '_', strtolower($title));
        $description = trim(JRequest::getString('description'));
        $global = JRequest::getBool('global');
        $reserves = JRequest::getBool('reserves');
        $contentCatID = JRequest::getInt('contentCat');

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        if($id)
        {
            $query->update("__thm_organizer_categories");
            $conditions = "title = '$title', alias = '$alias', description = '$description', ";
            $conditions .= "globaldisplay = '$global', reservesobjects = '$reserves', ";
            $conditions .= "contentCatID = '$contentCatID' ";
            $query->set($conditions);
            $query->where("id = '$id'");
        }
        else
        {
            $statement = "#__thm_organizer_categories ";
            $statement .= "(title, alias, description, globaldisplay, reservesobjects, contentCatID) ";
            $statement .= "VALUES ";
            $statement .= "( '$title', '$alias', '$description', '$global','$reserves', '$contentCatID' );";
            $query->insert($statement);
        }
        $dbo->setQuery((string)$query);
        $dbo->query();
        if($dbo->getErrorNum()) return false;
        else return true;
    }
	
    public function delete()
    {
        global $mainframe;

        $ids = JRequest::getVar('cid', array(0), 'post', 'array');
        if(count( $ids ))
        {
            $idsString = "'".implode("', '", $ids);
            $dbo = & JFactory::getDBO();
            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_categories");
            $query->where("id in ( $idsString )");
            $dbo->setQuery( $query );
            $dbo->query();

            //TODO: delete associated events & content/resouce associations?

            if ($dbo->getErrorNum()) return false;
            else return true;
        }
        return true;
    }
}