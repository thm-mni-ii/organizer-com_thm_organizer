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
 * Provides general functions for department access checks, data retrieval and display.
 */
class THM_OrganizerHelperDepartments
{
    /**
     * Retrieves the ids of the departments associated with the given resources.
     *
     * @param string $resource    the name of the resource
     * @param array  $resourceIDs the ids of the resources selected
     *
     * @return array the department ids associated with the selected resources
     */
    public static function getDepartmentsByResource($resource, $resourceIDs = null)
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT departmentID')
            ->from('#__thm_organizer_department_resources');
        if (!empty($resourceIDs) and is_array($resourceIDs)) {
            $resourceIDs = "'" . implode("', '", Joomla\Utilities\ArrayHelper::toInteger($resourceIDs)) . "'";
            $query->where("{$resource}ID IN ($resourceIDs)");
        } else {
            $query->where("{$resource}ID IS NOT NULL");
        }
        $dbo->setQuery($query);
        $departmentIDs = THM_OrganizerHelperComponent::executeQuery('loadColumn', []);

        return empty($departmentIDs) ? [] : $departmentIDs;
    }

    /**
     * Retrieves the department name from the database
     *
     * @param int $departmentID the
     *
     * @return string  the name of the department in the active language
     */
    public static function getName($departmentID)
    {
        require_once 'language.php';
        $languageTag = THM_OrganizerHelperLanguage::getShortTag();
        $dbo         = JFactory::getDbo();

        $query = $dbo->getQuery(true);
        $query->select("name_$languageTag as name")->from('#__thm_organizer_departments')
            ->where("id = '$departmentID'");

        $dbo->setQuery($query);

        return (string)THM_OrganizerHelperComponent::executeQuery('loadResult');
    }

    /**
     * Checks whether the plan resource is already associated with a department, creating an entry if none already exists.
     *
     * @param int    $resourceID the db id for the plan resource
     * @param string $column     the column in which the resource information is stored
     *
     * @return void
     */
    public static function setDepartmentResource($resourceID, $column)
    {
        $deptResourceTable = JTable::getInstance('department_resources', 'thm_organizerTable');

        /**
         * If associations already exist for the resource, further associations should be made explicitly using the
         * appropriate edit view.
         */
        $data = [$column => $resourceID];
        if ($deptResourceTable->load($data)) {
            return;
        }

        $formData             = THM_OrganizerHelperComponent::getInput()->get('jform', [], 'array');
        $data['departmentID'] = $formData['departmentID'];

        try {
            $deptResourceTable->save($data);
        } catch (Exception $exc) {
            THM_OrganizerHelperComponent::message($exc->getMessage(), 'error');
        }

        return;
    }

    /**
     * Getter method for teachers in database. Only retrieving the IDs here allows for formatting the names according to
     * the needs of the calling views.
     *
     * @param bool $short whether or not abbreviated names should be returned
     *
     * @return array
     */
    public static function getPlanDepartments($short = true)
    {
        require_once 'language.php';
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $tag   = THM_OrganizerHelperLanguage::getShortTag();

        $query->select("DISTINCT d.id, d.short_name_$tag AS shortName, d.name_$tag AS name");
        $query->from('#__thm_organizer_departments AS d');
        $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.departmentID = d.id');

        $dbo->setQuery($query);

        $results = THM_OrganizerHelperComponent::executeQuery('loadAssocList');
        if (empty($results)) {
            return [];
        }

        $departments = [];
        foreach ($results as $department) {
            $departments[$department['id']] = $short ? $department['shortName'] : $department['name'];
        }

        asort($departments);

        return $departments;
    }
}
