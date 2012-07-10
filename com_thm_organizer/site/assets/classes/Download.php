<?php
/**
 *@category    Joomla component
 *
 *@package     THM_Organizer
 *
 *@subpackage  com_thm_organizer.site
 *@name		   Download
 *@description Download file from com_thm_organizer
 *@author	   Wolf Rost, wolf.rost@mni.thm.de
 *
 *@copyright   2012 TH Mittelhessen
 *
 *@license     GNU GPL v.2
 *@link		   www.mni.thm.de
 *@version	   1.0
 */

defined('_JEXEC') or die;

/**
 * Class Download for component com_thm_organizer
 *
 * Class provides methods for authenticate the user
 *
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       1.5
 */
class Download
{
	/**
	 * Config
	 *
	 * @var    MySchedConfig
	 * @since  1.0
	 */
	private $_cfg = null;

	/**
	 * Username
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_username = null;

	/**
	 * Title
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_title = null;

	/**
	 * Type
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_what = null;

	/**
	 * Save to joomla folder
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_save = null;

	/**
	 * Document object
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_doc = null;

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
		$this->username = $JDA->getRequest("username");
		$this->title    = $JDA->getRequest("title");
		$this->what     = $JDA->getRequest("what");
		$this->save     = $JDA->getRequest("save");
		$this->cfg      = $CFG->getCFG();
		$this->doc		= $JDA->getDoc();
	}

	/**
	 * Method to create the schedule and send it to the user
	 *
	 * @return void
	 */
	public function schedule()
	{
		if (isset( $this->username ) && isset( $this->title ) && isset( $this->what ) &&isset( $this->save ) )
		{
			$path  = "/";
			$this->title = urldecode($this->title);

			if ($this->title == JText::_("COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE") && $this->username != "undefined")
			{
				$this->title = $this->username . " - " . $this->title;
			}

			$tmpFile = $this->cfg[ 'pdf_downloadFolder' ] . $path . 'stundenplan.' . $this->what;
			$file    = $this->cfg[ 'pdf_downloadFolder' ] . $path . $this->title . '.' . $this->what;

			if (empty($this->title) || $this->title == 'undefined')
			{
				if (!file_exists($tmpFile))
				{
					die( JText::_('COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_NO_FILE') );
				}
				else
				{
					$file  = $tmpFile;
					$this->title = 'stundenplan';
				}
			}

			if (!file_exists($file))
			{
				die( JText::_('COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_NO_FILE') );
			}

			if ($this->save == "true")
			{
				@copy($file, $this->cfg['pdf_downloadFolder'] . $path . $this->username . '.' . $this->what);
			}
			elseif ($this->what == "pdf")
			{
				$this->doc->setMimeEncoding('application/pdf');
			}
			elseif ($this->what == "xls")
			{
				$this->doc->setMimeEncoding('application/vnd.ms-excel');
			}
			else
			{
				// Ics
				$this->doc->setMimeEncoding('text/calendar');
			}
			header("Content-Length: " . filesize($file));
			header("Content-Disposition: attachment; filename=\"" . $this->title . "." . $this->what . "\"");

			// Datei senden
			@readfile($file);

			// Datei loeschen
			@unlink($file);
		}
	}
}
