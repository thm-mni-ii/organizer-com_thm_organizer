<?php

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

include_once(JPATH_COMPONENT."/assets/classes/DataAbstraction.php");
include_once(JPATH_COMPONENT."/assets/classes/config.php");

class thm_organizerModelAjaxhandler extends JModel
{
	private $semID = null;
	private $JDA = null;
	private $CFG = null;
	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
		$this->JDA = new DataAbstraction();
		$this->CFG = new mySchedConfig($this->JDA);
	}

	public function executeTask($task, $options = array())
	{
		if(is_string($task) === true)
		{
			if(preg_match("/^[A-Za-z]+\.[A-Za-z]+$/", $task) === 0)
				return array("success"=>false,"data"=>"Unknown task!");
		}
		else
		{
			return array("success"=>false,"data"=>"Unknown task!");
		}

		$taskarr = explode(".", $task);
		try
		{
			require_once(JPATH_COMPONENT."/assets/classes/".$taskarr[0].".php");
			$classname = $taskarr[0];
			if(count($options) == 0)
				$class = new $classname($this->JDA, $this->CFG);
			else
				$class = new $classname($this->JDA, $this->CFG, $options);
			return $class->$taskarr[1]();
		}
		catch(Exception $e)
		{
			return array("success"=>false,"data"=>"Unknown task!");
		}
	}
}