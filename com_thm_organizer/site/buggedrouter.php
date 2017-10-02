<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @description the base file for the component frontend
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Build the route for the com_thm_organizer component
 *
 * @param array &$query an array of URL arguments
 *
 * @return  array  the URL arguments to use to assemble the subsequent URL.
 */
function THM_organizerBuildRoute(&$query)
{
	$segments = [];
	$menu     = JFactory::getApplication()->getMenu();
	$item     = empty($query['Itemid']) ?
		$menu->getActive() : $menu->getItem($query['Itemid']);
	$view     = empty($query['view']) ? $item->query['view'] : $query['view'];

	// Invalid
	if (empty($view))
	{
		return $segments;
	}

	switch ($view)
	{
		case 'subject_details':
			setSubjectDetailsSegments($query, $segments);
			break;
		case 'subject_list':
			setSubjectListSegments($query, $segments, $item);
			break;
		case 'event_manager':
		default:
			break;
	}

	return $segments;
}

/**
 * Sets the segments necessary for the event details view
 *
 * @param array &$query    the url query parameters
 * @param array &$segments the segments for the sef url
 *
 * @return  void
 */
function setSubjectDetailsSegments(&$query, &$segments)
{
	if (empty($query['id']))
	{
		return;
	}

	$segments[] = $query['view'];
	unset($query['view']);

	$tag        = getLanguageTag($query);
	$segments[] = $tag;

	$dbo       = JFactory::getDbo();
	$nameQuery = $dbo->getQuery(true);
	$nameQuery->select("name_$tag")->from('#__thm_organizer_subjects')->where("id = '{$query['id']}'");
	$dbo->setQuery($nameQuery);

	try
	{
		$name = $dbo->loadResult();
	}
	catch (Exception $exc)
	{
		JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

		return;
	}

	$safeName   = JFilterOutput::stringURLSafe($name);
	$segments[] = empty($safeName) ? $query['id'] : "{$query['id']}:$safeName";
	unset($query['id']);

	setItemidSegment($query, $segments);
}

/**
 * Sets the segments necessary for the subject list view
 *
 * @param array  &$query    the url query parameters
 * @param array  &$segments the segments for the sef url
 * @param object &$item     the associated menu item (if applicable)
 *
 * @return  void
 */
function setSubjectListSegments(&$query, &$segments, &$item)
{
	$programID = $item->params->get('programID');

	if ($item->query['view'] == 'subject_list' AND !isset($query['groupBy']) AND !isset($query['languageTag'])
		OR empty($programID)
	)
	{
		return;
	}

	if (!empty($query['view']))
	{
		unset($query['view']);
	}

	$dbo          = JFactory::getDbo();
	$programQuery = $dbo->getQuery(true);
	$select       = ["p.subject", "' ('", "d.abbreviation", "' '", "p.version", "')'"];
	$programQuery->select($programQuery->concatenate($select, ""));
	$programQuery->from('#__thm_organizer_programs AS p')->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
	$programQuery->where("p.id = '$programID'");
	$dbo->setQuery($programQuery);

	try
	{
		$name = $dbo->loadResult();
	}
	catch (Exception $exc)
	{
		JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

		return;
	}

	$segments[] = 'subject_list:' . JFilterOutput::stringURLSafe($name);
	$segments[] = getLanguageTag($query);
	setItemidSegment($query, $segments);
}

/**
 * Gets the language tag, either from the query or the joomla framework
 *
 * @param array &$query the url query
 *
 * @return  string  the language tag
 */
function getLanguageTag(&$query)
{
	$activeLanguage = explode('-', JFactory::getLanguage()->getTag());
	$tag            = empty($query['languageTag']) ? $activeLanguage[0] : $query['languageTag'];
	if (isset($query['languageTag']))
	{
		unset($query['languageTag']);
	}

	return $tag;
}

/**
 * Sets the item id segment if existent
 *
 * @param array &$query    the url query as array
 * @param array &$segments the sequential parameters
 *
 * @return void
 */
function setItemidSegment(&$query, &$segments)
{
	if (!empty($query['Itemid']))
	{
		$segments[] = $query['Itemid'];
	}
	if (isset($query['Itemid']))
	{
		unset($query['Itemid']);
	}
}

/**
 * Parse the segments of a URL.
 *
 * @param array $segments the segments of the URL to parse.
 *
 * @return  array  the URL attributes to be used by the application.
 */
function THM_organizerParseRoute($segments)
{
	$vars = [];
	if (empty($segments))
	{
		return $vars;
	}

	$viewArray    = explode(':', $segments[0]);
	$vars['view'] = $viewArray[0];
	switch ($vars['view'])
	{
		case 'subject_details':
			$vars['languageTag'] = $segments[1];
			$idArray             = explode(':', $segments[2]);
			$vars['id']          = $idArray[0];
			if (!empty($segments[3]))
			{
				$vars['Itemid'] = $segments[3];
			}
			break;
		case 'subject_list':
			$vars['languageTag'] = $segments[1];
			$groupBy             = $segments[2];
			switch ($groupBy)
			{
				case 'alphabetical':
					$vars['groupBy'] = '0';
					break;
				case 'bypool';
					$vars['groupBy'] = '1';
					break;
				case 'byteacher';
					$vars['groupBy'] = '2';
					break;
				case 'byfield';
					$vars['groupBy'] = '3';
					break;
			}
			if (!empty($segments[3]))
			{
				$vars['Itemid'] = $segments[3];
			}
			break;
		default:
			break;
	}

	return $vars;
}
