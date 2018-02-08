<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerModelCampuses
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once 'language.php';

/**
 * Provides methods for room type objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
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
        $table = JTable::getInstance('campuses', 'thm_organizerTable');
        $table->load($campusID);

        if (!empty($table->location)) {
            $coordinates = str_replace(' ', '', $table->location);
            $location    = '<a target="_blank" href="https://www.google.de/maps/place/' . $coordinates . '">';
            $location    .= '<span class="icon-location"></span>';
            $location    .= '</a>';

            return $location;
        }

        return '';
    }

    /**
     * Gets the qualified campus name
     *
     * @param int $campusID the campus' id
     *
     * @return string the name if the campus could be resolved, otherwise empty
     * @throws Exception
     */
    public static function getName($campusID = null)
    {
        $languageTag = THM_OrganizerHelperLanguage::getShortTag();

        if (empty($campusID)) {
            return THM_OrganizerHelperLanguage::getLanguage()->_('COM_THM_ORGANIZER_CAMPUS_UNKNOWN');
        }

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("c1.name_$languageTag as name, c2.name_$languageTag as parentName")
            ->from('#__thm_organizer_campuses as c1')
            ->leftJoin('#__thm_organizer_campuses as c2 on c1.parentID = c2.id')
            ->where("c1.id = '$campusID'");
        $dbo->setQuery($query);

        try {
            $names = $dbo->loadAssoc();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return '';
        }

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
        $dbo     = JFactory::getDbo();

        if (!$used) {
            $query = $dbo->getQuery(true);
            $query->select('id')
                ->from('#__thm_organizer_campuses as c');

            $dbo->setQuery($query);

            try {
                $campusIDs = $dbo->loadColumn();
            } catch (Exception $exc) {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

                return $options;
            }
        } else {
            // Parent campuses should always be displayed.
            $query = $dbo->getQuery(true);
            $query->select('DISTINCT parentID')
                ->from('#__thm_organizer_campuses as c')
                ->where('parentID IS NOT NULL');

            $dbo->setQuery($query);

            try {
                $parentCampusIDs = $dbo->loadColumn();
            } catch (Exception $exc) {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

                return $options;
            }

            $query = $dbo->getQuery(true);
            $query->select('c.id')
                ->from('#__thm_organizer_campuses as c')
                ->innerJoin('#__thm_organizer_subjects as s on s.campusID = c.id');

            $dbo->setQuery($query);

            try {
                $subjectCampusIDs = $dbo->loadColumn();
            } catch (Exception $exc) {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

                return $options;
            }

            $query = $dbo->getQuery(true);
            $query->select('c.id')
                ->from('#__thm_organizer_campuses as c')
                ->innerJoin('#__thm_organizer_lessons as l on l.campusID = c.id');

            $dbo->setQuery($query);

            try {
                $courseCampusIDs = $dbo->loadColumn();
            } catch (Exception $exc) {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

                return $options;
            }

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
}
