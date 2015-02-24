<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerTableDepartments
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.database.table');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Class representing the majors table.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerTableDepartments extends JTable
{
    public $asset_id;

    /**
     * Constructor function for the class representing the majors table
     *
     * @param   JDatabaseDriver  &$dbo  A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_departments', 'id', $dbo);
    }

    /**
     * Overridden bind function
     *
     * @param   array  $array   named array
     * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  mixed  Null if operation was satisfactory, otherwise returns an error string
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['rules']) && is_array($array['rules']))
        {
            THM_OrganizerHelperComponent::cleanRules($array['rules']);
            $rules = new JAccessRules($array['rules']);
            $this->setRules($rules);
        }
        return parent::bind($array, $ignore);
    }

    /**
     * Method to return the title to use for the asset table.  In tracking the assets a title is kept for each asset so
     * that there is some context available in a unified access manager.
     *
     * @return  string  The string to use as the title in the asset table.
     */
    protected function _getAssetTitle()
    {
        return $this->short_name;
    }

    /**
     * Sets the department asset name
     *
     * @return  void
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_thm_organizer.department.'.(int) $this->$k;
    }

    /**
     * Sets the parent as the component root
     *
     * @return  int  the asset id of the component root
     */
    protected function _getAssetParentId(JTable $table = null, $id = null)
    {
        $asset = JTable::getInstance('Asset');
        $asset->loadByName('com_thm_organizer');
        return $asset->id;
    }
}
