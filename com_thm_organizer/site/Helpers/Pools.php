<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

defined('_JEXEC') or die;

/**
 * Provides general functions for (subject) pool access checks, data retrieval and display.
 */
class Pools
{
    /**
     * Creates a text for the required pool credit points
     *
     * @param object $pool the pool
     *
     * @return string  the required amount of credit points
     */
    public static function getCrPText($pool)
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

    /**
     * Retrieves the pool's full name if existent.
     *
     * @param int $poolID the table's pool id
     *
     * @return string the full name, otherwise an empty string
     */
    public static function getFullName($poolID)
    {
        $table  = OrganizerHelper::getTable('Groups');
        $exists = $table->load($poolID);

        return $exists ? $table->full_name : '';
    }

    /**
     * Retrieves the pool's full name if existent.
     *
     * @param int    $poolID the table's pool id
     * @param string $type   the pool's type (real|plan)
     *
     * @return string the full name, otherwise an empty string
     */
    public static function getName($poolID, $type = 'plan')
    {
        if ($type == 'plan') {
            $table  = OrganizerHelper::getTable('Groups');
            $exists = $table->load($poolID);

            return $exists ? $table->name : '';
        }

        $table  = OrganizerHelper::getTable('Pools');
        $exists = $table->load($poolID);

        if (!$exists) {
            return '';
        }

        $languageTag = Languages::getShortTag();

        if (!empty($table->{'name_' . $languageTag})) {
            return $table->{'name_' . $languageTag};
        } elseif (!empty($table->{'short_name_' . $languageTag})) {
            return $table->{'short_name_' . $languageTag};
        }

        return !empty($table->{'abbreviation_' . $languageTag}) ? $table->{'abbreviation_' . $languageTag} : '';

    }
}
