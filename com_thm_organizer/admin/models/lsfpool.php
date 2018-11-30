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

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/lsf.php';

/**
 * Class used to import lsf pool data.
 */
class THM_OrganizerModelLSFPool extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    /**
     * Creates a pool entry if none exists and calls
     *
     * @param object &$stub        a simplexml object containing rudimentary subject data
     * @param int    $departmentID the id of the department to which this data belongs
     *
     * @return mixed  int value of subject id on success, otherwise false
     */
    public function processStub(&$stub, $departmentID)
    {
        $valid = ((!empty($stub->pordid) or !empty($stub->modulid))
            and (!empty($stub->nrhis) or !empty($stub->modulnrhis)));
        if (!$valid) {
            return false;
        }

        $invalidTitle = THM_OrganizerHelperLSF::invalidTitle($stub);
        $blocked = !empty($stub->sperrmh) and strtolower((string)$stub->sperrmh) == 'x';

        $lsfID = empty($stub->pordid) ? (string)$stub->modulid : (string)$stub->pordid;
        $hisID = empty($stub->nrhis) ? (string)$stub->modulnrhis : (string)$stub->nrhis;

        $pool = JTable::getInstance('pools', 'thm_organizerTable');
        $pool->load(['lsfID' => $lsfID, 'hisID' => $hisID]);

        if (!empty($pool->id) and ($blocked or $invalidTitle)) {
            $poolModel = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('pool', 'THM_OrganizerModel');

            return $poolModel->deleteEntry($pool->id);
        }

        $pool->departmentID = $departmentID;
        $pool->lsfID        = $lsfID;
        $pool->hisID        = $hisID;
        $this->setAttribute($pool, 'externalID', (string)$stub->alphaid);
        $this->setAttribute($pool, 'abbreviation_de', (string)$stub->kuerzel);
        $this->setAttribute($pool, 'abbreviation_en', (string)$stub->kuerzelen, $pool->abbreviation_de);
        $this->setAttribute($pool, 'short_name_de', (string)$stub->kurzname);
        $this->setAttribute($pool, 'short_name_en', (string)$stub->kurznameen, $pool->short_name_de);
        $this->setAttribute($pool, 'name_de', (string)$stub->titelde);
        $this->setAttribute($pool, 'name_en', (string)$stub->titelen, $pool->name_de);

        $stubSaved = $pool->store();
        if (!$stubSaved) {
            return false;
        }

        return $this->processChildren($stub, $departmentID);
    }

    /**
     * Sets the value of a generic attribute if available
     *
     * @param object &$pool   the array where subject data is being stored
     * @param string $key     the key where the value should be put
     * @param string $value   the xpath value where the attribute value
     *                        should be
     * @param string $default the default value
     *
     * @return void
     */
    private function setAttribute(&$pool, $key, $value, $default = '')
    {
        if (empty($value)) {
            $pool->$key = empty($pool->$key) ?
                $default : $pool->$key;
        } else {
            $pool->$key = $value;
        }
    }

    /**
     * Processes the children of the stub element
     *
     * @param object &$stub        the pool element
     * @param int    $departmentID the id of the department to which this data belongs
     *
     * @return boolean true on success, otherwise false
     */
    private function processChildren(&$stub, $departmentID)
    {
        $lsfSubjectModel = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('LSFSubject', 'THM_OrganizerModel');

        foreach ($stub->modulliste->modul as $subStub) {
            $type    = THM_OrganizerHelperLSF::determineType($subStub);
            $success = true;

            if ($type == 'subject') {
                $success = $lsfSubjectModel->processStub($subStub, $departmentID);
            } elseif ($type == 'pool') {
                $success = $this->processStub($subStub, $departmentID);
            }

            // Malformed xml, invalid/incomplete data, database errors
            if (!$success) {
                return false;
            }
        }

        return true;
    }
}
