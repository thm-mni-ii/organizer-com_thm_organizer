<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerHelperTeacher
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;

/**
 * Provides functions dealing with pool data
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerHelperPool
{
    /**
     * Creates a text for the required pool credit points
     *
     * @param   object  &$pool  the pool
     *
     * @return  string  the required amount of credit points
     */
    public static function getCrPText(&$pool)
    {
        $minCrPExists = !empty($pool->minCrP);
        $maxCrPExists = !empty($pool->maxCrP);
        if ($maxCrPExists)
        {
            if ($minCrPExists)
            {
                if ($pool->minCrP == $pool->maxCrP)
                {
                    return "$pool->maxCrP CrP";
                }

                return "$pool->minCrP - $pool->maxCrP CrP";
            }

            return "max. $pool->maxCrP CrP";
        }

        if ($minCrPExists)
        {
            return "min. $pool->minCrP CrP";
        }

        return '';
    }
}