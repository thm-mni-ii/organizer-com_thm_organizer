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

use JDatabaseQuery;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

/**
 * Provides general functions for department access checks, data retrieval and display.
 */
class Departments implements Selectable
{
    /**
     * Filters departments according to user access and relevant resource associations.
     *
     * @param JDatabaseQuery &$query  the query to be modified.
     * @param string          $access any access restriction which should be performed
     *
     * @return void modifies the query
     */
    private static function addAccessFilter(&$query, $access)
    {
        $view = Input::getView();
        if (empty($access) or empty($view)) {
            return;
        }

        $resource = OrganizerHelper::getResource($view);
        if ($access === 'schedule') {
            $query->innerJoin('#__thm_organizer_department_resources AS dpr ON dpr.departmentID = depts.id');
            if (in_array($resource, ['category', 'teacher'])) {
                $query->where("dpr.{$resource}ID IS NOT NULL");
            }
        } elseif ($access === 'document') {
            $table = OrganizerHelper::getPlural($resource);
            $query->innerJoin("#__thm_organizer_$table AS res ON res.departmentID = depts.id");
        }

        $allowedIDs = Access::getAccessibleDepartments($access);
        $query->where("depts.id IN ( '" . implode("', '", $allowedIDs) . "' )");
    }

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
        $dbo = Factory::getDbo();
        $tag = Languages::getTag();

        $query = $dbo->getQuery(true);
        $query->select("name_$tag as name")->from('#__thm_organizer_departments')->where("id = '$departmentID'");

        $dbo->setQuery($query);

        return (string)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Retrieves the selectable options for the resource.
     *
     * @param bool   $short  whether or not abbreviated names should be returned
     * @param string $access any access restriction which should be performed
     *
     * @return array the available options
     */
    public static function getOptions($short = true, $access = '')
    {
        $options = [];
        foreach (self::getResources($access) as $department) {
            $name = $short ? $department['shortName'] : $department['name'];

            $options[] = HTML::_('select.option', $department['id'], $name);
        }

        uasort($options, function ($optionOne, $optionTwo) {
            return $optionOne->text > $optionTwo->text;
        });

        // Any out of sequence indexes cause JSON to treat this as an object
        return array_values($options);
    }

    /**
     * Retrieves the resource items.
     *
     * @param string $access any access restriction which should be performed
     *
     * @return array the available resources
     */
    public static function getResources($access = '')
    {
        $dbo   = Factory::getDbo();
        $tag   = Languages::getTag();
        $query = $dbo->getQuery(true);

        $query->select("DISTINCT depts.*, depts.short_name_$tag AS shortName, depts.name_$tag AS name")
            ->from('#__thm_organizer_departments AS depts');

        self::addAccessFilter($query, $access);

        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }

    /**
     * Checks whether the plan resource is already associated with a department, creating an entry if none already
     * exists.
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

        $formData             = Input::getForm();
        $data['departmentID'] = $formData['departmentID'];

        try {
            $deptResourceTable->save($data);
        } catch (Exception $exc) {
            OrganizerHelper::message($exc->getMessage(), 'error');
        }

        return;
    }
}
