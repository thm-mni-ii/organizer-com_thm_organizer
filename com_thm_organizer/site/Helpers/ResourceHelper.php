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


use Joomla\CMS\Table\Table;

class ResourceHelper
{
    /**
     * Returns the corresponding resource table.
     *
     * @return Table
     */
    protected static function getTable()
    {
        return OrganizerHelper::getTable(OrganizerHelper::getClass(get_called_class()));
    }

    /**
     * Attempts to retrieve the name of the resource.
     *
     * @param int    $resourceID
     * @param string $fieldName
     *
     * @return string
     */
    public static function getName($resourceID, $fieldName = '')
    {
        $table  = self::getTable();
        $exists = $table->load($resourceID);
        if (empty($exists)) {
            return '';
        }

        if ($fieldName) {
            return empty($table->$fieldName) ? '' : $table->$fieldName;
        }

        $tableFields = $table->getFields();
        if (array_key_exists('name', $tableFields)) {
            return $table->name;
        }

        $localizedName = 'name_' . Languages::getTag();
        if (array_key_exists($localizedName, $tableFields)) {
            return $table->$localizedName;
        }

        return '';
    }
}
