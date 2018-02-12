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
jimport('joomla.database.table');

/**
 * Class instantiates a JTable Object associated with the pools table.
 */
class THM_OrganizerTablePools extends JTable
{
    /**
     * Declares the associated table
     *
     * @param JDatabaseDriver &$dbo A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_pools', 'id', $dbo);
    }

    /**
     * Overridden bind function
     *
     * @param array $array  named array
     * @param mixed $ignore An optional array or space separated list of properties to ignore while binding.
     *
     * @return mixed  Null if operation was satisfactory, otherwise returns an error string
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['rules']) && is_array($array['rules'])) {
            THM_OrganizerHelperComponent::cleanRules($array['rules']);
            $rules = new JAccessRules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Sets the department asset name
     *
     * @return string
     */
    protected function _getAssetName()
    {
        return "com_thm_organizer.pool.$this->id";
    }

    /**
     * Sets the parent as the component root
     *
     * @param  JTable  $table A JTable object for the asset parent.
     * @param  integer $id    Id to look up
     *
     * @return int  the asset id of the component root
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
