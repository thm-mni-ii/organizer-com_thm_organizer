<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerTemplateRoom_Statistics_XLS
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
jimport('phpexcel.library.PHPExcel');

class THM_OrganizerTemplateRoom_Statistics_XLS
{

	public $endDate;

	public $endDoW;

	public $metaData;

	public $rooms;

	public $roomTypes;

	public $roomTypeMap;

	public $roomData;

	public $startDate;

	public $startDoW;

	/**
	 * THM_OrganizerTemplateRoom_Statistics_XLS constructor.
	 *
	 * @param object &$model the model containing the data for the room statistics
	 */
	public function __construct(&$model)
	{
		$this->endDate  = $model->endDate;
		$this->endDoW   = $model->endDoW;
		$this->metaData = $model->metaData;

		$this->rooms = array();
		foreach ($model->rooms as $roomName => $roomData)
		{
			$this->rooms[$roomData['id']] = $roomName;
		}

		$this->roomTypes   = $model->roomTypes;
		$this->roomTypeMap = $model->roomTypeMap;
		$this->roomData    = $model->roomData;
		$this->startDate   = $model->startDate;
		$this->startDoW    = $model->startDoW;
		unset ($model);

		$this->spreadSheet = new PHPExcel();

		$userName    = JFactory::getUser()->name;
		$startDate   = THM_OrganizerHelperComponent::formatDate($this->startDate);
		$endDate     = THM_OrganizerHelperComponent::formatDate($this->endDate);
		$description = JText::sprintf('COM_THM_ORGANIZER_ROOM_STATISTICS_EXPORT_DESCRIPTION', $startDate, $endDate);
		$this->spreadSheet->getProperties()->setCreator("THM Organizer")
			->setLastModifiedBy($userName)
			->setTitle(JText::_('COM_THM_ORGANIZER_ROOM_STATISTICS_EXPORT_TITLE'))
			->setDescription($description);

		$fontColor = new PHPExcel_Style_Color();
		$fontColor->setRGB('394A59');
		$this->spreadSheet->getDefaultStyle()->getFont()
			->setName('Arial')
			->setSize('12')
			->setColor($fontColor);

		$this->spreadSheet->getDefaultStyle()->getAlignment()
			->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

		$this->addSummarySheet();
		$this->addWeekSheet();
		$this->addGlossarySheet();

		// Reset the active sheet to the first item
		$this->spreadSheet->setActiveSheetIndex(0);
	}

	/**
	 * Creates a glossary sheet
	 *
	 * @return void
	 */
	private function addGlossarySheet()
	{
		$this->spreadSheet->createSheet();
		$this->spreadSheet->setActiveSheetIndex(2);
		$this->spreadSheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight('18');
		$this->spreadSheet->getActiveSheet()->setTitle(JTEXT::_('COM_THM_ORGANIZER_GLOSSARY'));
		$this->spreadSheet->getActiveSheet()->mergeCells("A1:F1");
		$title = JText::_('COM_THM_ORGANIZER_ROOM_STATISTICS_TITLE') . ' - ' . JTEXT::_('COM_THM_ORGANIZER_GLOSSARY');
		$this->spreadSheet->getActiveSheet()->setCellValue('A1', $title);
		$this->spreadSheet->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);

		$this->spreadSheet->getActiveSheet()->mergeCells("A3:B3");
		$this->spreadSheet->getActiveSheet()->setCellValue('A3', JText::_('COM_THM_ORGANIZER_COLUMN_EXPLANATIONS'));
		$this->spreadSheet->getActiveSheet()->getStyle('A3')->getFont()->setSize(14);
		$this->spreadSheet->getActiveSheet()->setCellValue("A4", JText::_('COM_THM_ORGANIZER_RAW_UTIL_TEXT'));
		$this->spreadSheet->getActiveSheet()->setCellValue("B4", JText::_('COM_THM_ORGANIZER_RAW_UTIL_TIP'));
		$this->spreadSheet->getActiveSheet()->setCellValue("A5", JText::_('COM_THM_ORGANIZER_RAW_PERCENT_TEXT'));
		$this->spreadSheet->getActiveSheet()->setCellValue("B5", JText::_('COM_THM_ORGANIZER_RAW_PERCENT_TIP'));
		$this->spreadSheet->getActiveSheet()->setCellValue("A6", JText::_('COM_THM_ORGANIZER_WEIGHTED_UTIL_TEXT'));
		$this->spreadSheet->getActiveSheet()->setCellValue("B6", JText::_('COM_THM_ORGANIZER_WEIGHTED_UTIL_TIP'));
		$this->spreadSheet->getActiveSheet()->setCellValue("A7", JText::_('COM_THM_ORGANIZER_WEIGHTED_PERCENT_TEXT'));
		$this->spreadSheet->getActiveSheet()->setCellValue("B7", JText::_('COM_THM_ORGANIZER_WEIGHTED_PERCENT_TIP'));

