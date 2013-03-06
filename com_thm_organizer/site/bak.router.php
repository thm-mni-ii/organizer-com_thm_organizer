<?php
/**
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_curriculum.site
 * @name		THM_Curriculum component site router
 * @description THM_Curriculum component site router
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

/**
 * Creates route for SEF
 *
 * @param   array  &$query  Array, containing the query
 *
 * @return  array  all SEF Elements as list
 */
function THM_curriculumBuildRoute(&$query)
{
	$segments = array();
	if (isset($query['view']))
	{
		$segments[] = $query['view'];
		unset($query['view']);
		if (isset($query['id']))
		{
			$segments[] = $query['id'];
			unset($query['id']);
		}
		elseif (isset($query['nrmni']))
		{
			$segments[] = $query['nrmni'];
			unset($query['nrmni']);
		}elseif (isset($query['gsuid']))
		{
			$segments[] = $query['gsuid'];
			unset($query['gsuid']);
		}
	}
	return $segments;
}

/**
 * Parses the route and calculates the parts from SEF
 *
 * @param   array  $segments  all SEF Elements as list
 *
 * @return  array  Accessable elements from SEF
 */
function THM_curriculumParseRoute($segments)
{
	$query = array();

	if (isset($segments[0]))
	{
		$query['view'] = $segments[0];

		if ($segments[0] == "groups")
		{
			if (isset($segments[1]))
			{
				$query['gsuid'] = $segments[1];
			}
		}
		elseif ($segments[0] == "details")
		{
			if (isset($segments[1]))
			{
				if (is_numeric($segments[1]))
				{
					$query['id'] = $segments[1];
				}
				else
				{
					$query['nrmni'] = $segments[1];
				}
			}
			if (isset($segments[2]))
			{
				$query['gsuid'] = $segments[1];
			}
		}
		elseif ($segments[0] == "lecturers")
		{
			if (isset($segments[1]))
			{
				$query['gsuid'] = $segments[1];
			}
		}
		elseif ($segments[0] == "index")
		{
			if (isset($segments[1]))
			{
				$query['gsuid'] = $segments[1];
			}
		}
		elseif ($segments[0] == "curriculum")
		{
			if (isset($segments[1]))
			{
				$query['gsuid'] = $segments[1];
			}
		}
	}
	return $query;
}
