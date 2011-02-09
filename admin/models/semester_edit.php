<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester editor model
 * @description db abstraction file for the editing  of semester entries
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class thm_organizersModelsemester_edit extends JModel
{
    public $sid;
    public $orgunit;
    public $semester;
    public $author;
    public $userGroups;

    function __construct()
    {
        parent::__construct();
        $this->getData();
        $this->getUserGroups();
    }

    function getData()
    {
        $sids = JRequest::getVar('cid',  null, '', 'array');
        if(!empty($sids)) $sid = $sids[0];
        if(!isset($sid)) $sid = JRequest::getVar('semester');
        if(is_numeric($sid) and $sid != 0)
        {
            $dbo = JFactory::getDBO();
            $query = $dbo->getQuery(true);
            $query->select("*");
            $query->from("#__thm_organizer_semesters");
            $query->where("sid = '$sid'");
            $dbo->setQuery((string)$query);
            $semesterData = $dbo->loadAssoc();
            if(!empty($semesterData))
                foreach($semesterData as $k => $v)$this->$k = $v;
        }
        else
        {
            $this->sid = 0;
            $this->author = 0;
            $this->orgunit = '';
            $this->semester = '';
        }
    }

    /**
     * private function getSemesters
     *
     * gets the IDs and names of the available semesters
     */
    private function getUserGroups()
    {
        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('id, title');
        $query->from('#__usergroups');
        $dbo->setQuery((string)$query);
        $usergroups = $dbo->loadAssocList();

        foreach($usergroups as $k => $v)
            if(!JAccess::checkGroup($v->id, 'core.login.admin'))
                unset($usergroups[$k]);

    $query = "SELECT DISTINCT username as id, name as name
          FROM #__users INNER JOIN #__user_usergroup_map ON #__users.id = user_id INNER JOIN #__usergroups ON group_id = #__usergroups.id WHERE";
    $first = true;
    if(is_array($usergroups))
    {
      foreach($usergroups as $k=>$v)
      {
          if($first != true)
            $query .= " OR";
          $query .= " #__usergroups.id = ".(int)$v;
          $first = false;
      }
    }
    $query .= " ORDER BY name";
    $dbo->setQuery( $query );
    $resps = $dbo->loadObjectList();
    return $resps;
  }
    }

    function store()
    {
        global $mainframe;
        //Sanitize
        $sid = JRequest::getVar('id');
        $author = trim(JRequest::getVar( 'author', '', 'post','string', JREQUEST_ALLOWRAW ));
        $orgunit = trim(JRequest::getVar( 'orgunit', '', 'post','string', JREQUEST_ALLOWRAW ));
        $semester = trim(JRequest::getVar( 'semester', '', 'post','string', JREQUEST_ALLOWRAW ));

        $dbo = & JFactory::getDBO();
        if($sid == 0)
                $query = "INSERT INTO #__giessen_scheduler_semester (author, orgunit, semester)
                                        VALUES ( '$author', '$orgunit', '$semester' );";
        else
                $query = "UPDATE #__giessen_scheduler_semester
                                                 SET author = '$author',
                                                         orgunit = '$orgunit',
                                                         semester = '$semester'
                                                WHERE sid = '$sid';";
        $dbo->setQuery($query);
        $dbo->query();
        if($dbo->getErrorNum())
        {
                return JText::_("Es darf nur ein Eintrag pro Org. Einheit & Semeseter geben.");
        }
        else return JText::_("Erfolgreich gespeichert.");
    }

    function delete()
    {
        global $mainframe;

        $ids = JRequest::getVar( 'cid' );
        if (count( $ids ))
        {
                $where = "";
                foreach($ids as $id)
                {
                        if($where != "") $where .= ", ";
                        $where .= "$id";
                }
                $dbo = & JFactory::getDBO();
                $query = "DELETE FROM #__giessen_scheduler_semester WHERE sid IN ( $where );";
                $dbo->setQuery( $query );
                $dbo->query();
                if ($dbo->getErrorNum())
                {
                        return false;
                }
        }
        return true;
    }
}