		$this->spreadSheet->getActiveSheet()->mergeCells("A9:B9");
		$this->spreadSheet->getActiveSheet()->setCellValue('A9', JText::_('COM_THM_ORGANIZER_ROOM_TYPES'));
		$this->spreadSheet->getActiveSheet()->getStyle('A9')->getFont()->setSize(14);
		$rowNumber = 9;

		foreach ($this->roomTypes as $typeData)
		{
			$rowNumber++;
			$this->spreadSheet->getActiveSheet()->setCellValue("A$rowNumber", $typeData['name']);
			$this->spreadSheet->getActiveSheet()->setCellValue("B$rowNumber", $typeData['description']);
		}

		$this->spreadSheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$this->spreadSheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	}

	/**
	 * Creates a room data summary row
	 *
	 * @param int   $rowNo     the row number
	 * @param int   $roomID    the room id
	 * @param array $weeksData the utilization data grouped by week number
	 *
	 * @return void
	 */
	private function addSummaryDataRow($rowNo, $roomID, $weeksData)
	{
		$this->spreadSheet->getActiveSheet()->setCellValue("A{$rowNo}", $this->rooms[$roomID]);
		$roomType = (empty($this->roomTypeMap[$roomID]) OR empty($this->roomTypes[$this->roomTypeMap[$roomID]]))?
			'' : $this->roomTypes[$this->roomTypeMap[$roomID]]['name'];
		$this->spreadSheet->getActiveSheet()->setCellValue("B{$rowNo}", $roomType);

		$total         = 0;
		$adjustedTotal = 0;
		$use           = 0;
		$adjustedUse   = 0;

		foreach ($weeksData as $weekData)
		{
			$total += $weekData['total'];
			$adjustedTotal += $weekData['adjustedTotal'];
			$use += $weekData['use'];
			$adjustedUse += $weekData['adjustedUse'];
		}

		$this->spreadSheet->getActiveSheet()->setCellValue("C{$rowNo}", $use);
		$sumValue = empty($total)? 0 : $use / $total;
		$this->spreadSheet->getActiveSheet()->setCellValue("D{$rowNo}", $sumValue);
		$this->spreadSheet->getActiveSheet()->getStyle("D{$rowNo}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);

		$this->spreadSheet->getActiveSheet()->setCellValue("E{$rowNo}", $adjustedUse);
		$adjSumValue = empty($adjustedTotal)? 0 : $adjustedUse / $adjustedTotal;
		$this->spreadSheet->getActiveSheet()->setCellValue("F{$rowNo}", $adjSumValue);
		$this->spreadSheet->getActiveSheet()->getStyle("F{$rowNo}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);

		return array('total' => $total, 'adjustedTotal' => $adjustedTotal);
	}

	/**
	 * Creates a summary sheet
	 *
	 * @return void
	 */
	private function addSummarySheet()
	{
		$this->spreadSheet->setActiveSheetIndex(0);
		$this->spreadSheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight('18');
		$this->spreadSheet->getActiveSheet()->setTitle(JTEXT::_('COM_THM_ORGANIZER_SUMMARY'));
		$this->spreadSheet->getActiveSheet()->mergeCells("A1:F1");
		$title = JText::_('COM_THM_ORGANIZER_ROOM_STATISTICS_TITLE') . ' - ';
		$title .= ' ' . $this->startDate . ' ' . JText::_('COM_THM_ORGANIZER_UNTIL') . ' ' . $this->endDate;
		$this->spreadSheet->getActiveSheet()->setCellValue('A1', $title);
		$this->spreadSheet->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);

		// TableStartRow is
		$headerRow = 6;
		$firstRow = $headerRow + 1;
		$lastRow  = $headerRow;

		foreach ($this->roomData as $roomID => $roomData)
		{
			$lastRow++;
			$totals = $this->addSummaryDataRow($lastRow, $roomID, $roomData['weeks']);
		}

		$this->spreadSheet->getActiveSheet()->setCellValue("C3", JText::_('COM_THM_ORGANIZER_RAW_UTIL_TEXT'));
		$this->spreadSheet->getActiveSheet()->setCellValue("D3", JText::_('COM_THM_ORGANIZER_RAW_PERCENT_TEXT'));
		$this->spreadSheet->getActiveSheet()->setCellValue("E3", JText::_('COM_THM_ORGANIZER_WEIGHTED_UTIL_TEXT'));
		$this->spreadSheet->getActiveSheet()->setCellValue("F3", JText::_('COM_THM_ORGANIZER_WEIGHTED_PERCENT_TEXT'));


		$this->spreadSheet->getActiveSheet()->setCellValue('A6', JText::_('COM_THM_ORGANIZER_NAME'));
		$this->spreadSheet->getActiveSheet()->setCellValue('B6', JText::_('COM_THM_ORGANIZER_ROOM_TYPE'));
		$this->spreadSheet->getActiveSheet()->setCellValue("C6", JText::_('COM_THM_ORGANIZER_RAW_UTIL_TEXT'));
		$this->spreadSheet->getActiveSheet()->setCellValue("D6", JText::_('COM_THM_ORGANIZER_RAW_PERCENT_TEXT'));
		$this->spreadSheet->getActiveSheet()->setCellValue("E6", JText::_('COM_THM_ORGANIZER_WEIGHTED_UTIL_TEXT'));
		$this->spreadSheet->getActiveSheet()->setCellValue("F6", JText::_('COM_THM_ORGANIZER_WEIGHTED_PERCENT_TEXT'));
		$this->spreadSheet->getActiveSheet()->setAutoFilter("A6:F{$lastRow}");

		$this->spreadSheet->getActiveSheet()->setCellValue('B4', JText::_('COM_THM_ORGANIZER_SUMMARY'));
		$this->spreadSheet->getActiveSheet()->setCellValue('C4', "=SUBTOTAL(109,C{$firstRow}:C{$lastRow})");
		$this->spreadSheet->getActiveSheet()
			->setCellValue('D4', "=C4/(SUBTOTAL(102,C{$firstRow}:C{$lastRow})*{$totals['total']})");
		$this->spreadSheet->getActiveSheet()->getStyle("D4")->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
		$this->spreadSheet->getActiveSheet()->setCellValue('E4', "=SUBTOTAL(109,E{$firstRow}:E{$lastRow})");
		$this->spreadSheet->getActiveSheet()
			->setCellValue('F4', "=E4/(SUBTOTAL(102,E{$firstRow}:E{$lastRow})*{$totals['adjustedTotal']})");
		$this->spreadSheet->getActiveSheet()->getStyle("F4")->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);

		$this->spreadSheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$this->spreadSheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$this->spreadSheet->getActiveSheet()->getColumnDimension('C')->setWidth(12.5);
		$this->spreadSheet->getActiveSheet()->getColumnDimension('D')->setWidth(12.5);
		$this->spreadSheet->getActiveSheet()->getColumnDimension('E')->setWidth(12.5);
		$this->spreadSheet->getActiveSheet()->getColumnDimension('F')->setWidth(12.5);
	}

	/**
	 * Creates a room data summary row
	 *
	 * @param int   $rowNo     the row number
	 * @param int   $roomID    the room id
	 * @param array $weeksData the utilization data grouped by week number
	 *
	 * @return string the last column name
	 */
	private function addWeekDataRow($rowNo, $roomID, $weeksData)
	{
		$this->spreadSheet->getActiveSheet(1)->setCellValue("A{$rowNo}", $this->rooms[$roomID]);
		$this->spreadSheet->getActiveSheet(1)->setCellValue("B{$rowNo}", $this->roomTypes[$this->roomTypeMap[$roomID]]['name']);

		$total         = 0;
		$adjustedTotal = 0;
		$use           = 0;
		$adjustedUse   = 0;

		$column = 'B';
		foreach ($weeksData as $weekData)
		{
			$total += $weekData['total'];
			$adjustedTotal += $weekData['adjustedTotal'];
			$use += $weekData['use'];
			$adjustedUse += $weekData['adjustedUse'];

			++$column;
			$this->spreadSheet->getActiveSheet()->setCellValue("{$column}{$rowNo}", $weekData['use']);
			++$column;
			$value = empty($weekData['total'])? 0 : $weekData['use'] / $weekData['total'];
			$this->spreadSheet->getActiveSheet()->setCellValue("{$column}{$rowNo}", $value);
			$this->spreadSheet->getActiveSheet()->getStyle("{$column}{$rowNo}")->getNumberFormat()
				->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
			++$column;
			$this->spreadSheet->getActiveSheet()->setCellValue("{$column}{$rowNo}", $weekData['adjustedUse']);
			++$column;
			$adjustedValue = empty($weekData['adjustedTotal'])? 0 : $weekData['adjustedUse'] / $weekData['adjustedTotal'];
			$this->spreadSheet->getActiveSheet()->setCellValue("{$column}{$rowNo}", $adjustedValue);
			$this->spreadSheet->getActiveSheet()->getStyle("{$column}{$rowNo}")->getNumberFormat()
				->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
		}

		return $column;
	}

	/**
	 * Adds a header group consisting of a title row of 4 merged cells and a header row consisting of 4 header cells
	 *
	 * @param string $startColumn the column letter where the column group should start
	 *
	 * @return void
	 */
	private function addWeekHeaderGroup($startColumn, $groupTitle)
	{
		++$startColumn;
		$currentColumn = $startColumn;
		$this->spreadSheet->getActiveSheet()->setCellValue("{$currentColumn}3", JText::_('COM_THM_ORGANIZER_RAW_UTIL_TEXT'));
		++$currentColumn;
		$this->spreadSheet->getActiveSheet()->setCellValue("{$currentColumn}3", JText::_('COM_THM_ORGANIZER_RAW_PERCENT_TEXT'));
		++$currentColumn;
		$this->spreadSheet->getActiveSheet()->setCellValue("{$currentColumn}3", JText::_('COM_THM_ORGANIZER_WEIGHTED_UTIL_TEXT'));
		++$currentColumn;
		$this->spreadSheet->getActiveSheet()->setCellValue("{$currentColumn}3", JText::_('COM_THM_ORGANIZER_WEIGHTED_PERCENT_TEXT'));

		$this->spreadSheet->getActiveSheet()->mergeCells("{$startColumn}2:{$currentColumn}2");
		$this->spreadSheet->getActiveSheet()->setCellValue("{$startColumn}2", $groupTitle);

		return $currentColumn;
	}

	/**
	 * Adds column headers to the sheet
	 *
	 * @return void
	 */
	private function addWeekSheet()
	{
		$this->spreadSheet->createSheet();
		$this->spreadSheet->setActiveSheetIndex(1);
		$this->spreadSheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight('18');
		$this->spreadSheet->getActiveSheet()->setTitle(JTEXT::_('COM_THM_ORGANIZER_BY_WEEK'));
		$this->spreadSheet->getActiveSheet()->mergeCells("A1:H1");
		$title = JText::_('COM_THM_ORGANIZER_ROOM_STATISTICS_TITLE') . ' - ' . JText::_('COM_THM_ORGANIZER_BY_WEEK');
		$this->spreadSheet->getActiveSheet()->setCellValue('A1', $title);
		$this->spreadSheet->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);

		// Merge the blank cells in the upper left corner
		$this->spreadSheet->getActiveSheet()->mergeCells("A2:B2");

		$startRow = 3;
		$lastRow  = $startRow;
		foreach ($this->roomData as $roomID => $roomData)
		{
			$lastRow++;
			$lastColumn = $this->addWeekDataRow($lastRow, $roomID, $roomData['weeks']);
		}

		$currentColumn = 'B';

		foreach ($this->metaData['weeks'] as $weekData)
		{
			$startDate     = THM_OrganizerHelperComponent::formatDate($weekData['startDate']);
			$endDate       = THM_OrganizerHelperComponent::formatDate($weekData['endDate']);
			$groupTitle    = "$startDate - $endDate";
			$currentColumn = $this->addWeekHeaderGroup($currentColumn, $groupTitle);
		}

		$this->spreadSheet->getActiveSheet()->setCellValue('A3', JText::_('COM_THM_ORGANIZER_NAME'));
		$this->spreadSheet->getActiveSheet()->setCellValue('B3', JText::_('COM_THM_ORGANIZER_ROOM_TYPE'));
		$this->spreadSheet->getActiveSheet()->setAutoFilter("A3:{$lastColumn}{$lastRow}");

		$this->spreadSheet->getActiveSheet()->getColumnDimension('A')->setWidth(12.5);
		$this->spreadSheet->getActiveSheet()->getColumnDimension('B')->setWidth(12.5);
		foreach(range('C',$lastColumn) as $columnID)
		{
			$this->spreadSheet->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
		}
	}

	/**
	 * Outputs the generated Excel file
	 *
	 * @return void
	 */
	public function render()
	{
		$objWriter = PHPExcel_IOFactory::createWriter($this->spreadSheet, 'Excel2007');
		ob_end_clean();
		header('Content-type: application/vnd.ms-excel');
		$docTitle = JApplicationHelper::stringURLSafe(JText::_('COM_THM_ORGANIZER_ROOM_STATISTICS_EXPORT_TITLE') . '_' . date('Ymd'));
		header("Content-Disposition: attachment;filename=$docTitle.xlsx");
		$objWriter->save('php://output');
		exit();
	}
}