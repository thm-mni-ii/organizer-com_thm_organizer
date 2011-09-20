<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model category editor view
 * @description database abstraction file for the category editor view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     1.7.0
 */
defined('_JEXEC') or die;
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


    public function __construct()
    {
        parent::__construct();
        $this->loadCategory();
        $this->loadUserGroups();
        $this->loadContentCategories();
        if($this->contentCat == 0)
            foreach($this->contentCategories as $category)
            {
                $this->contentCat = $category['id'];
                break;
            }
    }

    /**
     * loadCategory
     *
     * loads saved category information into object variables if available
     */
    private function loadCategory()
    {
        $id = JRequest::getInt('categoryID');
        if(empty($id))
        {
            $ids = JRequest::getVar('cid',  0, '', 'array');
            $id = (int)$ids[0];
        }
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

    /**
     * loadContentCategories
     *
     * retrieves a list of published content categories and their properties
     * from the database
     */
    private function loadContentCategories()
    {
        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("c.id, c.title, c.description, vl.title AS view_level");
        $query->from("#__categories AS c");
        $query->innerJoin("#__viewlevels AS vl ON c.access = vl.id");
        $query->where("extension = 'com_content'");
        $query->where("published = '1'");
        $query->order("c.title ASC");
        $dbo->setQuery((string)$query);
        $contentCategories = $dbo->loadAssocList();
        if(count($contentCategories))
        {
            foreach($contentCategories as $k => $v)
                $contentCategories[$k]['actions'] = $this->makeActionsTable($contentCategories[$k]['id']);
            $this->contentCategories = $contentCategories;
        }
        else $this->contentCategories = array();
    }

    /**
     * loadUserGroups
     *
     * creates an array associating user group ids to user group names and loads
     * it into $this->userGroups
     */
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

    /**
     * makeActionsTable
     *
     * creates a table illustration which usergroups have which rights to act on
     * a particular content category
     *
     * @param int $id
     * @return string
     */
    private function makeActionsTable($id)
    {
        $actions = array( 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete' );
        $asset = "com_content".".category.".$id;

        $table = "<table id='com_thm_organizer_ce_permissions'>";

        $columngroup = "<colgroup>";
        $columngroup .= "<col id='com_thm_organizer_ce_usergroups' />";
        foreach($actions as $action)
            $columngroup .= "<col id='com_thm_organizer_ce_action_column' />";
        $columngroup .= "</colgroup>";
        $table .= $columngroup;

        $tablehead = "<thead id='thm_organizer_ce_actions_head' class='row1'>";
        foreach($actions as $action)
        {
            $name = str_replace('CORE', '', str_replace('.', ' ', strtoupper($action)));
            $tablehead .= "<td align='center'>".JText::_($name)."</td>";
        }
        $tablehead .= "<td class='thm_organizer_ce_leftcolumn' />";
        $tablehead .= "</thead>";
        $table .= $tablehead;

        $rowcount = 0;
        $tablebody = "<tbody>";
        foreach($this->userGroups as $k => $v)
        {
            $found = false;
            $rowclass = $rowcount % 2 == 0? 'row0' : 'row1';
            $tablerow = "<tr class='$rowclass'>";
            foreach($actions as $action)
            {
                $tabledata = "<td align='center'>";
                $access = JAccess::checkGroup($k, $action, $asset);
                if($access)
                {
                   $tabledata .= JHTML::_('image', 'administrator/templates/bluestork/images/admin/tick.png',
                                            JText::_( 'COM_THM_ORGANIZER_ALLOWED' ), array( 'class' => 'thm_organizer_se_tick'));
                   $found = true;
                }
                else
                {
                   $tabledata .= JHTML::_('image', 'administrator/templates/bluestork/images/admin/publish_x.png',
                                            JText::_( 'COM_THM_ORGANIZER_DENIED' ), array( 'class' => 'thm_organizer_se_tick'));
                }
                $tabledata .= "</td>";
                $tablerow .= $tabledata;
            }
            $tabledata = "<td align='left' class='thm_organizer_ce_leftcolumn'>".$v."</td>";
            $tablerow .= $tabledata;
            $tablerow .= "</tr>";
            if($found)
            {
                $tablebody .= $tablerow;
                $rowcount++;
            }
        }

        $table .= $tablebody."</table>";
        if($rowcount)return $table;
        else return '';
    }
}