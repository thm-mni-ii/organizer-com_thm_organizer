<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelLSFPool
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

/**
 * Provides persistence handling for subject pools
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelLSFPool extends JModel
{

    /**
     * Creates a pool entry if none exists and calls
     *
     * @param   object  &$stub  a simplexml object containing rudimentary subject data
     *
     * @return  mixed  int value of subject id on success, otherwise false
     */
    public function processStub(&$stub)
    {
        $valid = (!empty($stub->pordid) OR !empty($stub->modulid))
         AND (!empty($stub->nrhis) OR !empty($stub->modulnrhis));
        if (!$valid)
        {
            return false;
        }

        $unwanted = !empty($stub->sperrmh) AND strtolower((string) $stub->sperrmh) == 'x';
        if ($unwanted)
        {
            return true;
        }

        $lsfID = empty($stub->pordid)? (string) $stub->modulid: (string) $stub->pordid;
        $hisID = empty($stub->nrhis)? (string) $stub->modulnrhis : (string) $stub->nrhis;

        $pool = JTable::getInstance('pools', 'thm_organizerTable');
        $pool->load(array('lsfID' => $lsfID, 'hisID' => $hisID));

        $pool->lsfID = $lsfID;
        $pool->hisID = $hisID;
        $this->setAttribute($pool, 'externalID', (string) $stub->alphaid);
        $this->setAttribute($pool, 'abbreviation_de', (string) $stub->kuerzel);
        $this->setAttribute($pool, 'abbreviation_en', (string) $stub->kuerzelen, $pool->abbreviation_de);
        $this->setAttribute($pool, 'short_name_de', (string) $stub->kurzname);
        $this->setAttribute($pool, 'short_name_en', (string) $stub->kurznameen, $pool->short_name_de);
        $this->setAttribute($pool, 'name_de', (string) $stub->titelde);
        $this->setAttribute($pool, 'name_en', (string) $stub->titelen, $pool->name_de);

        $stubSaved = $pool->store();
        if (!$stubSaved)
        {
            return false;
        }

        return $this->processChildren($stub);
    }

    /**
     * Sets the value of a generic attribute if available
     * 
     * @param   object  &$pool    the array where subject data is being stored
     * @param   string  $key      the key where the value should be put
     * @param   array   $value    the xpath value where the attribute value
     *                            should be
     * @param   string  $default  the default value
     * 
     * @return  void
     */
    private function setAttribute(&$pool, $key, $value, $default = '')
    {
        if (empty($value))
        {
            $pool->$key = empty($pool->$key)?
                $default : $pool->$key;
        }
        else
        {
            $pool->$key = $value;
        }
    }

    /**
     * Processes the children of the stub element
     * 
     * @param   object  &$stub  the pool element
     * 
     * @return  boolean true on success, otherwise false
     */
    private function processChildren(&$stub)
    {
        if (!empty($stub->modulliste->modul))
        {
            $lsfSubjectModel = JModel::getInstance('LSFSubject', 'THM_OrganizerModel');
            foreach ($stub->modulliste->modul as $subStub)
            {
                if (isset($subStub->modulliste->modul))
                {
                    $stubProcessed = $this->processStub($subStub);
                }
                else
                {
                    $stubProcessed = $lsfSubjectModel->processStub($subStub);
                }
                if (!$stubProcessed)
                {
                    return false;
                }
            }
        }
        return true;
    }
}
