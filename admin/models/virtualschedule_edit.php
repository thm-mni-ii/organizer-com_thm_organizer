<?php
defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class GiessenSchedulersModelvirtualschedule_edit extends JModel
{
  function __construct(){
     parent::__construct();
    }

    function getTypes()
  {
    $types[]["id"] = "class";
    $types[count($types)-1]["name"] = "Semester";
    $types[]["id"] = "room";
    $types[count($types)-1]["name"] = "Room";
    $types[]["id"] = "teacher";
    $types[count($types)-1]["name"] = "Teacher";
    return $types;
  }

  function getResponsibles()
  {
    $mainframe = JFactory::getApplication("administrator");
    $dbo = & JFactory::getDBO();
    $usergroups = array();

    $query = $dbo->getQuery(true);
    $query->select('id');
    $query->from('#__usergroups');
    $dbo->setQuery((string)$query);
    $groups = $dbo->loadObjectList();

    foreach($groups as $k=>$v)
    {
      if(JAccess::checkGroup($v->id, 'core.login.admin'))
      {
		$usergroups[] = $v->id;
      }
    }

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

  function getClasses()
  {
    $mainframe = JFactory::getApplication("administrator");
    $dbo = & JFactory::getDBO();
    $query = "SELECT cid as id, CONCAT(department, ' ', semester) as name
          FROM #__giessen_scheduler_classes
          ORDER BY name";
    $dbo->setQuery( $query );
    $classes = $dbo->loadObjectList();
    return $classes;
  }

  function getRooms()
  {
    $mainframe = JFactory::getApplication("administrator");
    $dbo = & JFactory::getDBO();
    $query = "SELECT rid as id, oname as name
          FROM #__giessen_scheduler_rooms
          INNER JOIN #__giessen_scheduler_objects
          ON rid = oid
          ORDER BY name";
    $dbo->setQuery( $query );
    $rooms = $dbo->loadObjectList();
    return $rooms;
  }

  function getTeachers()
  {
    $mainframe = JFactory::getApplication("administrator");
    $dbo = & JFactory::getDBO();
    $query = "SELECT tid as id, oname as name
          FROM #__giessen_scheduler_teachers
          INNER JOIN #__giessen_scheduler_objects
          ON tid = oid
          ORDER BY name";
    $dbo->setQuery( $query );
    $teachers = $dbo->loadObjectList();
    return $teachers;
  }

    function getSemesters()
  {
    $mainframe = JFactory::getApplication("administrator");
    $dbo = & JFactory::getDBO();
    $query = "SELECT sid as id, Concat(orgunit, '-', semester, ' (', author, ')') as name
          FROM #__giessen_scheduler_semester
          ORDER BY name";
    $dbo->setQuery( $query );
    $semesters = $dbo->loadObjectList();
    return $semesters;
  }

  /**
   * Holt alle Departments aus der Datenbank die zum $type passen
   */
    function getRoomDepartments()
  {
    $mainframe = JFactory::getApplication("administrator");
    $dbo = & JFactory::getDBO();
    $query = "SELECT DISTINCT CONCAT(department, '-', rtype) as id, CONCAT(department, '-', rtype) as name
          FROM #__giessen_scheduler_rooms
          ORDER BY department";
    $dbo->setQuery( $query );
    $departments = $dbo->loadObjectList();
    return $departments;
  }

  /**
   * Holt alle Departments aus der Datenbank die zum $type passen
   */
    function getDepartments($type)
  {
    $mainframe = JFactory::getApplication("administrator");
    $dbo = & JFactory::getDBO();
    $query = "SELECT DISTINCT department as id, department as name
          FROM #__giessen_scheduler_".$type."
          ORDER BY department";
    $dbo->setQuery( $query );
    $departments = $dbo->loadObjectList();
    return $departments;
  }

  /**
   * Holt alle Typen von Räumen
   */
    function getRoomTypes()
  {
    $mainframe = JFactory::getApplication("administrator");
    $dbo = & JFactory::getDBO();
    $query = "SELECT DISTINCT rtype as id, rtype as name
          FROM #__giessen_scheduler_rooms
          ORDER BY name";
    $dbo->setQuery( $query );
    $roomType = $dbo->loadObjectList();
    return $roomType;
  }

  /**
   * Holt alle Typen von Classes
   */
    function getClassTypes()
  {
    $mainframe = JFactory::getApplication("administrator");
    $dbo = & JFactory::getDBO();
    $query = "SELECT DISTINCT semester as id, semester as name
          FROM #__giessen_scheduler_classes
          ORDER BY name";
    $dbo->setQuery( $query );
    $classTypes = $dbo->loadObjectList();
    return $classTypes;
  }

  function idExists($id)
  {
    $mainframe = JFactory::getApplication("administrator");
    $dbo = & JFactory::getDBO();
    $query = "SELECT count(vid) as id_anz
          FROM #__giessen_scheduler_virtual_schedules
          WHERE vid = '".$id."';";
    $dbo->setQuery( $query );
    $id_anz = $dbo->loadObjectList();
    if($id_anz[0]->id_anz == "0")
      return false;
    return true;
  }

  function saveVScheduler($vscheduler_id,
              $vscheduler_name,
              $vscheduler_types,
              $vscheduler_semid,
              $vscheduler_resps,
              $vscheduler_Departments,
              $vscheduler_elements)
  {
    if($vscheduler_id == null)
    {
      $vscheduler_id = "VS_".$vscheduler_name;
    }
    else
    {
      $this->remove($vscheduler_id);
    }

    $mainframe = JFactory::getApplication("administrator");
    $dbo = & JFactory::getDBO();
    $query = "";
    $query = "INSERT INTO #__giessen_scheduler_virtual_schedules (vid, vname, vtype, vresponsible, department, sid)
          VALUES ( '".$vscheduler_id."', '".$vscheduler_name."', '".$vscheduler_types."', '".$vscheduler_resps."', '".$vscheduler_Departments."', '".$vscheduler_semid."' ); ";
    $dbo->setQuery( $query );
    $dbo->query();
    if ($dbo->getErrorNum())
    {
      return "0";
    }
    else
    {
      $query = "";
      foreach($vscheduler_elements as $v)
      {
        $query = "INSERT INTO #__giessen_scheduler_virtual_schedules_elements (vid, eid, sid)
            VALUES ( '".$vscheduler_id."', '".$v."', '".$vscheduler_semid."' ); ";
        $dbo->setQuery( $query );
        $dbo->query();
        if ($dbo->getErrorNum())
        {
          foreach($vscheduler_elements as $i)
          {
            $query = "DELETE FROM #__giessen_scheduler_virtual_schedules_elements
               WHERE vid = '".$vscheduler_id."'";
            $dbo->setQuery( $query );
            $dbo->query();
          }
          $query = "DELETE FROM #__giessen_scheduler_virtual_schedules
               WHERE vid = '".$vscheduler_id."'; ";
          $dbo->setQuery( $query );
          $dbo->query();
          return "0";
        }
      }
      return "1";
    }
  }

  function remove($id)
  {
    $mainframe = JFactory::getApplication("administrator");
    $dbo = & JFactory::getDBO();

    $query = 'DELETE FROM #__giessen_scheduler_virtual_schedules'
             . ' WHERE vid IN ( "'. $id .'" );';

    $dbo->setQuery( $query );
        $dbo->query();

        if ($dbo->getErrorNum())
    {
      return 0;
    }
    else
    {
      $query = 'DELETE FROM #__giessen_scheduler_virtual_schedules_elements'
             . ' WHERE vid IN ( "'. $id .'" );';

      $dbo->setQuery( $query );
          $dbo->query();
    }
    return true;
  }

  function getData($id)
  {
    $mainframe = JFactory::getApplication("administrator");
    $dbo = & JFactory::getDBO();
    $query='SELECT * FROM #__giessen_scheduler_virtual_schedules ' .
        'INNER JOIN #__giessen_scheduler_virtual_schedules_elements ' .
        'ON #__giessen_scheduler_virtual_schedules.vid = #__giessen_scheduler_virtual_schedules_elements.vid ' .
        'WHERE #__giessen_scheduler_virtual_schedules.vid = "'.$id.'"';
    $dbo->setQuery( $query );
    $dbo->query();
    if ($dbo->getErrorNum())
    {
      return "0";
    }
    else
    {
      $data = $dbo->loadObjectList();
    }
    return $data;
  }
}
?>
