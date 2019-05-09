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

namespace Organizer\Tables;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

/**
 * Class instantiates a Table Object associated with the programs table.
 */
class Programs extends Assets
{
    /**
     * Declares the associated table
     *
     * @param \JDatabaseDriver &$dbo A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_programs', 'id', $dbo);
    }

    /**
     * Set the table column names which are allowed to be null
     *
     * @return boolean  true
     */
    public function check()
    {
        $nullColumns = ['fieldID'];
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
        return "com_thm_organizer.program.$this->id";
    }

    /**
     * Sets the parent as the component root
     *
     * @param Table $table A Table object for the asset parent.
     * @param integer $id    Id to look up
     *
     * @return int  the asset id of the component root
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getAssetParentId(Table $table = null, $id = null)
    {
        $asset = Table::getInstance('Asset');
        $asset->loadByName("com_thm_organizer.department.$this->departmentID");

        return $asset->id;
    }
}
