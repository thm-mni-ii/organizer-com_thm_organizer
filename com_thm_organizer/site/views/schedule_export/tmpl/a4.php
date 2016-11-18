<?php

/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerTemplateSchedule_Export_PDF_A4
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
class THM_OrganizerTemplateSchedule_Export_PDF_A4
{
	private $document;

	private $lessons;

	private $parameters;

	/**
	 * THM_OrganizerTemplateSchedulePDFA4 constructor.
	 *
	 * @param array $parameters the parameters for document
	 * @param array $grid       the lesson grid for use in display
	 * @param array &$lessons   the lessons to be displayed
	 */
	public function __construct($parameters, $grid, &$lessons)
	{
		$this->parameters = $parameters;
		$this->grid       = $grid;
		$this->lessons    = $lessons;
		$this->document   = $this->getDocument();

		$this->parameters['cellLineHeight'] = 4.4;
		$this->parameters['dataWidth']      = 45;
		$this->parameters['padding']        = 1;
		$this->parameters['timeWidth']      = 11;

		$this->render();
	}

	/**
	 * Filters the lesson indexes for those applicable to the row being iterated.
	 *
	 * @param array $rowHeader     An array containing information about the row being iterated.
	 * @param array $lessonIndexes An array of indexes (startTime-endTime) for the given day.
	 *
	 * @return array The lesson indexes applicable for the row.
	 */
	private function filterIndexes($rowHeader, $lessonIndexes)
	{
		$rowStart = $rowHeader['startTime'];
		$rowEnd   = $rowHeader['endTime'];

		$filteredIndexes = array();
		foreach ($lessonIndexes as $index)
		{
			list($indexStart, $indexEnd) = explode('-', $index);

			$tooEarly = $indexEnd < $rowStart;
			$tooLate  = $rowEnd < $indexStart;

			if (!$tooEarly AND !$tooLate)
			{
				$filteredIndexes[] = $index;
			}
		}

		return $filteredIndexes;
	}

	/**
	 * Creates the basic pdf object
	 *
	 * @return THM_Organizer_PDF_Schedule_Export
	 */
	private function getDocument()
	{
		$document = new THM_OrganizerTCPDFScheduleA4();
		$document->SetCreator('THM Organizer');
		$document->SetAuthor(JFactory::getUser()->name);

		$pageTitle = $this->parameters['pageTitle'];
		$document->SetTitle($pageTitle);
		$document->SetHeaderData('thm.svg', 40, $pageTitle, $this->parameters['headerString'], array(57, 74, 89));

		$document->SetMargins(5, 25, 5);
		$document->SetAutoPageBreak(true, 5);
		$document->setHeaderMargin(5);
		$document->setCellPaddings('', 1, '', 1);
		$document->SetTextColor(57, 74, 89);

		return $document;
	}

	/**
	 * Gets the row header information. startTime and endTime are used for later indexing purposes. Text is the text to
	 * actually be displayed in the row header.
	 *
	 * @return array
	 */
	private function getColumnHeaders()
	{
		$dates = array_keys($this->lessons);

		$columns     = array();
		$columnIndex = 0;

		foreach ($dates as $date)
		{
			$columns[$columnIndex]          = array();
			$columns[$columnIndex]['value'] = $date;
			$columns[$columnIndex]['text']  = THM_OrganizerHelperComponent::formatDateShort($date, true);
			$columnIndex++;
		}

		return $columns;
	}

