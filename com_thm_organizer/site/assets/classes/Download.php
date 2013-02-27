<?php
/**
 * @version     v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        Download
 * @description Download file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;

/**
 * Class Download for component com_thm_organizer
 *
 * Class provides methods for authenticate the user
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
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
		$this->_username = $JDA->getRequest("username");
		$this->_title    = $JDA->getRequest("title");
		$this->_what     = $JDA->getRequest("what");
		$this->_save     = $JDA->getRequest("save");
		$this->_cfg      = $CFG->getCFG();
		$this->_doc		= $JDA->getDoc();
	}

	/**
	 * Method to create the schedule and send it to the user
	 *
	 * @return void
	 */
	public function schedule()
	{
		if (isset( $this->_username ) && isset( $this->_title ) && isset( $this->_what ) &&isset( $this->_save ) )
		{
			$path  = "/";
			$this->_title = urldecode($this->_title);

			if ($this->_title == JText::_("COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE") && $this->_username != "undefined")
			{
				$this->_title = $this->_username . " - " . $this->_title;
			}

			$tmpFile = $this->_cfg[ 'pdf_downloadFolder' ] . $path . 'stundenplan.' . $this->_what;
			$file    = $this->_cfg[ 'pdf_downloadFolder' ] . $path . $this->_title . '.' . $this->_what;

			if (empty($this->_title) || $this->_title == 'undefined')
			{
				if (!file_exists($tmpFile))
				{
					echo JText::_('COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_NO_FILE');
				}
				else
				{
					$file  = $tmpFile;
					$this->_title = 'stundenplan';
				}
			}

			if (!file_exists($file))
			{
				echo JText::_('COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_NO_FILE');
			}
			else
			{

				if ($this->_save == "true")
				{
					@copy($file, $this->_cfg['pdf_downloadFolder'] . $path . $this->_username . '.' . $this->_what);
				}
				elseif ($this->_what == "pdf")
				{
					$this->_doc->setMimeEncoding('application/pdf');
				}
				elseif ($this->_what == "xls")
				{
					$this->_doc->setMimeEncoding('application/vnd.ms-excel');
				}
				else
				{
					// Ics
					$this->_doc->setMimeEncoding('text/calendar');
				}
				header("Content-Length: " . filesize($file));
				header("Content-Disposition: attachment; filename=\"" . $this->_title . "." . $this->_what . "\"");
	
				// Datei senden
				@readfile($file);
	
				// Datei loeschen
				@unlink($file);
			}
		}
	}
}
