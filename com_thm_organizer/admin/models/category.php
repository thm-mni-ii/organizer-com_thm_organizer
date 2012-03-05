<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model category
 * @description database abstraction file for category persistence
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_SITE.'/components/com_thm_organizer/models/events.php';
class thm_organizersModelcategory extends JModel
{
    /**
     * save
     *
     * saves the event category
     *
     * @return bool true on success, otherwise false
     */
    public function save()
    {
        $id = JRequest::getVar('id');
        $title = addslashes(trim(JRequest::getString('title')));
        $description = addslashes(trim($_REQUEST['description']));
        $global = JRequest::getBool('global');
        $reserves = JRequest::getBool('reserves');
        $contentCatID = JRequest::getInt('contentCat');

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        if($id)
        {
            $query->update("#__thm_organizer_categories");
            $conditions = "title = '$title', description = '$description', ";
            $conditions .= "globaldisplay = '$global', reservesobjects = '$reserves', ";
            $conditions .= "contentCatID = '$contentCatID' ";
            $query->set($conditions);
            $query->where("id = '$id'");
        }
        else
        {
            $statement = "#__thm_organizer_categories ";
            $statement .= "(title, description, globaldisplay, reservesobjects, contentCatID) ";
            $statement .= "VALUES ";
            $statement .= "( '$title', '$description', '$global','$reserves', '$contentCatID' );";
            $query->insert($statement);

        }
        $dbo->setQuery((string)$query);
        $dbo->query();
        return ($dbo->getErrorNum())? false : true;
    }

    /**
     * delete
     *
     * deletes event categories and associated events/event-resource associations
     *
     * @return bool true on success, otherwise false
     */
    public function delete()
    {
        $categoryIDs = array();
        $categoryIDs[0] = JRequest::getInt('id');
        if(empty($categoryIDs[0]))
            $categoryIDs = JRequest::getVar('cid', array(0), 'post', 'array');
        if(count($categoryIDs))
        {
            $categoryIDs = "( '".implode("', '", $categoryIDs)."' )";
            $dbo = & JFactory::getDBO();

            //remove events & event resources dependant upon this category
            $query = $dbo->getQuery(true);
            $query->select("DISTINCT (id)");
            $query->from("#__thm_organizer_events");
            $query->where("categoryID IN $categoryIDs");
            $dbo->setQuery((string)$query);
            $eventIDs = $dbo->loadResultArray();
            if(count($eventIDs))
            {
                $events = new thm_organizerModelevents();
                foreach($eventIDs as $eventID)
                {
                    $success = $events->delete($eventID);
                    if(!$success)return false;
                }
            }

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_categories");
            $query->where("id IN $categoryIDs");
            $dbo->setQuery((string)$query);
            $dbo->query();

            return ($dbo->getErrorNum())? false : true;
        }
        return true;
    }
}