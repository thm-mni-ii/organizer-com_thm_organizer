<?php

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

include_once(JPATH_COMPONENT."/classes/DataAbstraction.php");
include_once(JPATH_COMPONENT."/classes/config.php");

class GiessenSchedulerModelAjaxhandler extends JModel
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

	public function executeTask($task)
	{
		$taskarr = explode(".", $task);
		try
		{
			require_once(JPATH_COMPONENT."/classes/".$taskarr[0].".php");
			$classname = $taskarr[0];
			$class = new $classname($this->JDA, $this->CFG);
			return $class->$taskarr[1]();
		}
		catch(Exception $e)
		{
			return array("success"=>false,"data"=>"Unknown task!");
		}
	}

//	public function authUser()
//	{
//		include_once(JPATH_COMPONENT."/classes/authUser.php");
//		$this->doc->setMimeEncoding('application/json');
//		$authUser = new authUser($this->JDA, $this->CFG);
//		return $authUser->doauthUser();
//	}
//
//	public function saveUserSchedule()
//	{
//		include_once(JPATH_COMPONENT."/classes/UserSchedule.php");
//		$this->doc->setMimeEncoding('application/json');
//		$userSchedule = new UserSchedule($this->JDA, $this->CFG);
//		return $userSchedule->save();
//	}
//
//	public function loadUserSchedule()
//	{
//		include_once(JPATH_COMPONENT."/classes/UserSchedule.php");
//		$this->doc->setMimeEncoding('application/json');
//		$userSchedule = new UserSchedule($this->JDA, $this->CFG);
//		return $userSchedule->load();
//	}
//
//	public function saveScheduleChanges()
//	{
//		include_once(JPATH_COMPONENT."/classes/ScheduleChanges.php");
//		$this->doc->setMimeEncoding('application/json');
//		$scheduleChanges = new ScheduleChanges($this->JDA, $this->CFG);
//		return $scheduleChanges->save();
//	}
//
//	public function loadEvents()
//	{
//		include_once(JPATH_COMPONENT."/classes/Events.php");
//		$this->doc->setMimeEncoding('application/json');
//		$events = new Events($this->JDA, $this->CFG);
//		return $events->load();
//	}
//
//	public function loadTreeView()
//	{
//		include_once(JPATH_COMPONENT."/classes/TreeView.php");
//		$this->doc->setMimeEncoding('application/json');
//		$treeView = new TreeView($this->JDA, $this->CFG);
//		return $treeView->load();
//	}
//
//	public function loadDescription()
//	{
//		include_once(JPATH_COMPONENT."/classes/Description.php");
//		$this->doc->setMimeEncoding('application/json');
//		$desc = new Description($this->JDA, $this->CFG);
//		return $desc->load();
//	}
//
//	public function exportSchedule()
//	{
//		include_once(JPATH_COMPONENT."/classes/StundenplanExport.php");
//		$this->doc->setMimeEncoding('application/json');
//		$exportSchedule = new StundenplanExport($this->JDA, $this->CFG);
//		return $exportSchedule->export();
//	}
//
//	public function downloadSchedule()
//	{
//		include_once(JPATH_COMPONENT."/classes/Down.php");
//		$downloadSchedule = new Down($this->JDA, $this->CFG);
//		return $downloadSchedule->get();
//	}
}