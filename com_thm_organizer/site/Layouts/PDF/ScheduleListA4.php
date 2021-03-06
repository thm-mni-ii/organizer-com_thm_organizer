<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Layouts\PDF;

/**
 * Class generates a PDF file in A4 format.
 */
class ScheduleListA4 extends BaseLayout
{
	/**
	 * Performs initial construction of the TCPDF Object.
	 */
	public function __construct()
	{
		$orientation = self::LANDSCAPE; //$this->parameters['interval'] == 'day' ? self::PORTRAIT : self::LANDSCAPE;
		parent::__construct($orientation);
	}

	/**
	 * THM_OrganizerTemplateSchedule_Export_PDF_A4 constructor.
	 *
	 * @param   array  $parameters  the parameters for document
	 * @param   array &$lessons     the lessons to be displayed
	 * @param   array  $grid        the lesson grid for use in display
	 */
	public function x($parameters, &$lessons, $grid = null)
	{
		parent::__construct($parameters, $lessons, $grid);

		$this->parameters['cellLineHeight'] = 4.4;

		if ($this->parameters['interval'] == 'day')
		{
			$this->parameters['dataWidth'] = empty($this->grid) ? 200 : 188;
		}
		else
		{
			$this->parameters['dataWidth'] = empty($this->grid) ? 46.5 : 45;
		}

		$this->parameters['padding']   = 1;
		$this->parameters['timeWidth'] = 11;

		$this->fill();
	}

	/**
	 * Creates the basic pdf object
	 *
	 * @return THM_OrganizerTCPDFScheduleA4
	 */
	protected function getDocument()
	{
		$this->SetTitle($this->parameters['pageTitle']);
		$this->margins(5, 25, 5, 5);
		$this->setCellPaddings('', 1, '', 1);
		$this->SetTextColor(0, 0, 0);
		$this->setHeaderTemplateAutoreset(true);
	}

	/**
	 * Gets the text to be displayed in the row cells
	 *
	 * @param   array  $columnHeaders  the column header information:
	 *                                 value => the date (Y-m-d), text => the text to display
	 * @param   array  $rowHeader      the row header information:
	 *                                 start- and endTime used for indexing, text => the text to display
	 *
	 * @return array
	 */
	protected function getRowCells($columnHeaders, $rowHeader = null)
	{
		$rowCells = [];

		foreach ($columnHeaders as $columnHeader)
		{
			$date         = $columnHeader['value'];
			$indexLessons = $this->lessons[$date];
			$allIndexes   = array_keys($indexLessons);
			$timeIndexes  = $this->filterIndexes($allIndexes, $rowHeader);
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
						$rowCells[$indexCount] = [];
					}

					$rowCells[$indexCount][$date] = $this->getInstanceText($instance, $timeIndex, $rowHeader);
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
			$counts = [];
			foreach ($instances as $instance)
			{
				$counts[] = $this->getNumLines($instance, $this->parameters['dataWidth']);
			}
			$lineCount = max($counts);

			$rowCells[$index]['lineCount'] = $lineCount;
			$totalLineCount                += $lineCount;
		}

		$rowCells['lineCount'] = $totalLineCount;

