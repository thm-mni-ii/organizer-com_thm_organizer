<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerTableSubjects
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.tables.assets');
/**
 * Class representing the assets table.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin.tables
 */
class THM_OrganizerTableSubjects extends THM_CoreTableAssets
{
    /**
     * Constructor to call the parent constructor
     *
     * @param   JDatabaseDriver  &$dbo  A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_subjects', 'id', $dbo);
    }

    /**
     * Set the table column names which are allowed to be null
     *
     * @return  boolean  true
     */
    public function check()
    {
        $nullColumns = array('lsfID', 'hisID', 'frequencyID', 'fieldID', 'expertise', 'self_competence', 'method_competence', 'social_competence');
        foreach ($nullColumns as $nullColumn)
        {
            if (!strlen($this->$nullColumn))
            {
                $this->$nullColumn = NULL;
            }
        }
        return true;
    }

    /**
     * Sets the department asset name
     *
     * @return  void
     */
    protected function _getAssetName()
    {
        return "com_thm_organizer.subject.$this->id";
    }

    /**
     * Sets the parent as the component root
     *
     * @return  int  the asset id of the component root
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getAssetParentId(JTable $table = null, $id = null)
    {
        $asset = JTable::getInstance('Asset');
        $asset->loadByName("com_thm_organizer.department.$this->departmentID");
        return $asset->id;
    }
}
