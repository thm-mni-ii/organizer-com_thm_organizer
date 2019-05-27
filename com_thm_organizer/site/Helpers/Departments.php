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

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

/**
 * Provides general functions for department access checks, data retrieval and display.
 */
class Departments
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
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT departmentID')
            ->from('#__thm_organizer_department_resources');
        if (!empty($resourceIDs) and is_array($resourceIDs)) {
            $resourceIDs = "'" . implode("', '", ArrayHelper::toInteger($resourceIDs)) . "'";
            $query->where("{$resource}ID IN ($resourceIDs)");
        } else {
            $query->where("{$resource}ID IS NOT NULL");
        }
        $dbo->setQuery($query);
        $departmentIDs = OrganizerHelper::executeQuery('loadColumn', []);

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
        $languageTag = Languages::getShortTag();
        $dbo         = Factory::getDbo();

        $query = $dbo->getQuery(true);
        $query->select("name_$languageTag as name")->from('#__thm_organizer_departments')
            ->where("id = '$departmentID'");

        $dbo->setQuery($query);

        return (string)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Getter method for departments in database. Only retrieving the IDs here allows for formatting the names according to
     * the needs of the calling views.
     *
     * @param bool $short whether or not abbreviated names should be returned
     *
     * @return array
     */
    public static function getOptions($short = true)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $tag   = Languages::getShortTag();

        $query->select("DISTINCT d.id, d.short_name_$tag AS shortName, d.name_$tag AS name");
        $query->from('#__thm_organizer_departments AS d');
        $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.departmentID = d.id');

        $dbo->setQuery($query);

        $results = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($results)) {
            return [];
        }

        $options = [];
        foreach ($results as $department) {
            $options[$department['id']] = $short ? $department['shortName'] : $department['name'];
        }

        asort($options);

        return $options;
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
        $deptResourceTable = OrganizerHelper::getTable('Department_Resources');

        /**
         * If associations already exist for the resource, further associations should be made explicitly using the
         * appropriate edit view.
         */
        $data = [$column => $resourceID];
        if ($deptResourceTable->load($data)) {
            return;
        }

        $formData             = OrganizerHelper::getFormInput();
        $data['departmentID'] = $formData['departmentID'];

        try {
            $deptResourceTable->save($data);
        } catch (Exception $exc) {
            OrganizerHelper::message($exc->getMessage(), 'error');
        }

        return;
    }
}
