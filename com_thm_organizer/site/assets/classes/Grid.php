<?php
/**
 * @version	    v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		Grid
 * @description Grid file from com_thm_organizer
 * @author	    Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

/**
 * Class Grid for component com_thm_organizer
 *
 * Class provides methods for the schedule grid
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class Grid
{
	/**
	 * Joomla data abstraction
	 *
	 * @var    DataAbstraction
	 * @since  1.0
	 */
	private $_JDA = null;

	/**
	 * Semester id
	 *
	 * @var    Integer
	 * @since  1.0
	 */
	private $_semID = null;

	/**
	 * Constructor with the joomla data abstraction object and configuration object
	 *
	 * @param   DataAbstraction  $JDA  A object to abstract the joomla methods
	 * @param   MySchedConfig	 $CFG  A object which has configurations including
	 *
	 * @since  1.5
	 *
	 */
	public function __construct($JDA, $CFG)
	{
		$this->JDA = $JDA;
		$this->semID = $JDA->getSemID();
	}

	/**
	 * Method to load the grid data
	 *
	 * @return Array An array which includes the grid data
	 */
	public function load()
	{
		if (isset( $this->semID))
		{
			$query = "SELECT gpuntisID AS tpid, day, period, starttime, endtime
			FROM #__thm_organizer_periods
			ORDER BY CAST(SUBSTRING(tpid, 4) AS SIGNED INTEGER)";
			$ret   = $this->JDA->query($query);

			if ($ret !== false)
			{
				return array("success" => true, "data" => $ret);
			}
			return array("success" => false, "data" => JText::_('COM_THM_ORGANIZER_SCHEDULER_GRID_ERROR_LOADING'));
		}
		else
		{
			return array("success" => false, "data" => JText::_('COM_THM_ORGANIZER_SCHEDULER_GRID_ERROR_LOADING'));
		}
	}
}
