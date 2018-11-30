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

require_once 'assets.php';

/**
 * Class instantiates a JTable Object associated with the subjects table.
 */
class THM_OrganizerTableSubjects extends THM_OrganizerTableAssets
{
    /**
     * Declares the associated table
     *
     * @param JDatabaseDriver &$dbo A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_subjects', 'id', $dbo);
    }

    /**
     * Set the table column names which are allowed to be null
     *
     * @return boolean  true
     */
    public function check()
    {
        $nullColumns = [
            'campusID',
            'expertise',
            'fieldID',
            'frequencyID',
            'hisID',
            'instructionLanguage',
            'lsfID',
            'method_competence',
            'self_competence',
            'social_competence'
        ];

        foreach ($nullColumns as $nullColumn) {
            if (!strlen($this->$nullColumn)) {
                $this->$nullColumn = null;
            }
        }

        return true;
    }

    /**
     * Sets the department asset name
     *
     * @return string
     */
    protected function _getAssetName()
    {
        return "com_thm_organizer.subject.$this->id";
    }

    /**
     * Sets the parent as the component root
     *
     * @param JTable  $table A JTable object for the asset parent.
     * @param integer $id    Id to look up
     *
     * @return int  the asset id of the component root
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getAssetParentId(JTable $table = null, $id = null)
    {
        $asset = JTable::getInstance('Asset');
        $name  = empty($this->departmentID) ? 'com_thm_organizer' : "com_thm_organizer.department.$this->departmentID";
        $asset->loadByName($name);

        return $asset->id;
    }
}
