<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

/**
 * Class providing functions useful to multiple component files
 */
class THM_OrganizerHelper
{
    /**
     * Calls the appropriate controller
     *
     * @param boolean $isAdmin whether the file is being called from the backend
     *
     * @return void
     * @throws Exception
     */
    public static function callController($isAdmin = true)
    {
        $basePath = $isAdmin ? JPATH_COMPONENT_ADMINISTRATOR : JPATH_COMPONENT_SITE;

        $handler = explode(".", JFactory::getApplication()->input->getCmd('task', ''));
        if (count($handler) == 2) {
            list($controller, $task) = $handler;
        } else {
            $task = $handler[0];
        }

        require_once $basePath . '/controller.php';

        $controllerObj = new THM_OrganizerController;
        $controllerObj->execute($task);
        $controllerObj->redirect();
    }

    /**
     * Attempts to delete entries from a standard table
     *
     * @param string $table the table name
     *
     * @return boolean  true on success, otherwise false
     * @throws Exception
     */
    public static function delete($table)
    {
        $cids         = JFactory::getApplication()->input->get('cid', [], '[]');
        $formattedIDs = "'" . implode("', '", $cids) . "'";

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->delete("#__thm_organizer_$table");
        $query->where("id IN ( $formattedIDs )");
        $dbo->setQuery($query);
        try {
            $dbo->execute();
        } catch (Exception $exception) {
            return false;
        }

        return true;
    }
}