		return $rowCells;
	}

	/**
	 * Outputs the lessons organized according to a grid structure with times
	 *
	 * @param   array  &$columnHeaders  the object with the column headers
	 * @param   array   $dimensions     the dimensions
	 * @param   string  $startDate      the start date for the interval
	 * @param   string  $breakDate      the end date for the interval
	 *
	 * @return void
	 */
	protected function outputGrid(&$columnHeaders, $dimensions, $startDate, $breakDate)
	{
		$rowCells  = $this->getRowCells($columnHeaders);
		$originalY = $this->getY();

		if (empty($rowCells))
		{
			return;
		}
		else
		{
			$totalRowHeight     = $rowCells['lineCount'] * $this->parameters['cellLineHeight'];
			$totalPaddingHeight = 2 * $this->parameters['padding'];
		}

		// The row size would cause it to traverse the page break
		if (($originalY + $totalRowHeight + $totalPaddingHeight + $dimensions['bm']) > ($dimensions['hk']))
		{
			$this->Ln();
			$this->outputHeader($columnHeaders, $startDate, $breakDate, true);
		}

		$this->SetFont('helvetica', '', 8, '');

		$rowHeight = 0;
		foreach ($rowCells as $rowName => $row)
		{
			if ($rowName === 'lineCount')
			{
				continue;
			}

			$this->SetLineStyle(['width' => 0.1, 'dash' => 0, 'color' => [0, 0, 0]]);

			$originalY = $this->getY();
			$newY      = $originalY + $rowHeight + $this->parameters['padding'];
			$this->setY($newY);

			$cellHeight = $row['lineCount'] * $this->parameters['cellLineHeight'];

			for ($currentDate = $startDate; $currentDate != $breakDate;)
			{
				$dow        = date('w', strtotime($currentDate));
				$validIndex = (
					!empty($columnHeaders[$currentDate])
					and $dow >= (int) $this->parameters['startDay']
					and $dow <= (int) $this->parameters['endDay']
				);
				if ($validIndex)
				{
					// Small horizontal spacer
					$this->renderMultiCell(1, $cellHeight, '');

					if (empty($row[$columnHeaders[$currentDate]['value']]))
					{
						$dataText = '';
						$border   = 0;
					}
					else
					{
						$dataText = $row[$columnHeaders[$currentDate]['value']];
						$border   = 'LRBT';
					}

					// Lesson instance cell
					$this->renderMultiCell(
						$this->parameters['dataWidth'],
						$cellHeight,
						$dataText,
						self::CENTER,
						$border,
						false,
						self::CENTER,
						$cellHeight
					);
				}

				$currentDate = date('Y-m-d', strtotime("+1 day", strtotime($currentDate)));
			}

			$this->Ln();
		}

		$this->outputRowEnd();
	}

	/**
	 * Outputs the column headers to the document
	 *
	 * @param   array   $columnHeaders   The date information to be output to the document.
	 * @param   string  $startDate       the first column date/index to use
	 * @param   string  $breakDate       the last column date/index to iterate
	 * @param   bool    $outputTimeGrid  whether or not the time column should be written
	 *
	 * @return void  outputs to the document
	 */
	protected function outputHeader($columnHeaders, $startDate, $breakDate, $outputTimeGrid)
	{
		$this->AddPage();

		$this->SetFont('helvetica', '', 10, '');
		$this->SetLineStyle(['width' => 0.5, 'dash' => 0, 'color' => [0, 0, 0]]);

		if ($outputTimeGrid)
		{
			$this->renderMultiCell(
				$this->parameters['timeWidth'],
				0,
				Languages::_('ORGANIZER_TIME'),
				self::CENTER,
				self::HORIZONTAL
			);
		}

		for ($currentDate = $startDate; $currentDate != $breakDate;)
		{
			$dow        = date('w', strtotime($currentDate));
			$validIndex = (!empty($columnHeaders[$currentDate])
				and $dow >= (int) $this->parameters['startDay']
				and $dow <= (int) $this->parameters['endDay']);

			if ($validIndex)
			{
				$this->renderMultiCell(
					$this->parameters['dataWidth'] + 1,
					0,
					$columnHeaders[$currentDate]['text'],
					self::CENTER,
					self::HORIZONTAL
				);
			}

			$currentDate = date('Y-m-d', strtotime("+1 day", strtotime($currentDate)));
		}

		$this->Ln();
		$this->SetFont('helvetica', '', 8, '');
	}

	/**
	 * Creates a line to cap the end of a row
	 *
	 * @return void outputs to the document
	 */
	private function outputRowEnd()
	{
		$originalY = $this->getY();
		$this->setY($originalY - 4);

		$this->SetLineStyle(['width' => 0.1, 'dash' => 0, 'color' => [0, 0, 0]]);
		$this->renderCell(0, 0, '', self::LEFT, self::BOTTOM);
		$this->Ln();
	}

	/**
	 * Writes the time cell to the document
	 *
	 * @param   int     $height  the estimated cell height
	 * @param   string  $text    the time text
	 *
	 * @return void
	 */
	private function outputTimeCell($height, $text)
	{
		$this->renderMultiCell(
			$this->parameters['timeWidth'],
			$height,
			$text,
			self::CENTER,
			self::NONE,
			false,
			self::CENTER,
			$height
		);
	}

	/**
	 * Outputs the lessons organized according to a grid structure with times
	 *
	 * @param   array  &$rowHeaders     the row grid times
	 * @param   array  &$columnHeaders  the dates
	 * @param   array  &$dimensions     the dimensions of the cells
	 * @param   string  $startDate      the date to start from
	 * @param   string  $breakDate      the date to stop iteration
	 *
	 * @return void
	 */
	protected function outputTimeGrid(&$rowHeaders, &$columnHeaders, $dimensions, $startDate, $breakDate)
	{
		foreach ($rowHeaders as $rowHeader)
		{
			$headerLineCount = $this->getNumLines($rowHeader['text'], $this->parameters['dataWidth']);
			$rowCells        = $this->getRowCells($columnHeaders, $rowHeader);
			$originalY       = $this->getY();

			if (empty($rowCells))
			{
				$totalRowHeight = $headerLineCount * $this->parameters['cellLineHeight'];

				// This should actually be less one because of the line count index, but the footer adds it back.
				$totalPaddingHeight = count($rowCells) * $this->parameters['padding'];
			}
			else
			{
				$minLineCount       = max($headerLineCount, $rowCells['lineCount']);
				$totalRowHeight     = $minLineCount * $this->parameters['cellLineHeight'];
				$totalPaddingHeight = 2 * $this->parameters['padding'];
			}

			// The row size would cause it to traverse the page break
			if (($originalY + $totalRowHeight + $totalPaddingHeight + $dimensions['bm']) > ($dimensions['hk']))
			{
				$this->Ln();
				$this->outputHeader($columnHeaders, $startDate, $breakDate, true);

				// New page, new Y
				$originalY = $this->getY();
			}

			$this->SetFont('helvetica', '', 8, '');

			if (empty($rowCells))
			{
				$newY = $originalY + $this->parameters['padding'];
				$this->setY($newY);

				$height = $headerLineCount * $this->parameters['cellLineHeight'];
				$text   = $rowHeader['text'];
				$this->outputTimeCell($height, $text);

				// One long cell for the border
				$this->renderMultiCell(0, $height, '');

				$this->Ln();

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

				$this->SetLineStyle(['width' => 0.1, 'dash' => 0, 'color' => [0, 0, 0]]);

				$originalY = $this->getY();
				$newY      = $originalY + $rowHeight + $this->parameters['padding'];
				$this->setY($newY);

				$lineCount  = $outputTime ? max($headerLineCount, $row['lineCount']) : $row['lineCount'];
				$cellHeight = $lineCount * $this->parameters['cellLineHeight'];

				$timeText = $outputTime ? $rowHeader['text'] : '';
				$this->outputTimeCell($cellHeight, $timeText);
				$outputTime = false;

				for ($currentDate = $startDate; $currentDate != $breakDate;)
				{
					$dow        = date('w', strtotime($currentDate));
					$validIndex = (!empty($columnHeaders[$currentDate])
						and $dow >= (int) $this->parameters['startDay']
						and $dow <= (int) $this->parameters['endDay']);
					if ($validIndex)
					{
						// Small horizontal spacer
						$this->renderMultiCell(1, $cellHeight, '');

						if (empty($row[$columnHeaders[$currentDate]['value']]))
						{
							$dataText = '';
							$border   = 0;
						}
						else
						{
							$dataText = $row[$columnHeaders[$currentDate]['value']];
							$border   = 'LRBT';
						}

						// Lesson instance cell
						$this->renderMultiCell(
							$this->parameters['dataWidth'],
							$cellHeight,
							$dataText,
							self::CENTER,
							$border,
							false,
							self::CENTER,
							$cellHeight
						);
					}

					$currentDate = date('Y-m-d', strtotime("+1 day", strtotime($currentDate)));
				}

				$this->Ln();
			}

			$this->outputRowEnd();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function fill($data)
	{
		// TODO: Implement fill() method.
	}
}


//Page header
/*public function Header()
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
		$this->SetFont($headerFont[0], self::BOLD, $headerFont[2] + 1);
		$this->SetX($header_x);
		$this->Cell($cw, $cell_height, $headerData['title'], 0, 1, '', 0, '', 0);

		// Plan format and date
		$this->SetFont($headerFont[0], $headerFont[1], $headerFont[2]);
		$this->SetX($header_x);
		$this->renderMultiCell($cw, $cell_height, $headerData['string']);

		$this->endTemplate();
	}

	// print header template
	$this->printTemplate($this->header_xobjid, 0, 0, 0, 0, '', '', false);

	// reset header xobject template at each page
	if ($this->header_xobj_autoreset)
	{
		$this->header_xobjid = false;
	}
}*/

// Page footer
/*public function Footer()
{
	// Position at 15 mm from bottom
	$this->SetY(-10);
	// Set font
	$this->SetFont('helvetica', 'I', 7);
	// Page number
	$pagination = 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages();
	$this->Cell(0, 10, $pagination, 0, false, self::CENTER, 0, '', 0, false, 'T', 'M');
}*/
