<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        Download
 * @description Download file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
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
 */
class THMDownload
{
	/**
	 * Config
	 *
	 * @var    MySchedConfig
	 */
	private $_cfg = null;

	/**
	 * Username
	 *
	 * @var    String
	 */
	private $username = null;

	/**
	 * Title
	 *
	 * @var    String
	 */
	private $title = null;

	/**
	 * Type
	 *
	 * @var    String
	 */
	private $_what = null;

	/**
	 * Save to joomla folder
	 *
	 * @var    String
	 */
	private $_save = null;

	/**
	 * Document object
	 *
	 * @var    Object
	 */
	private $_doc = null;

	/**
	 * Constructor with the configuration object
	 *
	 * @param MySchedConfig $cfg A object which has configurations including
	 */
	public function __construct($cfg)
	{
		$input           = JFactory::getApplication()->input;
		$this->username = $input->getString("username");
		$this->title    = $input->getString("title");
		$this->_what     = $input->getString("what");
		$this->_save     = $input->get("save");
		$this->_cfg      = $cfg;
		$this->_doc      = JFactory::getDocument();
	}

	/**
	 * Method to create the schedule and send it to the user
	 *
	 * @return void
	 */
	public function schedule()
	{
		if (isset($this->username) && isset($this->title) && isset($this->_what) && isset($this->_save))
		{
			$path         = "/";
			$this->title = urldecode($this->title);

			if ($this->title == JText::_("COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE") && $this->username != "undefined")
			{
				$this->title = $this->username . " - " . $this->title;
			}

			$tmpFile = $this->_cfg->pdf_downloadFolder . $path . 'stundenplan.' . $this->_what;
			$file    = $this->_cfg->pdf_downloadFolder . $path . $this->title . '.' . $this->_what;

			if (empty($this->title) || $this->title == 'undefined')
			{
				if (!file_exists($tmpFile))
				{
					echo JText::_('COM_THM_ORGANIZER_MESSAGE_FILE_CREATION_FAIL');
				}
				else
				{
					$file         = $tmpFile;
					$this->title = 'stundenplan';
				}
			}

			if (!file_exists($file))
			{
				echo JText::_('COM_THM_ORGANIZER_MESSAGE_FILE_CREATION_FAIL');
			}
			else
			{
				if ($this->_save == "true")
				{
					@copy($file, $this->_cfg->pdf_downloadFolder . $path . $this->username . '.' . $this->_what);
				}
				elseif ($this->_what == "pdf")
				{
					$this->_doc->setMimeEncoding('application/pdf');
				}
				elseif ($this->_what == "ics")
				{
					$this->_doc->setMimeEncoding('text/calendar');
				}

				// Todo: Add some kind of default encoding for errors.

				header("Content-Length: " . filesize($file));
				header("Content-Disposition: attachment; filename=\"" . $this->title . "." . $this->_what . "\"");

				// Datei senden
				@readfile($file);

				// Datei loeschen
				@unlink($file);
			}
		}
	}
}