	/**
	 * Creates the text to be output for the lesson instance
	 *
	 * @param array $instance the instance information
	 *
	 * @return string the html for the instance text
	 */
	private function getInstanceText($instance)
	{
		$subjectNames = array();
		$subjectNos   = array();
		$pools        = array();
		$teachers     = array();
		$rooms        = array();
		$method       = empty($instance['method']) ? '' : $instance['method'];
		$comment      = empty($instance['comment']) ? '' : $instance['comment'];

		foreach ($instance['subjects'] as $subjectName => $subject)
		{
			if (!in_array($subjectName, $subjectNames))
			{
				$subjectNames[] = $subjectName;
			}

			if (!empty($subject['subjectNo']) AND !in_array($subject['subjectNo'], $subjectNos))
			{
				$subjectNos[] = $subject['subjectNo'];
			}

			// Only if no specific pool was requested individually
			if (empty($this->parameters['poolIDs']) OR count($this->parameters['poolIDs']) > 1)
			{
				foreach ($subject['pools'] as $poolID => $pool)
				{
					$pools[$poolID] = $pool['gpuntisID'];
				}
			}

			// Only if no specific teacher was requested individually
			if (empty($this->parameters['teacherIDs']) OR count($this->parameters['teacherIDs']) > 1)
			{
				foreach ($subject['teachers'] as $teacherID => $teacherName)
				{
					$teachers[$teacherID] = $teacherName;
				}
			}

			// Only if no specific room was requested individually
			if (empty($this->parameters['roomIDs']) OR count($this->parameters['roomIDs']) > 1)
			{
				foreach ($subject['rooms'] as $roomID => $roomName)
				{
					$rooms[$roomID] = $roomName;
				}
			}
		}

		$subjectName = implode('/', $subjectNames);
		$subjectName .= " - $method";

		if (!empty($subjectNos))
		{
			$subjectName .= ' (' . implode('/', $subjectNos) . ')';
		}

		$text = "$subjectName\n";

		$output = array();

		if (!empty($pools))
		{
			$output[] = implode('/', $pools);
		}

		if (!empty($teachers))
		{
			$output[] = implode('/', $teachers);
		}

		if (!empty($rooms))
		{
			$output[] = implode('/', $rooms);
		}

		if (!empty($comment))
		{
			$output[] = "$comment";
		}

		$text .= implode(' ', $output);

		return $text;
	}

	/**
	 * Gets the text to be displayed in the row cells
	 *
	 * @param array $rowHeader     the row header information: start- and endTime used for indexing, text => the text to display
	 * @param array $columnHeaders the column header information: value => the date (Y-m-d), text => the text to display
	 *
	 * @return array
	 */
	private function getRowCells($rowHeader, $columnHeaders)
	{
		$rowCells = array();

		foreach ($columnHeaders as $columnHeader)
		{
			$date         = $columnHeader['value'];
			$indexLessons = $this->lessons[$date];
			$timeIndexes  = $this->filterIndexes($rowHeader, array_keys($indexLessons));
			$indexCount   = 0;

			// No lesson instances on the given day
			if (empty($timeIndexes))
			{
				continue;
			}

			foreach ($timeIndexes as $timeIndex)
			{
				foreach ($this->lessons[$date][$timeIndex] as $instance)
				{
					if (empty($rowCells[$indexCount]))
					{
						$rowCells[$indexCount] = array();
					}

					$rowCells[$indexCount][$date] = $this->getInstanceText($instance);
					$indexCount++;
				}
			}
		}

		// Skip line counting if empty
		if (empty($rowCells))
		{
			return $rowCells;
		}

		$totalLineCount = 0;

		foreach ($rowCells as $index => $instances)
		{
			$counts = array();
			foreach ($instances as $instance)
			{
				$counts[] = $this->document->getNumLines($instance, $this->parameters['dataWidth']);
			}
			$lineCount = max($counts);

			$rowCells[$index]['lineCount'] = $lineCount;
			$totalLineCount += $lineCount;
		}

		$rowCells['lineCount'] = $totalLineCount;

		return $rowCells;
	}

	/**
	 * Gets the row header information. startTime and endTime are used for later indexing purposes. Text is the text to
	 * actually be displayed in the row header.
	 *
	 * @return mixed
	 */
	private function getRowHeaders()
	{
		$rows     = array();
		$rowIndex = 0;

		foreach ($this->grid as $times)
		{
			$rows[$rowIndex]              = array();
			$rows[$rowIndex]['startTime'] = $times['startTime'];
			$rows[$rowIndex]['endTime']   = $times['endTime'];
			$formattedStartTime           = THM_OrganizerHelperComponent::formatTime($times['startTime']);
			$formattedEndTime             = THM_OrganizerHelperComponent::formatTime($times['endTime']);
			$rows[$rowIndex]['text']      = $formattedStartTime . "\n-\n" . $formattedEndTime;
			$rowIndex++;
		}

		return $rows;
	}

