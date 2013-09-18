<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @description the base file for the component frontend
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Build the route for the com_thm_organizer component
 *
 * @param   array  &$query  an array of URL arguments
 *
 * @return  array  the URL arguments to use to assemble the subsequent URL.
 */
function THM_organizerBuildRoute(&$query)
{
    $segments = array();
    if (!empty($query['view']))
    {
        $view = $query['view'];
        $segments[] = $view;
        unset($query['view']);

        $activeLanguage = explode('-', JFactory::getLanguage()->getTag());
        $languageTag = empty($query['languageTag'])? $activeLanguage[0] : $query['languageTag'];
        unset($query['languageTag']);

        switch ($view)
        {
            case 'event_details':
            case 'event_edit':
                $segments[] = !empty($query['eventID'])?
                    getEventSegment($query['eventID']) : '0-new-event';
                unset($query['eventID']);
                break;
            case 'subject_details':
                $segments[] = $languageTag;
                $segments[] = getSubjectSegment($query['id']);
                unset($query['id']);
                break;
            case 'subject_list':
                $segments[] = $languageTag;
                $segments[] = getGroupBySegment(isset($query['groupBy'])? $query['groupBy'] : '0');
                unset($query['groupBy']);
            case 'scheduler':
            case 'event_manager':
            default:
                break;
        }
        if (isset ($query['Itemid']))
        {
            $segments[] = $query['Itemid'];
            unset($query['Itemid']);
        }
    }
    return $segments;
}

/**
 * Parse the segments of a URL.
 *
 * @param   array  $segments  the segments of the URL to parse.
 *
 * @return  array  the URL attributes to be used by the application.
 */
function THM_organizerParseRoute($segments)
{
    $vars = array();
    if (empty($segments))
    {
        return $vars;
    }

    $vars['view'] = $segments[0];
    switch ($vars['view'])
    {
        case 'event_details':
        case 'event_edit':
            $vars['eventID'] = explode(':', $segments[1])[0];
            if (!empty($segments[2]))
            {
                $vars['Itemid'] = $segments[2];
            }
            break;
        case 'subject_details':
            $vars['languageTag'] = $segments[1];
            $vars['id'] = explode(':', $segments[2])[0];
            if (!empty($segments[3]))
            {
                $vars['Itemid'] = $segments[3];
            }
            break;
        case 'subject_list':
            if (count($segments) <= 1)
            {
                $activeLanguage = explode('-', JFactory::getLanguage()->getTag());
                $vars['languageTag'] = $activeLanguage[0];
                if (!empty($segments[1]))
                {
                    $vars['Itemid'] = $segments[1];
                }
                
                break;
            }
            $vars['languageTag'] = $segments[1];
            $vars['groupBy'] = explode(':', $segments[2])[0];
            if (!empty($segments[3]))
            {
                $vars['Itemid'] = $segments[3];
            }
            break;
        case 'scheduler':
        case 'event_manager':
        default:
            break;
    }
    return $vars;
}

/**
 * Creates a human readable event segment
 * 
 * @param   string  $eventID  the id of the event
 * 
 * @return  string  the alias (if available) and event id
 */
function getEventSegment($eventID)
{
    $dbo = JFactory::getDbo();
    $query = $dbo->getQuery(true);
    $query->select('alias')->from('#__content')->where("id = '$eventID'");
    $dbo->setQuery((string) $query);
    $alias = $dbo->loadResult();
    return empty($alias)? $eventID : "$eventID:$alias";
}

/**
 * Creates a human readable event segment
 * 
 * @param   string  $subjectID  the id of the event
 * 
 * @return  string  the alias (if available) and event id
 */
function getSubjectSegment($subjectID)
{
    $languageTag = explode('-', JFactory::getLanguage()->getTag());
    $dbo = JFactory::getDbo();
    $query = $dbo->getQuery(true);
    $query->select("name_{$languageTag[0]}")->from('#__thm_organizer_subjects')->where("id = '$subjectID'");
    $dbo->setQuery((string) $query);
    $name = $dbo->loadResult();
    $safeName = JFilterOutput::stringURLSafe($name);
    return empty($safeName)? $subjectID : "$subjectID:$safeName";
}

/**
 * Creates a human readable event segment
 * 
 * @param   string  $groupingID  the id grouping
 * 
 * @return  string  the alias (if available) and event id
 */
function getGroupBySegment($groupBy)
{
    switch ($groupBy)
    {
        case '1':
            return '1:bysubject';
        case '2':
            return '2:byteacher';
        case '3':
            return '3:byfield';
        case '0':
            return '0:alphabetical';
    }
}