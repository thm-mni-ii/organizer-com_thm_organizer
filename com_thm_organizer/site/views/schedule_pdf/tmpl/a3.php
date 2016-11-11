<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerTemplateSchedulePDFA3
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
class THM_OrganizerTemplateSchedulePDFA3
{
	private $document;

	private $lessons;

	private $parameters;

	private $resources;

	/**
	 * Aggregates the disparate requested resources to a single array for ease of later iteration.
	 *
	 * @return void
	 */
	private function aggregateRequestedResources()
	{
		$resources = array();

		if (!empty($this->parameters['poolIDs']))
		{
			foreach ($this->parameters['poolIDs'] as $poolID)
			{
				$resources[] = array('id' => $poolID, 'index' => 'pools');
			}
		}

		if (!empty($this->parameters['teacherIDs']))
		{
			foreach ($this->parameters['teacherIDs'] as $teacherID)
			{
				$resources[] = array('id' => $teacherID, 'index' => 'teachers');
			}
		}

		if (!empty($this->parameters['roomIDs']))
		{
			foreach ($this->parameters['roomIDs'] as $roomID)
			{
				$resources[] = array('id' => $roomID, 'index' => 'rooms');
			}
		}

		$this->resources = $resources;
	}

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

		$resourceCount = $this->countRequestedResources();

		if ($resourceCount > 1)
		{
			$this->aggregateRequestedResources();
			$this->parameters['dataWidth'] = 62;
			$this->parameters['cellLineHeight'] = 3.6;
		}
		else
		{
			$this->parameters['dataWidth'] = 66;
			$this->parameters['cellLineHeight'] = 3.8;
		}

		$this->parameters['resourceCount']  = $resourceCount;
		$this->parameters['padding']        = 2;
		$this->parameters['timeWidth']      = 11;
		$this->parameters['resourceWidth']  = 24;

		$this->document   = $this->getDocument();
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
	 * Retrieves an array of normal border styles
	 *
	 * @return array an array of border styles
	 */
	private function getCellBorder()
	{
		return array(
			'R' => array('width' => '.1', 'color' => array(223, 229, 230)),
			'B' => array('width' => '.1', 'color' => array(223, 229, 230))
		);
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

		$paperFormat = $this->parameters['paperFormat'];
		foreach ($dates as $date)
		{
			$columns[$columnIndex]          = array();
			$columns[$columnIndex]['value'] = $date;
			$text                           = $paperFormat == 'A4' ?
				THM_OrganizerHelperComponent::formatDateShort($date, true) : THM_OrganizerHelperComponent::formatDate($date, true);
			$columns[$columnIndex]['text']  = $text;
			$columnIndex++;
		}

		return $columns;
	}