	/**
	 * Outputs the column headers to the document
	 *
	 * @param array $columnHeaders The date information to be output to the document.
	 * @param bool  $showTime      Whether or not the time column should be displayed, default true.
	 *
	 * @return void  outputs to the document
	 */
	private function outputHeader($columnHeaders, $showTime = true)
	{
		// TODO: strategize for day, month, semester and no grid output

		$this->document->AddPage();

		$this->document->SetFont('helvetica', '', 10, '', 'default', true);
		$this->document->SetLineStyle(array('width' => 0.5, 'dash' => 0, 'color' => array(74, 92, 102)));

		if ($showTime)
		{
			$this->document->MultiCell($this->parameters['timeWidth'], 0, JText::_('COM_THM_ORGANIZER_TIME'), 'TB', 'C', 0, 0);
		}

		foreach ($columnHeaders as $columnHeader)
		{
			$this->document->MultiCell($this->parameters['dataWidth'] + 1, 0, $columnHeader['text'], 'TB', 'C', 0, 0);
		}

		$this->document->Ln();
		$this->document->SetFont('helvetica', '', 8, '', 'default', true);
	}

	/**
	 * Creates a line to cap the end of a row
	 *
	 * @return void outputs to the document
	 */
	private function outputRowEnd()
	{
		$originalY = $this->document->getY();
		$this->document->setY($originalY - 4);

		$this->document->SetLineStyle(array('width' => 0.1, 'dash' => 0, 'color' => array(119, 133, 140)));
		$this->document->cell(0, 0, '', 'B', 1, 0, 0);
	}

	/**
	 * Outputs the schedule table to the document
	 *
	 * @return void Outputs lesson instance data to the document.
	 */
	private function outputTable()
	{
		$rowHeaders    = $this->getRowHeaders();
		$columnHeaders = $this->getColumnHeaders();

		$dimensions = $this->document->getPageDimensions();

		$this->outputHeader($columnHeaders);

		foreach ($rowHeaders as $rowHeader)
		{
			$headerLineCount = $this->document->getNumLines($rowHeader['text'], $this->parameters['dataWidth']);
			$rowCells        = $this->getRowCells($rowHeader, $columnHeaders);
			$originalY       = $this->document->getY();

			if (empty($rowCells))
			{
				$totalRowHeight = $headerLineCount * $this->parameters['cellLineHeight'];

				// This should actually be less one because of the line count index, but the footer adds it back.
				$totalPaddingHeight = count($rowCells) * $this->parameters['padding'];
			}
			else
			{
				$minLineCount       = max($headerLineCount, $rowCells['lineCount']);
				$totalRowHeight    = $minLineCount * $this->parameters['cellLineHeight'];
				$totalPaddingHeight = 2 * $this->parameters['padding'];
			}

			// The row size would cause it to traverse the page break
			if (($originalY + $totalRowHeight + $totalPaddingHeight + $dimensions['bm']) > ($dimensions['hk']))
			{
				$this->document->Ln();
				$this->outputHeader($columnHeaders);

				// New page, new Y
				$originalY = $this->document->getY();
			}

			$this->document->SetFont('helvetica', '', 8, '', 'default', true);

			if (empty($rowCells))
			{
				$newY = $originalY + $this->parameters['padding'];
				$this->document->setY($newY);

				$height = $headerLineCount * $this->parameters['cellLineHeight'];
				$text   = $rowHeader['text'];
				$this->outputTimeCell($height, $text);

				// One long cell for the border
				$this->document->MultiCell(0, $height, '', 0, 0, 0, 0, '', '', true);

				$this->document->Ln();

				$this->outputRowEnd();

				continue;
			}

			$rowHeight  = 0;
			$outputTime = true;
			foreach ($rowCells as $rowName => $row)
			{
				if ($rowName === 'lineCount')
				{
					continue;
				}

				$this->document->SetLineStyle(array('width' => 0.1, 'dash' => 0, 'color' => array(57, 74, 89)));

				$originalY = $this->document->getY();
				$newY      = $originalY + $rowHeight + $this->parameters['padding'];
				$this->document->setY($newY);

				$lineCount  = $outputTime? max($headerLineCount, $row['lineCount']) : $row['lineCount'];
				$cellHeight = $lineCount * $this->parameters['cellLineHeight'];

				$timeText = $outputTime ? $rowHeader['text'] : '';
				$this->outputTimeCell($cellHeight, $timeText);
				$outputTime = false;

				foreach ($columnHeaders as $columnHeader)
				{
					// Small horizontal spacer
					$this->document->MultiCell(1, $cellHeight, '', 0, 0, 0, 0);

					if (empty($row[$columnHeader['value']]))
					{
						$dataText = '';
						$border   = 0;
					}
					else
					{
						$dataText = $row[$columnHeader['value']];
						$border   = 'LRBT';
					}

					// Lesson instance cell
					$this->document->MultiCell(
						$this->parameters['dataWidth'],
						$cellHeight,
						$dataText,
						$border, 'C', 0, 0, '', '', true, 0, false, true,
						$cellHeight, 'M'
					);
				}

				$this->document->Ln();
			}

			$this->outputRowEnd();
		}
	}

