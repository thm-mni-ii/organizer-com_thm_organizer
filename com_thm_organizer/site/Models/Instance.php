<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Organizer\Helpers\Input;

defined('_JEXEC') or die;

/**
 * Class which manages stored instance data.
 */
class Instance extends BaseModel
{

    /**
     * Method to save instances
     *
     * @param array $data the data to be used to create the instance
     *
     * @return Boolean
     */
    public function save($data = [])
    {
        $data = empty($data) ? Input::getFormItems()->toArray() : $data;

        $table = $this->getTable();
        $addInstance = $table->save($data);

        $data['id'] = $table->id;

        $data['checkAssocID'] = isset($data['checkAssocID']) ? $data['checkAssocID'] : false;

        if ($addInstance) {

            $this->_db->transactionStart();
            $groupData = $this->saveInstanceData($data);

            if ($groupData) {

                $this->_db->transactionCommit();

                return $table->id;
            } else {

                $this->_db->transactionRollback();

                return false;
            }
        }
        return false;
    }

    /**
     * Method to check the new instance data and to save it
     *
     * @param array $newData the new instance data
     *
     * checkAssocID to check the existing assocID or create a new one
     * @return Boolean
     */
    private function saveInstanceData($newData)
    {

        foreach ($newData['resources'] as $person) {

            $instancePersonsData  = [];
            $instancePersonsTable = $this->getTable('InstancePersons');

            $instancePersonsData['personID']   = $person["personID"];
            $instancePersonsData['instanceID'] = $newData['id'];
            $instancePersonsData['roleID']     = 1;

            if (!empty($person['assocID']) && !$newData['checkAssocID']) {
                $instancePersonsData['id'] = $person['assocID'];
            } else {
                $instancePersonsData['delta'] = "new";
            }

            $instancePerson = $instancePersonsTable->save($instancePersonsData);

            if (!$instancePerson) {
                return false;
            }

            foreach ($person['groups'] as $group) {

                $instanceGroupsData  = [];
                $instanceGroupsTable = $this->getTable('InstanceGroups');

                if (empty($person["assocID"]) || $newData['checkAssocID']) {

                    $personID = $instancePersonsTable->id;
                } else {
                    $personID = $person["assocID"];
                }

                $instanceGroupsData['assocID'] = $personID;
                $instanceGroupsData['groupID'] = $group["groupID"];

                if (!empty($group['instanceGroupID']) && !$newData["checkAssocID"]) {

                    $instanceGroupsData['id'] = $group['instanceGroupID'];
                } else {
                    $instanceGroupsData['delta'] = "new";
                }

                $instanceGroup = $instanceGroupsTable->save($instanceGroupsData);

                if (!$instanceGroup) {
                    return false;
                }

            }

            foreach ($person['rooms'] as $room) {

                $instanceRoomsData  = [];
                $instanceRoomsTable = $this->getTable('InstanceRooms');

                if (empty($person["assocID"]) || $newData['checkAssocID']) {

                    $personID = $instancePersonsTable->id;
                } else {
                    $personID = $person["assocID"];
                }

                $instanceRoomsData['assocID'] = $personID;
                $instanceRoomsData['roomID']  = $room["roomID"];

                if (!empty($room['instanceRoomID']) && !$newData['checkAssocID']) {

                    $instanceRoomsData['id'] = $room['instanceRoomID'];
                } else {
                    $instanceRoomsData['delta'] = "new";
                }

                $instanceRoom = $instanceRoomsTable->save($instanceRoomsData);

                if (!$instanceRoom) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Method to save existing instances as copies
     *
     * @param array $data the data to be used to create the instance
     *
     * @return $saveInstance
     */
    public function save2copy($data = [])
    {
        $data = empty($data) ? Input::getFormItems()->toArray() : $data;

        if (isset($data['id'])) {
            unset($data['id']);
        }

        $data['checkAssocID'] = true;
        $saveInstance = $this->save($data);

        return $saveInstance;
    }
}