	/**
	 * Creates the basic pdf object
	 *
	 * @return THM_Organizer_PDF_Schedule_Export
	 */
	private function getDocument()
	{
		$document = new THM_OrganizerTCPDFScheduleA3();
		$document->SetCreator('THM Organizer');
		$document->SetAuthor(JFactory::getUser()->name);

		$pageTitle = $this->parameters['pageTitle'];
		$document->SetTitle($pageTitle);
		$document->SetHeaderData('thm.svg', 40, $pageTitle, $this->parameters['headerString'], array(57, 74, 89));

		$document->SetMargins(5, 25, 5);
		$document->setHeaderMargin(5);

		// This is here to access the bottom margin.
		$document->setPageOrientation('L', false, '15');
		$document->setCellPaddings('', .5, '', .5);
		$document->SetTextColor(57, 74, 89);

		return $document;
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
			if (!in_array($subject['shortName'], $subjectNames))
			{
				$subjectNames[] = $subject['shortName'];
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

		$subjectName = implode(' / ', $subjectNames);
		$subjectName .= " - $method";

		if (!empty($subjectNos))
		{
			$subjectName .= ' (' . implode(' / ', $subjectNos) . ')';
		}

		$text = "$subjectName\n";

		$output = array();

		if (!empty($pools))
		{
			$output[] = implode(' / ', $pools);
		}

		if (!empty($teachers))
		{
			$output[] = implode(' / ', $teachers);
		}

		if (!empty($rooms))
		{
			$output[] = implode(' / ', $rooms);
		}

		if (!empty($comment))
		{
			$output[] = "$comment";
		}

		$text .= implode(' ', $output);

		return $text;
	}

	/**
	 * Retrieves an array of border styles for the last element in a column grouping
	 *
	 * @return array an array of border styles
	 */
	private function getLastCellBorder()
	{
		return array(
			'R' => array('width' => '.1', 'color' => array(223, 229, 230)),
			'B' => array('width' => '.5', 'color' => array(74, 92, 102))
		);
	}

	/**
	 * Retrieves an array of border styles for the last row header in a column grouping
	 *
	 * @return array an array of border styles
	 */
	private function getLastRowHeadCellBorder()
	{
		return array(
			'R' => array('width' => '.5', 'color' => array(74, 92, 102)),
			'B' => array('width' => '.5', 'color' => array(74, 92, 102))
		);
	}

	/**
	 * Retrieves the resource name and the instance text.
	 *
	 * @param array $instance the instance information
	 * @param array $resource the resource information id & type
	 *
	 * @return void
	 */
	private function getResourceInstanceText($instance, $resource)
	{
		$return        = array();
		$resourceID    = $resource['id'];
		$resourceIndex = $resource['index'];

		$subjectNames = array();
		$subjectNos   = array();
		$pools        = array();
		$teachers     = array();
		$rooms        = array();
		$method       = empty($instance['method']) ? '' : $instance['method'];
		$comment      = empty($instance['comment']) ? '' : $instance['comment'];

		foreach ($instance['subjects'] as $subjectName => $subject)
		{
			// The iterated resource is not associated with this instance
			if (empty($subject[$resourceIndex][$resourceID]))
			{
				continue;
			}

			if (!in_array($subject['shortName'], $subjectNames))
			{
				$subjectNames[] = $subject['shortName'];
			}

			if (!empty($subject['subjectNo']) AND !in_array($subject['subjectNo'], $subjectNos))
			{
				$subjectNos[] = $subject['subjectNo'];
			}

			if ($resourceIndex == 'pools')
			{
				$return['resourceName'] = $subject[$resourceIndex][$resourceID]['gpuntisID'];
			}
			else
			{
				foreach ($subject['pools'] as $poolID => $pool)
				{
					$pools[$poolID] = $pool['gpuntisID'];
				}
			}

			if ($resourceIndex == 'teachers')
			{
				$return['resourceName'] = $subject[$resourceIndex][$resourceID];
			}
			else
			{
				foreach ($subject['teachers'] as $teacherID => $teacherName)
				{
					$teachers[$teacherID] = $teacherName;
				}
			}

			if ($resourceIndex == 'rooms')
			{
				$return['resourceName'] = $subject[$resourceIndex][$resourceID];
			}
			else
			{
				foreach ($subject['rooms'] as $roomID => $roomName)
				{
					$rooms[$roomID] = $roomName;
				}
			}
		}

		// None of the instances were relevant to the resource.
		if (empty($subjectNames))
		{
			return $return;
		}

		$subjectName = implode(' / ', $subjectNames);
		$subjectName .= " - $method";

		if (!empty($subjectNos))
		{
			$subjectName .= ' (' . implode(' / ', $subjectNos) . ')';
		}

		$text = "$subjectName\n";

		$output = array();

		if (!empty($pools))
		{
			$output[] = implode(' / ', $pools);
		}

		if (!empty($teachers))
		{
			$output[] = implode(' / ', $teachers);
		}

		if (!empty($rooms))
		{
			$output[] = implode(' / ', $rooms);
		}

		if (!empty($comment))
		{
			$output[] = "$comment";
		}

		$text .= implode(' ', $output);

		$return['instanceText'] = $text;

		return $return;

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
					if ($this->parameters['resourceCount'] < 2)
					{
						if (empty($rowCells[$indexCount]))
						{
							$rowCells[$indexCount] = array();
						}

						$rowCells[$indexCount][$date] = $this->getInstanceText($instance);
						$indexCount++;
					}
					else
					{
						foreach ($this->resources as $resource)
						{
							$rowNumber = 0;
							$results   = $this->getResourceInstanceText($instance, $resource);

							if (empty($results))
							{
								continue;
							}

							$resourceName = $results['resourceName'];

							if (empty($rowCells[$resourceName]))
							{
								$rowCells[$resourceName] = array();
							}
							else
							{
								$rowFound = false;
								foreach ($rowCells[$resourceName] as $tempNumber => $dates)
								{
									if (!key_exists($date, $dates))
									{
										$rowNumber = $tempNumber;
										$rowFound  = true;
										break;
									}
								}

								if (!$rowFound)
								{
									$rowNumber = count($rowCells[$resourceName]);
								}
							}

							if (empty($rowCells[$resourceName][$rowNumber]))
							{
								$rowCells[$resourceName][$rowNumber] = array();
							}

							$rowCells[$resourceName][$rowNumber][$date] = $results['instanceText'];
						}
					}
				}
			}
		}

		ksort($rowCells);

		// Skip line counting if empty
		if (empty($rowCells))
		{
			return $rowCells;
		}

		$totalLineCount = 0;

		if ($this->parameters['resourceCount'] < 2)
		{
			foreach ($rowCells as $index => $dates)
			{
				$lineCount                     = max(
					array_map(
						function ($instance)
						{
							return $this->document->getNumLines($instance, $this->parameters['dataWidth']);
						},
						$dates
					)
				);
				$rowCells[$index]['lineCount'] = $lineCount;
				$totalLineCount += $lineCount;
			}
		}
		else
		{
			$rowCount = 0;
			foreach ($rowCells as $resourceName => $rows)
			{
				foreach ($rows as $index => $dates)
				{
					$lineCount                                    = max(
						array_map(
							function ($instance)
							{
								return $this->document->getNumLines($instance, $this->parameters['dataWidth']);
							},
							$dates
						)
					);
					$rowCells[$resourceName][$index]['lineCount'] = $lineCount;
					$totalLineCount += $lineCount;
					$rowCount++;
				}
			}

			$rowCells['rowCount'] = $rowCount;
		}

		$rowCells['lineCount'] = $totalLineCount;

		return $rowCells;
	}

	/**
	 * Retrieves an array of normal border styles
	 *
	 * @return array an array of border styles
	 */
	private function getRowHeadCellBorder()
	{
		return array(
			'R' => array('width' => '.5', 'color' => array(74, 92, 102))
		);
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
			$rows[$rowIndex]['text']      = $formattedStartTime . "\n" . $formattedEndTime;
			$rowIndex++;
		}

		return $rows;
	}

	/**
	 * Counts the number of requested resources
	 *
	 * @return int the number of requested resources
	 */
	private function countRequestedResources()
	{
		$countPools    = empty($this->parameters['poolIDs']) ? 0 : count($this->parameters['poolIDs']);
		$countTeachers = empty($this->parameters['teacherIDs']) ? 0 : count($this->parameters['teacherIDs']);
		$countRooms    = empty($this->parameters['roomIDs']) ? 0 : count($this->parameters['roomIDs']);

		return $countPools + $countTeachers + $countRooms;
	}

	/**
	 * Outputs an empty row for the given time
	 *
	 * @param int    $lineCount   the number of lines calculated for the time text
	 * @param string $timeText    the text to be output for the row time
	 * @param int    $columnCount the number of date columns in the table
	 *
	 * @return void
	 */
	private function outputEmptyRow($lineCount, $timeText, $columnCount)
	{
		$originalY = $this->document->getY();
		$newY      = $originalY + $this->padding;
		$this->document->setY($newY);

		$height = $lineCount * $this->parameters['cellLineHeight'];
		$this->outputTimeCell($height, $timeText);

		if ($this->parameters['resourceCount'] > 1)
		{
			$this->document->MultiCell(
				$this->parameters['resourceWidth'],
				$height,
				'',
				'RB', 'C', 0, 0, '', '', true, 0, false, true
			);
		}

		for ($i = 0; $i < $columnCount; $i++)
		{
			$this->document->MultiCell(
				$this->parameters['dataWidth'],
				$height,
				'',
				'RB', 'C', 0, 0, '', '', true, 0, false, true
			);
		}

		$this->document->Ln();
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
		$this->document->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(74, 92, 102)));

		if ($showTime)
		{
			$this->document->MultiCell($this->parameters['timeWidth'], 0, JText::_('COM_THM_ORGANIZER_TIME'), 'TB', 'C', 0, 0);
		}

		if ($this->parameters['resourceCount'] > 1)
		{
			$this->document->MultiCell($this->parameters['resourceWidth'], 0, JText::_('COM_THM_ORGANIZER_RESOURCE'), 'TB', 'C', 0, 0);
		}

		foreach ($columnHeaders as $columnHeader)
		{
			$this->document->MultiCell($this->parameters['dataWidth'], 0, $columnHeader['text'], 'TB', 'C', 0, 0);
		}

		$this->document->Ln();

		// Reset font after header
		$this->document->SetFont('helvetica', '', 6, '', 'default', true);
	}

	/**
	 * Outputs the rows associated with a particular time index. (Not further compartmentalized according to resources.)
	 *
	 * @param array $resourceCells the data for the rows associated with the time index
	 * @param int   $minLineCount  the minimum amount of lines a row may have => number used in time column of row header.
	 * @param array $rowHeader     the row header / index information being iterated
	 * @param array $columnHeaders the column headers / indexes
	 *
	 * @return void
	 */
	private function outputResourceRows(&$resourceCells, $minLineCount, $rowHeader, &$columnHeaders)
	{
		// Less one because of the line count index
		$lastRowNumber = $resourceCells['rowCount'];
		$rowNumber     = 1;
		$outputTime    = true;
		foreach ($resourceCells as $resourceName => $resourceRows)
		{
			if ($resourceName === 'lineCount' or $resourceName === 'rowCount')
			{
				continue;
			}

			$lastResourceRowNumber = count($resourceRows);
			$resourceRowNumber     = 1;
			$outputResource        = true;

			foreach ($resourceRows as $row)
			{
				$lineCount  = max($minLineCount, $row['lineCount']);
				$cellHeight = $lineCount * $this->parameters['cellLineHeight'];

				$timeText   = $outputTime ? $rowHeader['text'] : '';
				$timeBorder = $rowNumber == $lastRowNumber ? $this->getLastRowHeadCellBorder() : $this->getRowHeadCellBorder();
				$this->outputTimeCell($cellHeight, $timeText, $timeBorder);
				$outputTime = false;
				$rowNumber++;

				$resourceText   = $outputResource ? $resourceName : '';
				if ($resourceRowNumber == $lastResourceRowNumber)
				{

					$resourceBorder = $this->getLastRowHeadCellBorder();
					$dataBorder = $this->getLastCellBorder();
				}
				else
				{
					$resourceBorder = $this->getRowHeadCellBorder();
					$dataBorder = $this->getCellBorder();
				}

				$this->document->MultiCell(
					$this->parameters['resourceWidth'],
					$cellHeight,
					$resourceText,
					$resourceBorder, 'C', 0, 0, '', '', true, 0, false, true,
					$cellHeight, 'M'
				);
				$outputResource = false;
				$resourceRowNumber++;

				foreach ($columnHeaders as $columnHeader)
				{
					$dataText = empty($row[$columnHeader['value']]) ? '' : $row[$columnHeader['value']];
					// Lesson instance cell
					$this->document->MultiCell(
						$this->parameters['dataWidth'],
						$cellHeight,
						$dataText,
						$dataBorder, 'C', 0, 0, '', '', true, 0, false, true,
						$cellHeight, 'M'
					);
				}

				$this->document->Ln();
			}
		}
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
			}
			else
			{
				$minLineCount       = max($headerLineCount, $rowCells['lineCount']);
				$totalRowHeight     = $minLineCount * $this->parameters['cellLineHeight'];
			}

			// The row size would cause it to traverse the page break
			if (($originalY + $totalRowHeight + $dimensions['bm']) > ($dimensions['hk']))
			{
				$this->document->Ln();
				$this->outputHeader($columnHeaders);
			}

			if (empty($rowCells))
			{
				$this->outputEmptyRow($headerLineCount, $rowHeader['text'], count($columnHeaders));
			}
			elseif ($this->parameters['resourceCount'] < 2)
			{
				$this->outputTimeRows($rowCells, $headerLineCount, $rowHeader, $columnHeaders);
			}
			else
			{
				$this->outputResourceRows($rowCells, $headerLineCount, $rowHeader, $columnHeaders);
			}
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
	private function outputTimeCell($height, $text, $border = 'R')
	{
		$this->document->MultiCell(
			$this->parameters['timeWidth'],
			$height,
			$text,
			$border, 'C', 0, 0, '', '', true, 0, false, true,
			$height, 'M'
		);
	}

	/**
	 * Outputs the rows associated with a particular time index. (Not further compartmentalized according to resources.)
	 *
	 * @param array $rowCells      the data for the rows associated with the time index
	 * @param int   $minLineCount  the minimum amount of lines a row may have => number used in time column of row header.
	 * @param array $rowHeader     the row header / index information being iterated
	 * @param array $columnHeaders the column headers / indexes
	 *
	 * @return void
	 */
	private function outputTimeRows(&$rowCells, $minLineCount, $rowHeader, &$columnHeaders)
	{
		// Less one because of the line count index
		$lastRowNumber = count($rowCells) - 1;
		$rowNumber     = 1;
		$outputTime    = true;
		foreach ($rowCells as $rowName => $row)
		{
			if ($rowName === 'lineCount')
			{
				continue;
			}

			$lineCount  = max($minLineCount, $row['lineCount']);
			$cellHeight = $lineCount * $this->parameters['cellLineHeight'];

			$timeText = $outputTime ? $rowHeader['text'] : '';
			$border   = $rowNumber == $lastRowNumber ? $this->getLastCellBorder() : $this->getRowHeadCellBorder();
			$this->outputTimeCell($cellHeight, $timeText, $border);
			$outputTime = false;
			$rowNumber++;

			foreach ($columnHeaders as $columnHeader)
			{
				$dataText = empty($row[$columnHeader['value']]) ? '' : $row[$columnHeader['value']];
				// Lesson instance cell
				$this->document->MultiCell(
					$this->parameters['dataWidth'],
					$cellHeight,
					$dataText,
					'RB', 'C', 0, 0, '', '', true, 0, false, true,
					$cellHeight, 'M'
				);
			}

			$this->document->Ln();
		}
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
			$this->document->cell('', '', JText::_('COM_THM_ORGANIZER_NO_LESSONS_AVAILABLE'));
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
class THM_OrganizerTCPDFScheduleA3 extends TCPDF
{

	/**
	 * Constructs using the implementation of the parent class restricted to the relevant parameters.
	 */
	public function __construct()
	{
		parent::__construct('L', 'mm', 'A3');
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