	/**
	 * Writes the time cell to the document
	 *
	 * @param int    $height the estimated cell height
	 * @param string $text   the time text
	 *
	 * @return void
	 */
	private function outputTimeCell($height, $text)
	{
		$this->document->MultiCell(
			$this->parameters['timeWidth'],
			$height,
			$text,
			0, 'C', 0, 0, '', '', true, 0, false, true,
			$height, 'M'
		);
	}

	/**
	 * Renders the document
	 *
	 * @return void
	 */
	private function render()
	{
		if (!empty($this->lessons))
		{
			$this->outputTable();
		}
		else
		{
			$this->document->AddPage();
			$this->document->cell('', '', JText::_('COM_THM_ORGANIZER_NO_LESSONS'));
		}
		$this->document->Output($this->parameters['docTitle'] . '.pdf', 'I');
		ob_flush();
	}
}

/**
 * Class extends TCPDF for ease of instanciation and customized header/footer
 *
 * @category    Joomla.Component.site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerTCPDFScheduleA4 extends TCPDF
{

	/**
	 * Constructs using the implementation of the parent class restricted to the relevant parameters.
	 */
	public function __construct()
	{
		parent::__construct('L', 'mm', 'A4');
	}

	//Page header
	public function Header()
	{
		if ($this->header_xobjid === false)
		{
			// start a new XObject Template
			$this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);

			$this->y = $this->header_margin;
			$this->x = $this->original_lMargin;

			$this->Image(K_PATH_IMAGES . 'thm_logo.png', 5, 5, 46, 15);

			$headerFont  = $this->getHeaderFont();
			$headerData  = $this->getHeaderData();
			$cell_height = $this->getCellHeight($headerFont[2] / $this->k);

			// set starting margin for text data cell
			$header_x = 70;
			$cw       = $this->w - $this->original_lMargin - $this->original_rMargin - 200;
			$this->SetTextColorArray($this->header_text_color);

			// Plan title
			$this->SetFont($headerFont[0], 'B', $headerFont[2] + 1);
			$this->SetX($header_x);
			$this->Cell($cw, $cell_height, $headerData['title'], 0, 1, '', 0, '', 0);

			// Plan format and date
			$this->SetFont($headerFont[0], $headerFont[1], $headerFont[2]);
			$this->SetX($header_x);
			$this->MultiCell($cw, $cell_height, $headerData['string'], 0, '', 0, 1, '', '', true, 0, false, true, 0, 'T', false);

			$this->endTemplate();
		}

		// print header template
		$this->printTemplate($this->header_xobjid, 0, 0, 0, 0, '', '', false);

		// reset header xobject template at each page
		if ($this->header_xobj_autoreset)
		{
			$this->header_xobjid = false;
		}
	}

	// Page footer
	public function Footer()
	{
		// Position at 15 mm from bottom
		$this->SetY(-10);
		// Set font
		$this->SetFont('helvetica', 'I', 7);
		// Page number
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}