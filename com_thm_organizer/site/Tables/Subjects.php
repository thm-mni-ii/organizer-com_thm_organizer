<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Tables;

use Joomla\CMS\Table\Table;

/**
 * Class instantiates a Table Object associated with the subjects table.
 */
class Subjects extends Assets
{
    /**
     * Declares the associated table
     *
     * @param \JDatabaseDriver &$dbo A database connector object
     */
    public function __construct(&$dbo = null)
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
     * @param Table   $table A Table object for the asset parent.
     * @param integer $id    Id to look up
     *
     * @return int  the asset id of the component root
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getAssetParentId(Table $table = null, $id = null)
    {
        $asset = Table::getInstance('Asset');
        $name  = empty($this->departmentID) ? 'com_thm_organizer' : "com_thm_organizer.department.$this->departmentID";
        $asset->loadByName($name);

        return $asset->id;
    }
}
