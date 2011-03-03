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


    public function __construct()
    {
        parent::__construct();
        $this->loadCategory();
        $this->loadContentCategories();
        $this->loadUserGroups();
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
        $query->select("c.id, c.title, c.description, rules");
        $query->from("#__categories AS c");
        $query->innerJoin("#__assets AS a ON c.asset_id = a.id");
        $query->where("extension = 'com_content'");
        $query->order("c.title ASC");
        $dbo->setQuery((string)$query);
        $contentCategories = $dbo->loadObjectList();
        if(count($contentCategories))
            $this->contentCategories = $contentCategories;
    }

    private function loadUserGroups()
    {
        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("c.id, c.title, c.description, rules");
        $query->from("#__categories AS c");
        $query->innerJoin("#__assets AS a ON c.asset_id = a.id");
        $query->where("extension = 'com_content'");
        $query->order("c.title ASC");
        $dbo->setQuery((string)$query);
        $contentCategories = $dbo->loadObjectList();
        if(count($contentCategories))
            $this->contentCategories = $contentCategories;
    }

    public function store()
    {
        $post = print_r($_POST, true);

        //Sanitize
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