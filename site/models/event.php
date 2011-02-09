<?php
/**
 * 
 * EditEvent Model for Giessen Times Component
 * 
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
/**
 * Room Model
 *
 */
class GiessenSchedulerModelEvent extends JModel
{
    var $data = null;

    /**
     * Constructor
     *
     * @since 1.5
     */
    function __construct()
    {
        global $mainframe;
        parent::__construct();
        $eventid = JRequest::getVar('eventid');
        if(!isset($eventid) || $eventid == '')$mainframe->redirect();
        $this->data = new stdClass();
        $this->data->eventid = $eventid;
        $this->loadData();
    }
	
    /**
    * Load object variable $event with the event from the db tables
    *
    * @param int $eventid the id of the event
    */
    function loadData()
    {
        global $mainframe;

        $data = & $this->data;
        $eventid = $data->eventid;

        $dbo = & JFactory::getDBO();
        $query = "SELECT contentid FROM #__thm_organizer_events WHERE eid = '$eventid'";
        $dbo->setQuery( $query );
        $savedcid = $dbo->loadResult();

        //check whether associated content was changed external to giessen scheduler
        if(isset($savedcid) && $savedcid != '0')
        {
            $query = "SELECT title FROM #__content WHERE id = '$savedcid' AND state != '-2'";
            $dbo->setQuery($query);
            $confirmtitle = $dbo->loadResult();
        }
        if(isset($confirmtitle))
        {
            $query = "SELECT gse.eid AS eventid, gse.title, gse.edescription AS description,
                             gse.created_by AS authorid, gse.startdate, gse.enddate, recurrence_type AS rec_type,
                             SUBSTR(starttime, 1, 5) AS starttime, SUBSTR(endtime, 1, 5) AS endtime,
                             c.id AS contentid, c.catid AS ccatid,
                             DATE(publish_up) AS publish_up, DATE(publish_down) AS publish_down,
                             sect.title AS sectname, sect.description AS secttext, cats.title AS ccatname,
                             ecname, ec.access, ec.ecdescription, u.name AS author, reservingp, globalp
                     FROM #__thm_organizer_events AS gse
                        INNER JOIN #__content AS c ON id = contentid
                        INNER JOIN #__sections AS sect ON sect.id = c.sectionid
                        INNER JOIN #__categories AS cats ON cats.id = c.catid
                        INNER JOIN #__thm_organizer_categories AS ec ON ecatid = ecid
                        INNER JOIN #__users AS u ON gse.created_by = u.id
                     WHERE eid='$eventid'";
            $dbo->setQuery( $query );
            $fetchedevent = $dbo->loadAssoc();
        }
        else
        {
            $query = "SELECT gse.eid AS eventid, gse.title, edescription AS description, gse.created_by AS authorid,
                        u.name AS author, ecname, startdate, enddate, SUBSTR(starttime, 1, 5) AS starttime,
                        SUBSTR(endtime, 1, 5) AS endtime,  ec.access, ec.ecdescription, reservingp, globalp,
                        recurrence_type AS rec_type
                     FROM #__thm_organizer_events AS gse
                     INNER JOIN #__thm_organizer_categories AS ec ON ecatid = ecid
                     INNER JOIN #__users AS u ON gse.created_by = u.id
                     WHERE eid='$eventid'";
            $dbo->setQuery( $query );
            $fetchedevent = $dbo->loadAssoc();
        }
        if(isset($fetchedevent))
        {
            $user =& JFactory::getUser();
            $gid = $user->gid;
            if(isset($fetchedevent['access']) && $fetchedevent['access']  > $gid) $mainframe->redirect();
            if(isset($fetchedevent['enddate']) && $fetchedevent['enddate'] == '0000-00-00')
                unset($fetchedevent['enddate']);
            if(isset($fetchedevent['starttime']) && ($fetchedevent['starttime'] == '00:00' || $fetchedevent['starttime'] == ''))
                unset($fetchedevent['starttime']);
            if(isset($fetchedevent['endtime']) && ($fetchedevent['endtime'] == '00:00' || $fetchedevent['endtime'] == ''))
                unset($fetchedevent['endtime']);
            if($fetchedevent['reservingp'] && $fetchedevent['globalp'] )
                $fetchedevent['displaybehaviour'] = 'Wird auf jedem Monitor als Hinweis angezeigt, sollte er eine Ressource in einem Raum belegen wird er besonders gekennzeichnet.';
            else if($fetchedevent['reservingp'])
                $fetchedevent['displaybehaviour'] = 'Wird auf jedem Monitor angezeigt, wo eine Ressource betroffen ist und belegt diese Ressource.';
            else if($fetchedevent['globalp'])
                $fetchedevent['displaybehaviour'] = 'Wird auf jedem Monitor als Hinweis angezeigt.';
            else
                $fetchedevent['displaybehaviour'] = 'Wird auf jedem Monitor angezeigt, wo eine Ressource betroffen ist, belegt aber diese Ressource nicht.';
        }
        foreach($fetchedevent as $k => $v)
        {
            $v = preg_replace("/<p[^>]+\>/i", "", $v);
            $v = preg_replace("/<\/p[^>]+\>/i", "", $v);
            $data->$k = $v;
        }
        $query = "SELECT oname
                  FROM #__thm_organizer_objects
                    INNER JOIN #__thm_organizer_eventobjects ON objectid = oid
                  WHERE eventid = '$eventid'
                    AND otype = 'teacher'";
        $dbo->setQuery( $query );
        $data->teachers = $dbo->loadResultArray();
        $query = "SELECT oname
                  FROM #__thm_organizer_objects
                    INNER JOIN #__thm_organizer_eventobjects ON objectid = oid
                  WHERE eventid = '$eventid'
                    AND otype = 'class'";
        $dbo->setQuery( $query );
        $data->classes = $dbo->loadResultArray();
        $query = "SELECT oname
                  FROM #__thm_organizer_objects
                    INNER JOIN #__thm_organizer_eventobjects ON objectid = oid
                  WHERE eventid = '$eventid'
                    AND otype = 'room'";
        $dbo->setQuery( $query );
        $data->rooms = $dbo->loadResultArray();
        $query = "SELECT name AS oname
                  FROM #__giessen_staff_groups
                  INNER JOIN #__thm_organizer_eventobjects ON objectid = id
                  WHERE eventid = '$eventid'";
        $dbo->setQuery( $query );
        $query2 = $query;
        $data->usergroups = $dbo->loadResultArray();
    }
}
?>