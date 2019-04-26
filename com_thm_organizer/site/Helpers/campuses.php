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

require_once 'OrganizerHelper.php';

use Joomla\CMS\Factory;

/**
 * Provides general functions for campus access checks, data retrieval and display.
 */
class THM_OrganizerHelperCampuses
{
    /**
     * Creates a link to the campus' location
     *
     * @param int $campusID the id of the campus
     *
     * @return string the HTML for the location link
     */
    public static function getLocation($campusID)
    {
        $table = \JTable::getInstance('campuses', 'thm_organizerTable');
        $table->load($campusID);

        return empty($table->location) ? '' : str_replace(' ', '', $table->location);
    }

    /**
     * Gets the qualified campus name
     *
     * @param int $campusID the campus' id
     *
     * @return string the name if the campus could be resolved, otherwise empty
     */
    public static function getName($campusID = null)
    {
        $languageTag = Languages::getShortTag();

        if (empty($campusID)) {
            return Languages::_('THM_ORGANIZER_CAMPUS_UNKNOWN');
        }

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("c1.name_$languageTag as name, c2.name_$languageTag as parentName")
            ->from('#__thm_organizer_campuses as c1')
            ->leftJoin('#__thm_organizer_campuses as c2 on c1.parentID = c2.id')
            ->where("c1.id = '$campusID'");
        $dbo->setQuery($query);
        $names = OrganizerHelper::executeQuery('loadAssoc', []);

        if (empty($names)) {
            return '';
        }

        return empty($names['parentName']) ? $names['name'] : "{$names['parentName']} / {$names['name']}";
    }

    /**
     * Retrieves an alphabetized list of campuses suitable for use in creating HTML options.
     *
     * @param bool $used whether or not only campuses associated with subjects or lessons should be returned.
     *
     * @return array campuses in the form of id => name
     */
    public static function getOptions($used = false)
    {
        $options = [];
        $dbo     = Factory::getDbo();

        if (!$used) {
            $query = $dbo->getQuery(true);
            $query->select('id')->from('#__thm_organizer_campuses as c');
            $dbo->setQuery($query);
            $campusIDs = OrganizerHelper::executeQuery('loadColumn', []);
        } else {
            // Parent campuses should always be displayed.
            $query = $dbo->getQuery(true);
            $query->select('DISTINCT parentID')
                ->from('#__thm_organizer_campuses as c')
                ->where('parentID IS NOT NULL');

            $dbo->setQuery($query);
            $parentCampusIDs = OrganizerHelper::executeQuery('loadColumn', []);

            $query = $dbo->getQuery(true);
            $query->select('c.id')
                ->from('#__thm_organizer_campuses as c')
                ->innerJoin('#__thm_organizer_subjects as s on s.campusID = c.id');

            $dbo->setQuery($query);
            $subjectCampusIDs = OrganizerHelper::executeQuery('loadColumn', []);

            $query = $dbo->getQuery(true);
            $query->select('c.id')
                ->from('#__thm_organizer_campuses as c')
                ->innerJoin('#__thm_organizer_lessons as l on l.campusID = c.id');
            $dbo->setQuery($query);
            $courseCampusIDs = OrganizerHelper::executeQuery('loadColumn', []);

            $campusIDs = array_unique(array_merge($parentCampusIDs, $subjectCampusIDs, $courseCampusIDs));
        }

        if (empty($campusIDs)) {
            return $options;
        }

        foreach ($campusIDs as $campusID) {
            $options[self::getName($campusID)] = $campusID;
        }

        // Sort alphabetically by full name
        ksort($options);

        // Normalize: id => name
        return array_flip($options);
    }

    /**
     * Returns a pin icon with a link for the location
     *
     * @param mixed $input int the id of the campus, string the location coordinates
     *
     * @return string the html output of the pin
     */
    public static function getPin($input)
    {
        $isID     = is_int($input);
        $location = $isID ? self::getLocation($input) : $input;

        if (!preg_match('/\d{1,2}\.\d{6},[ ]*\d{1,2}\.\d{6}/', $location)) {
            return '';
        }

        $pin = '<a target="_blank" href="https://www.google.de/maps/place/' . $location . '">';
        $pin .= '<span class="icon-location"></span></a>';

        return $pin;
    }
}
