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

require_once 'departments.php';
require_once 'language.php';
require_once 'programs.php';


/**
 * Provides general functions for (subject) pool access checks, data retrieval and display.
 */
class THM_OrganizerHelperPools
{
    /**
     * Retrieves the table id if existent.
     *
     * @param string $gpuntisID the pool name in untis
     *
     * @return int id on success, otherwise 0
     */
    public static function getID($gpuntisID)
    {
        $table  = JTable::getInstance('plan_pools', 'thm_organizerTable');
        $data   = ['gpuntisID' => $gpuntisID];
        $exists = $table->load($data);

        return $exists ? $table->id : 0;
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
        $table  = JTable::getInstance('plan_pools', 'thm_organizerTable');
        $exists = $table->load($poolID);

        return $exists ? $table->full_name : '';
    }

    /**
     * Retrieves the pool's full name if existent.
     *
     * @param int   $poolID the table's pool id
     * @param sting $type   the pool's type (real|plan)
     *
     * @return string the full name, otherwise an empty string
     */
    public static function getName($poolID, $type = 'plan')
    {
        if ($type == 'plan') {
            $table  = JTable::getInstance('plan_pools', 'thm_organizerTable');
            $exists = $table->load($poolID);

            return $exists ? $table->name : '';
        }

        $table  = JTable::getInstance('pools', 'thm_organizerTable');
        $exists = $table->load($poolID);

        if (!$exists) {
            return '';
        }

        $languageTag = THM_OrganizerHelperLanguage::getShortTag();

        if (!empty($table->{'name_' . $languageTag})) {
            return $table->{'name_' . $languageTag};
        } elseif (!empty($table->{'short_name_' . $languageTag})) {
            return $table->{'short_name_' . $languageTag};
        }

        return !empty($table->{'abbreviation_' . $languageTag}) ? $table->{'abbreviation_' . $languageTag} : '';

    }

    /**
     * Getter method for pools in database e.g. for selecting a schedule
     *
     * @param bool $short whether or not abbreviated names should be returned
     *
     * @return string  all pools in JSON format
     *
     * @throws RuntimeException
     * @throws Exception
     */
    public static function getPlanPools($short = true)
    {
        $dbo = JFactory::getDbo();

        $query = $dbo->getQuery(true);
        $query->select('ppo.id, ppo.name, ppo.full_name');
        $query->from('#__thm_organizer_plan_pools AS ppo');

        $input               = JFactory::getApplication()->input;
        $selectedDepartments = $input->getString('departmentIDs');
        $selectedPrograms    = $input->getString('programIDs');

        if (!empty($selectedDepartments)) {
            $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.poolID = ppo.id');
            $departmentIDs = "'" . str_replace(',', "', '", $selectedDepartments) . "'";
            $query->where("dr.departmentID IN ($departmentIDs)");
        }

        if (!empty($selectedPrograms)) {
            $programIDs = "'" . str_replace(',', "', '", $selectedPrograms) . "'";
            $query->where("ppo.programID in ($programIDs)");
        }

        $dbo->setQuery($query);

        $default = [];
        try {
            $results = $dbo->loadAssocList();
        } catch (RuntimeException $exc) {
            JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

            return $default;
        }

        if (empty($results)) {
            return $default;
        }

        $pools = [];
        foreach ($results as $pool) {
            $name         = $short ? $pool['name'] : $pool['full_name'];
            $pools[$name] = $pool['id'];
        }

        ksort($pools);

        return $pools;
    }

    /**
     * Attempts to get the plan pool's id, creating it if non-existent.
     *
     * @param object $pool the pool object
     *
     * @return mixed int on success, otherwise null
     */
    public static function getPlanResourceID($gpuntisID, $pool)
    {
        $poolID = self::getID($gpuntisID);
        if (!empty($poolID)) {
            return $poolID;
        }

        $data              = [];
        $data['gpuntisID'] = $gpuntisID;

        $programID = THM_OrganizerHelperPrograms::getID($pool->degree);
        if (!empty($programID)) {
            $data['programID'] = $programID;
        }

        $data['name']      = $pool->restriction;
        $data['full_name'] = $pool->longname;
        $data['gridID']    = $pool->gridID;

        $table   = JTable::getInstance('plan_pools', 'thm_organizerTable');
        $success = $table->save($data);

        return $success ? $table->id : null;

    }
}
