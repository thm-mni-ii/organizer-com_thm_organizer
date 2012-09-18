<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		 ModuleList
 * @description ModuleList component site helper
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

/**
 * Class ModuleList for component com_thm_organizer
 *
 * Class provides methods to represent a list of module objects
 *
 * @category	Joomla.Component.Site
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class ModuleList
{
	/**
	 * List
	 *
	 * @var    Array
	 * @since  1.0
	 */
	public $list;

	/**
	 * Constructor to set up the class variables
	 */
	public function __construct()
	{
		$this->list = array();
	}

	/**
	 * Method to attach a module to the list
	 *
	 * @param   String  $modulObj  Module
	 *
	 * @return void
	 */
	public function modulesToList($modulObj)
	{
		array_push($this->list, $modulObj);
	}

	/**
	 * Method to return the list
	 *
	 * @return multitype
	 */
	public function getList()
	{
		return $this->list;
	}
}
