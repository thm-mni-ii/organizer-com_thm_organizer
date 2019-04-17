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

/**
 * Class instantiates a \JTable Object associated with the schedules table.
 */
class THM_OrganizerTableSchedules extends \Joomla\CMS\Table\Table
{
    /**
     * Declares the associated table
     *
     * @param \JDatabaseDriver &$dbo A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_schedules', 'id', $dbo);
    }

    /**
     * Method to return the title to use for the asset table.  In tracking the assets a title is kept for each asset so
     * that there is some context available in a unified access manager.
     *
     * @return string  The string to use as the title in the asset table.
     */
    protected function _getAssetTitle()
    {
        $dbo       = \JFactory::getDbo();
        $deptQuery = $dbo->getQuery(true);
        $deptQuery->select('short_name_en')
            ->from('#__thm_organizer_departments')
            ->where("id = '{$this->departmentID}'");

        $dbo->setQuery($deptQuery);
        $deptName = (string)\OrganizerHelper::executeQuery('loadResult');

        $planPeriodQuery = $dbo->getQuery(true);
        $planPeriodQuery->select('name')
            ->from('#__thm_organizer_planning_periods')
            ->where("id = '{$this->planningPeriodID}'");

        $dbo->setQuery($planPeriodQuery);
        $planPeriodName = (string)\OrganizerHelper::executeQuery('loadResult');

        return "Schedule: $deptName - $planPeriodName";
    }

    /**
     * Sets the department asset name
     *
     * @return string
     */
    protected function _getAssetName()
    {
        return "com_thm_organizer.schedule.$this->id";
    }

    /**
     * Sets the parent as the component root.
     *
     * @param \JTable $table A \JTable object for the asset parent.
     * @param integer $id    Id to look up
     *
     * @return int  the asset id of the component root
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getAssetParentId(\JTable $table = null, $id = null)
    {
        $asset = \JTable::getInstance('Asset');
        $asset->loadByName("com_thm_organizer.department.$this->departmentID");

        return $asset->id;
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
            \OrganizerHelper::cleanRules($array['rules']);
            $rules = new \JAccessRules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }
}
