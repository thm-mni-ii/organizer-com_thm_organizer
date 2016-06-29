<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THMPDFBuilder
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once dirname(__FILE__) . "/AbstractBuilder.php";
require_once dirname(__FILE__) . "/mySched_pdf.php";

/**
 * Class provides methods to create a schedule in pdf format
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THMPDFBuilder extends THMAbstractBuilder
{
	/**
	 * Config
	 *
	 * @var    Object
	 */
	private $_cfg = null;

	/**
	 * The pdf object
	 *
	 * @var  object
	 */
	private $_pdf = null;

	/**
	 * Constructor with the configuration object
	 *
	 * @param   object $cfg     an object which has configurations including
	 * @param   array  $options an object which has options including
	 */
	public function __construct($cfg, $options)
	{
		$this->_cfg       = $cfg;
		$this->startdate  = $options["startdate"];
		$this->enddate    = $options["enddate"];
		$this->semesterID = $options["semesterID"];
	}

	/**
	 * Method to create a PDF schedule
	 *
	 * @param   Object $scheduleData the schedule object
	 * @param   string $username     the joomla username
	 * @param   string $title        the schedule title
	 *
	 * @return array An array with information about the status of the creation
	 */
	public function createSchedule($scheduleData, $username, $title)
	{
		$this->_pdf = new MySchedPdf($this->getTitle($username, $title), $this->startdate, $this->enddate);
		$this->setPDFSettings();

		$rows    = count((array) $scheduleData->grid);
		$columns = $scheduleData->daysPerWeek == "1" ? 6 : 7;

		$this->_pdf->Table_Init($columns, true, true);
		$this->_pdf->Set_Table_Type($this->getTableSettings());
		$this->_pdf->Set_Header_Type($this->getHeaderData($columns));
		$this->_pdf->Draw_Header();
		$this->_pdf->Set_Data_Type($this->getDataSettings($columns));

		// Creates an empty table as a template, otherwise the frame border widths are inconsistent
		$emptyRow = array_fill(0, $columns, array());
		$schedule = array_fill(0, $rows, $emptyRow);

		$this->fillTimeColumn($scheduleData, $schedule, $rows);
		$this->setScheduleData($scheduleData, $schedule);

		$endThird      = substr_replace($scheduleData->grid->{4}->endtime, ":", 2, 0);
		$startFourth   = substr_replace($scheduleData->grid->{5}->starttime, ":", 2, 0);
		$addLunchBreak = $endThird != $startFourth;
		if ($addLunchBreak)
		{
			array_splice($schedule, 3, 0, array($emptyRow));
			$schedule[3][0]["TEXT"] = '';
		}

		$separatorSettings = array('LN_SIZE' => 0.1, 'TEXT' => ' ', 'BRD_SIZE' => 0.7, 'BRD_TYPE' => 'T');
		$separator         = array_fill(0, $columns, $separatorSettings);
		$counter           = 0;

		ksort($schedule);
		foreach ($schedule as $row)
		{
			$counter++;
			$this->_pdf->Draw_Data($separator);

			if ($addLunchBreak AND $counter == 4)
			{
				$data               = array(0 => array());
				$data[0]['TEXT']    = JText::_("COM_THM_ORGANIZER_SCHEDULER_LUNCHTIME");
				$data[0]['COLSPAN'] = 7;
				$this->_pdf->Draw_Data($data);
				continue;
			}

			$this->renderRow($row);
		}

		$this->_pdf->Draw_Table_Border();

		if ($title == JText::_("COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE"))
		{
			$pdfLink = $this->_cfg->pdf_downloadFolder . $username . " - " . $title . '.pdf';
		}
		else
		{
			$pdfLink = $this->_cfg->pdf_downloadFolder . $title . '.pdf';
		}

		@$this->_pdf->Output($pdfLink, 'F');

		if (is_file($pdfLink))
		{
			return array("success" => true, "data" => JText::_("COM_THM_ORGANIZER_MESSAGE_FILE_CREATED"));
		}

		return array("success" => false, "data" => JText::_("COM_THM_ORGANIZER_MESSAGE_FILE_CREATION_FAIL"));
	}

	/**
	 * Gets the title
	 *
	 * @param   string $username the current logged in username
	 * @param   string $title    the schedule title
	 *
	 * @return  string  the document title
	 */
	private function getTitle($username = '', $title = '')
	{
		if (empty($title))
		{
			return 'stundenplan';
		}

		if ($title == JText::_("COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE") AND !empty($username))
		{
			return $username . " - " . $title;
		}

		return $title;
	}

	/**
	 * Sets document and tag properties for the PDF file
	 *
	 * @return  void  sets document and tag properties
	 */
	private function setPDFSettings()
	{
		// Page settings
		$this->_pdf->SetAutoPageBreak(true, 13);
		$this->_pdf->SetTopMargin(8);
		$this->_pdf->AddPage('L');

		// Format tag settings
		$this->_pdf->SetStyle("b", "arial", "b", 10, "0, 0, 0");
		$this->_pdf->SetStyle("i", "arial", "I", 10, "0, 0, 0");
		$this->_pdf->SetStyle("small", "arial", "", 8, "0, 0, 0");
	}

	/**
	 * Gets the table settings used for schedule pdf files
	 *
	 * @return  array  the table settings
	 */
	private function getTableSettings()
	{
		$brdColor      = array(150, 150, 150);
		$tableSettings = array(
			'TB_ALIGN'  => 'C',
			'BRD_COLOR' => $brdColor,
			'BRD_SIZE'  => 0.7
		);

		return $tableSettings;
	}

	/**
	 * Gets the data needed for the construction of the headers
	 *
	 * @param   int $columns the number of schedule columns
	 *
	 * @return  array  the header data
	 */
	private function getHeaderData($columns)
	{
		// These are falsely flagged in the metrics. The coding standard needs to be extended for them.
		$days = array(
			0 => JText::_("COM_THM_ORGANIZER_SCHEDULER_TIME"),
			1 => JText::_("MONDAY"),
			2 => JText::_("TUESDAY"),
			3 => JText::_("WEDNESDAY"),
			4 => JText::_("THURSDAY"),
			5 => JText::_("FRIDAY"),
			6 => JText::_("SATURDAY")
		);

		$headerData     = array();
		$headerSettings = $this->getHeaderSettings();
		$width          = $columns == 7 ? 45 : 50;
		for ($index = 0; $index < $columns; $index++)
		{
			$headerData[$index]          = $headerSettings;
			$headerData[$index]['WIDTH'] = $index === 0 ? 20 : $width;
			$headerData[$index]['TEXT']  = $days[$index];
		}

		return $headerData;
	}

	/**
	 * Gets the header settings used for schedule pdf files
	 *
	 * @return  array  the header settings
	 */
	private function getHeaderSettings()
	{
		$tColor         = array(80, 80, 80);
		$bgColor        = array(255, 255, 255);
		$brdColor       = array(150, 150, 150);
		$headerSettings = array(
			'WIDTH'             => 6,
			'T_COLOR'           => $tColor,
			'T_SIZE'            => 14,
			'T_FONT'            => 'Arial',
			'T_ALIGN'           => 'C',
			'V_ALIGN'           => 'T',
			'T_TYPE'            => 'B',
			'LN_SIZE'           => 7,
			'BG_COLOR'          => $bgColor,
			'BRD_COLOR'         => $brdColor,
			'BRD_SIZE'          => 0.1,
			'BRD_TYPE'          => '1',
			'BRD_TYPE_NEW_PAGE' => '',
			'TEXT'              => ''
		);

		return $headerSettings;
	}

	/**
	 * Gets the data settings used for schedule pdf files
	 *
	 * @param   int $columns the number of schedule columns
	 *
	 * @return  array  the data settings
	 */
	private function getDataSettings($columns)
	{
		$tColor          = array(0, 0, 0);
		$bgColor         = array(255, 255, 255);
		$brdColor        = array(150, 150, 150);
		$generalSettings = array(
			'T_COLOR'           => $tColor,
			'T_SIZE'            => 11,
			'T_FONT'            => 'Arial',
			'T_ALIGN'           => 'C',
			'V_ALIGN'           => 'M',
			'T_TYPE'            => '',
			'LN_SIZE'           => 4,
			'BG_COLOR'          => $bgColor,
			'BRD_COLOR'         => $brdColor,
			'BRD_SIZE'          => 0.1,
			'BRD_TYPE'          => '1',
			'BRD_TYPE_NEW_PAGE' => ''
		);

		$dataSettings = array();
		for ($i = 0; $i < $columns; $i++)
		{
			$dataSettings[$i] = $generalSettings;
		}

		// Special properties for the time column
		$dataSettings[0]['V_ALIGN']  = 'M';
		$dataSettings[0]['T_ALIGN']  = 'C';
		$dataSettings[0]['T_SIZE']   = 11;
		$dataSettings[0]['LN_SIZE']  = 5;
		$dataSettings[0]['BRD_TYPE'] = "R";
		$dataSettings[0]['BRD_SIZE'] = 0.3;

		return $dataSettings;
	}

	/**
	 * Fills the time label column with values
	 *
	 * @param   object &$scheduleData the object with the schedule data
	 * @param   array  &$schedule     the array holding the schedule data for the pdf document
	 * @param   int    $rows          the number of rows
	 *
	 * @return  void  sets data in the schedule
	 */
	private function fillTimeColumn(&$scheduleData, &$schedule, $rows)
	{
		for ($index = 0; $index < $rows; $index++)
		{
			$schedule[$index][0]["TEXT"] = substr_replace($scheduleData->grid->{$index + 1}->starttime, ":", 2, 0);
			$schedule[$index][0]["TEXT"] .= "\n-\n";
			$schedule[$index][0]["TEXT"] .= substr_replace($scheduleData->grid->{$index + 1}->endtime, ":", 2, 0);
		}
	}

	/**
	 * Sets the schedule data to be displayed in the pdf
	 *
	 * @param   object &$scheduleData the schedule data from the HTML display
	 * @param   array  &$schedule     the schedule data for the PDF display
	 *
	 * @return  void  sets data in the schedule to be output
	 *
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	private function setScheduleData(&$scheduleData, &$schedule)
	{
		// What creates this difference? Does it still have to be taken into consideration?
		if (isset($scheduleData->data[0]->htmlView))
		{
			// array mapping weekdays to their numerical values
			$assign = array(
				'monday'    => 1,
				'tuesday'   => 2,
				'wednesday' => 3,
				'thursday'  => 4,
				'friday'    => 5,
				'saturday'  => 6
			);

			$lessons = $scheduleData[0]->htmlView;
			foreach ($lessons as $block => $event)
			{
				foreach ($event as $day => $html)
				{
					foreach ($html as $value)
					{
						$output                            = $this->getPDFOutput($value);
						$schedule[$block][$assign[$day]][] = $output;
					}
				}
			}
		}
		else
		{
			$lessons = $scheduleData->data;
			foreach ($lessons as $key => $lesson)
			{
				if (isset($lesson->cell))
				{
					$output = $this->getPDFOutput($lesson->cell);

					// This assignment ($l->block - 1) seems odd, like it would write data to the time column?
					$schedule[$lesson->block - 1][$lesson->dow][] = $output;
				}
			}
		}
	}

	/**
	 * Processes the displayed HTML output for PDF output
	 *
	 * @param   string $htmlLesson the HTML lesson output
	 *
	 * @return  mixed|string
	 */
	private function getPDFOutput($htmlLesson)
	{
		$pdfLesson = str_replace('<br/>', "\n", $htmlLesson);
		$pdfLesson = str_replace('<br>', "\n", $pdfLesson);
		$pdfLesson = preg_replace('/[,|\s]*<span[^>]+class=[^>]*removed[^>]*>[^<]*<\/span>/', "", $pdfLesson, -1);
		$pdfLesson = strip_tags($pdfLesson, "<b><i><small>");
		$pdfLesson = preg_replace("/class=\"lecturename_dis\s*\"/", "", $pdfLesson);
		$pdfLesson = preg_replace("/class=\"lecturename\s*\"/", "", $pdfLesson);
		$pdfLesson = preg_replace("/class=\"\"\s*/", "", $pdfLesson);
		$pdfLesson = preg_replace("/class=\"roomshortname\s*\"/", "", $pdfLesson);
		$pdfLesson = preg_replace("/class=\"oldroom\s*\"/", "", $pdfLesson);

		return $pdfLesson;
	}

	/**
	 * Renders the row being iterated
	 *
	 * @param   array &$row the row being iterated
	 *
	 * @return  void
	 */
	private function renderRow(&$row)
	{
		$maxEntries = $this->getMaxEntries($row);

		for ($columnEntry = 0; $columnEntry < $maxEntries; $columnEntry++)
		{
			$data = array();
			foreach ($row as $columnIndex => $column)
			{
				// Time column text is not indexed, so an index is simulated to prevent redundant output
				if ($columnEntry == 0 && $columnIndex == 0)
				{
					$data[$columnIndex] = $column;
					continue;
				}

				if (!empty($column[$columnEntry]))
				{
					$data[$columnIndex]['TEXT']     = $column[$columnEntry];
					$data[$columnIndex]['BRD_TYPE'] = ($columnEntry == 0) ? "LR" : "TLR";
				}
				else
				{
					$data[$columnIndex]['TEXT']     = ' ';
					$data[$columnIndex]['BRD_TYPE'] = 'LR';
				}
			}
			$this->_pdf->Draw_Data($data);
		}
	}

	/**
	 * Find the maximum number of entries in a single column for the row
	 *
	 * @param   array &$row the row being iterated
	 *
	 * @return  int  the maximum number of entries for the row
	 */
	private function getMaxEntries(&$row)
	{
		// Find the maximum number of entries in a single column for the row
		$maxEntries = 1;
		foreach ($row as $column)
		{
			// Apparently only the time column has this property set
			if (isset($column['TEXT']))
			{
				continue;
			}

			if (count($column) > $maxEntries)
			{
				$maxEntries = count($column);
			}
		}

		return $maxEntries;
	}
}
