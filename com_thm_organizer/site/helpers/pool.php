<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

/**
 * Provides general functions for (subject) pool access checks, data retrieval and display.
 */
class THM_OrganizerHelperPool
{
    /**
     * Creates a text for the required pool credit points
     *
     * @param object &$pool the pool
     *
     * @return string  the required amount of credit points
     */
    public static function getCrPText(&$pool)
    {
        $minCrPExists = !empty($pool->minCrP);
        $maxCrPExists = !empty($pool->maxCrP);
        if ($maxCrPExists) {
            if ($minCrPExists) {
                if ($pool->minCrP == $pool->maxCrP) {
                    return "$pool->maxCrP CrP";
                }

                return "$pool->minCrP - $pool->maxCrP CrP";
            }

            return "max. $pool->maxCrP CrP";
        }

        if ($minCrPExists) {
            return "min. $pool->minCrP CrP";
        }

        return '';
    }
}