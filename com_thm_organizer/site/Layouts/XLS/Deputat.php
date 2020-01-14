<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Layouts\XLS;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;

/**
 * Class generates the room statistics XLS file.
 */
class Deputat
{
	private $borders;

	private $fills;

	private $heights;

	/**
	 * THM_OrganizerTemplateRoom_Statistics_XLS constructor.
	 */
	public function __construct()
	{
		$this->spreadSheet = new \PHPExcel();

		$userName    = Factory::getUser()->name;
		$term        = 'TERM';
		$date        = \Date::formatDate(date('Y-m-d'));
		$description = Languages::sprintf('THM_ORGANIZER_DEPUTAT_DESCRIPTION', $term, $date);
		$this->spreadSheet->getProperties()->setCreator('THM Organizer')
			->setLastModifiedBy($userName)
			->setTitle(Languages::_('THM_ORGANIZER_DEPUTAT'))
			->setDescription($description);
		$this->spreadSheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

		$this->setStyles();
		$this->addInstructionSheet();
		$this->addWorkSheet();

		// Reset the active sheet to the first item
		$this->spreadSheet->setActiveSheetIndex(0);
	}

	/**
	 * Adds a basic field (label and input box)
	 *
	 * @param   int     $row    the row where the cells should be edited
	 * @param   string  $label  the field label
	 *
	 * @return void
	 */
	private function addBasicField($row, $label)
	{
		$activeSheet = $this->spreadSheet->getActiveSheet();
		$activeSheet->getStyle("B$row")->applyFromArray([
			'borders' => $this->borders['header'],
			'fill'    => $this->fills['header']
		]);
		$activeSheet->getStyle("B$row")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$activeSheet->setCellValue("B$row", $label);
		$activeSheet->getStyle("B$row")->getFont()->setSize('11');
		$activeSheet->getStyle("B$row")->getFont()->setBold(true);
		$activeSheet->getStyle("C$row:D$row")->applyFromArray([
			'borders' => $this->borders['header']
		]);
	}

	/**
	 * Adds a column header to the sheet
	 *
	 * @param   string  $startCell  the coordinates of the column headers top left most cell
	 * @param   string  $endCell    the coordinates of the column header's bottom right most cell
	 * @param   string  $text       the column header
	 * @param   array   $comments   the comments necessary for clarification of the column's contents
	 *
	 * @return void
	 */
	private function addColumnHeader($startCell, $endCell, $text, $comments = [])
	{
		$activeSheet = $this->spreadSheet->getActiveSheet();
		$activeSheet->mergeCells("$startCell:$endCell");
		$activeSheet->getStyle("$startCell:$endCell")->applyFromArray([
			'borders' => $this->borders['header'],
			'fill'    => $this->fills['header'],
			'font'    => ['bold' => true]
		]);
		$alignment = $activeSheet->getStyle($startCell)->getAlignment();
		$alignment->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$alignment->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
		$alignment->setWrapText(true);
		$activeSheet->setCellValue($startCell, $text);

		if (!empty($comments))
		{
			foreach ($comments as $comment)
			{
				$this->addComment($startCell, $comment);
			}
		}
	}

	/**
	 * Adds a comment to a specific cell
	 *
	 * @param   string  $cell     the cell coordinates
	 * @param   array   $comment  an associative array with a title and or text
	 *
	 * @return void
	 */
	private function addComment($cell, $comment)
	{
		if (empty($comment['title']) and empty($comment['text']))
		{
			return;
		}

		$activeSheet = $this->spreadSheet->getActiveSheet();
		$activeSheet->getComment($cell)->setWidth(320);
		$activeSheet->getComment($cell)->setHeight(160);
		if (!empty($comment['title']))
		{
			$commentTitle = $activeSheet->getComment($cell)->getText()->createTextRun($comment['title']);
			$commentTitle->getFont()->setBold(true);
			if (!empty($comment['text']))
			{
				$activeSheet->getComment($cell)->getText()->createTextRun('\r\n');
			}
		}
		if (!empty($comment['text']))
		{
			$activeSheet->getComment($cell)->getText()->createTextRun($comment['text']);
		}

		return;
	}

	/**
	 * Adds the THM Logo to a cell.
	 *
	 * @param   string  $cell     the cell coordinates
	 * @param   int     $height   the display height of the logo
	 * @param   int     $offsetY  the offset from the top of the cell
	 *
	 * @return void
	 */
	private function addLogo($cell, $height, $offsetY)
	{
		$objDrawing = new \PHPExcel_Worksheet_Drawing();
		$objDrawing->setName('THM Logo');
		$objDrawing->setDescription('THM Logo');
		$objDrawing->setPath(JPATH_COMPONENT_SITE . '/images/thm_logo.png');
		$objDrawing->setCoordinates($cell);
		$objDrawing->setHeight($height);
		$objDrawing->setOffsetY($offsetY);
		$activeSheet = $this->spreadSheet->getActiveSheet();
		$objDrawing->setWorksheet($activeSheet);
	}

	/**
	 * Creates an instructions sheet
	 *
	 * @return void
	 */
	private function addInstructionSheet()
	{
		$this->spreadSheet->setActiveSheetIndex(0);
		$activeSheet = $this->spreadSheet->getActiveSheet();

		$activeSheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
		$activeSheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
		$activeSheet->getPageSetup()->setFitToPage(true);

		$activeSheet->setTitle('Anleitung');
		$activeSheet->setShowGridlines(false);
		$activeSheet->getColumnDimension('A')->setWidth(5);
		$activeSheet->getColumnDimension('B')->setWidth(75);
		$activeSheet->getColumnDimension('C')->setWidth(5);
		$activeSheet->getRowDimension('1')->setRowHeight('85');

		$this->addLogo('B1', 60, 25);

		$activeSheet->getRowDimension('2')->setRowHeight('90');
		$preface = 'Mit dem ablaufenden Wintersemester 2017/18 wird ein leicht veränderter B-Bogen in Umlauf ';
		$preface .= 'gesetzt. Er dient einer dezi\ndieteren Kostenrechnung. Bitte nutzen Sie ausschließlich diesen ';
		$preface .= 'Bogen.';
		$activeSheet->setCellValue('B2', $preface);
		$activeSheet->getStyle('B2')->getAlignment()->setWrapText(true);
		$activeSheet->getStyle('B2')->getAlignment()
			->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
		$activeSheet->getStyle('B2')->getFont()->setSize('14');

		$activeSheet->getRowDimension('3')->setRowHeight('35');
		$activeSheet->setCellValue('B3', 'Hinweise:');
		$activeSheet->getStyle('B3')->getFont()->setBold(true);
		$activeSheet->getStyle('B3')->getFont()->setUnderline(true);
		$activeSheet->getStyle('B3')->getAlignment()
			->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
		$activeSheet->getStyle('B3')->getFont()->setSize('14');

		$activeSheet->getRowDimension('4')->setRowHeight('55');
		$program1 = 'In der Spalte "Studiengang" ist eine Auswahlliste für Ihren Fachbereich hinterlegt. ';
		$program1 .= 'Bitte klicken Sie den entsprechenden Studiengang an.';
		$activeSheet->setCellValue('B4', $program1);
		$activeSheet->getStyle('B4')->getAlignment()->setWrapText(true);
		$activeSheet->getStyle('B4')->getAlignment()
			->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
		$activeSheet->getStyle('B4')->getFont()->setSize('14');

		$activeSheet->getRowDimension('5')->setRowHeight('55');
		$program4 = 'Sollten Sie in der Auswahlliste einen Studiengang nicht finden, so nutzen Sie bitte die ';
		$program4 .= 'letzte Rubrik "nicht vorgegeben". ';
		$activeSheet->setCellValue('B5', $program4);
		$activeSheet->getStyle('B5')->getAlignment()->setWrapText(true);
		$activeSheet->getStyle('B5')->getAlignment()
			->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
		$activeSheet->getStyle('B5')->getFont()->setSize('14');

		$activeSheet->getRowDimension('6')->setRowHeight('55');
		$program2 = 'Sollte eine Lehrveranstaltung in mehreren Studiengängen sein, so können Sie, dann über ';
		$program2 .= 'mehrere Zeilen, nach Ihrem Ermessen quoteln.';
		$activeSheet->setCellValue('B6', $program2);
		$activeSheet->getStyle('B6')->getAlignment()->setWrapText(true);
		$activeSheet->getStyle('B6')->getAlignment()
			->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
		$activeSheet->getStyle('B6')->getFont()->setSize('14');

		$activeSheet->getRowDimension('7')->setRowHeight('45');
		$program3 = 'So können alle Studiengänge berücksichtigt werden. ';
		$activeSheet->setCellValue('B7', $program3);
		$activeSheet->getStyle('B7')->getAlignment()
			->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
		$activeSheet->getStyle('B7')->getFont()->setSize('14');

		$activeSheet->getRowDimension('8')->setRowHeight('90');
		$department1 = 'Sollten Sie eine Lehrveranstaltung gehalten haben, die in mehreren Fachbereichen ';
		$department1 .= 'angeboten wird, so verfahren Sie bitte analog, nutzen aber die Rubrik "mehrere ';
		$department1 .= 'Fachbereiche", da dort eine  Auswahlliste hinterlegt ist, die alle Studiengänge ';
		$department1 .= 'der THM enthält.';
		$activeSheet->setCellValue('B8', $department1);
		$activeSheet->getStyle('B8')->getAlignment()->setWrapText(true);
		$activeSheet->getStyle('B8')->getAlignment()
			->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
		$activeSheet->getStyle('B8')->getFont()->setSize('14');

		$activeSheet->getRowDimension('9')->setRowHeight('20');
		$department2 = 'Die Liste ist nach Fachbereichen geordnet.';
		$activeSheet->setCellValue('B9', $department2);
		$activeSheet->getStyle('B9')->getAlignment()
			->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
		$activeSheet->getStyle('B9')->getFont()->setSize('14');

		$activeSheet->getRowDimension('10')->setRowHeight('20');
		$activeSheet->getRowDimension('11')->setRowHeight('20');
		$activeSheet->setCellValue('B11', 'Für Ihre Mühe danke ich Ihnen,');
		$activeSheet->getStyle('B11')->getFont()->setSize('14');
		$activeSheet->getRowDimension('12')->setRowHeight('20');
		$activeSheet->setCellValue('B12', 'Prof. Olaf Berger');
		$activeSheet->getStyle('B12')->getFont()->setSize('14');

		$noOutline = [
			'borders' => [
				'outline' => [
					'style' => PHPExcel_Style_Border::BORDER_NONE
				],
			],
		];
		$activeSheet->getStyle('A1:C12')->applyFromArray($noOutline);
	}

	/**
	 * Adds a lesson row at the given row number
	 *
	 * @param   int  $row  the row number
	 *
	 * @return void
	 */
	private function addLessonRow($row)
	{
		$activeSheet = $this->spreadSheet->getActiveSheet();

		$activeSheet->mergeCells("C$row:E$row");
		$activeSheet->mergeCells("K$row:L$row");
		for ($current = 'B'; $current <= 'M'; $current++)
		{
			if ($current === 'B' or $current === 'H' or $current === 'I')
			{
				$activeSheet->getStyle("$current$row")->applyFromArray([
					'borders' => $this->borders['data'],
					'fill'    => $this->fills['index']
				]);
				continue;
			}

			$activeSheet->getStyle("$current$row")->applyFromArray([
				'borders' => $this->borders['data'],
				'fill'    => $this->fills['data']
			]);
		}
	}

	/**
	 * Adds a lesson row at the given row number
	 *
	 * @param   int     $row   the row number
	 * @param   string  $text  the text for the labeling column
	 *
	 * @return void
	 */
	private function addLessonSubHeadRow($row, $text)
	{
		$activeSheet = $this->spreadSheet->getActiveSheet();
		$activeSheet->mergeCells("C$row:E$row");
		$activeSheet->mergeCells("K$row:L$row");
		for ($current = 'B'; $current <= 'M'; $current++)
		{
			$activeSheet->getStyle("$current$row")->applyFromArray([
				'borders' => $this->borders['data'],
				'fill'    => $this->fills['header']
			]);
			if ($current === 'B')
			{
				$activeSheet->setCellValue("B$row", $text);
				$activeSheet->getStyle("B$row")->getFont()->setBold(true);
				$activeSheet->getStyle("B$row")->getAlignment()->setWrapText(true);
			}
		}
	}

	/**
	 * Creates and formats a row to be used for a deputat relevant role listing.
	 *
	 * @param   int  $row  the row to add
	 *
	 * @return void
	 */
	private function addRoleRow($row)
	{
		$activeSheet = $this->spreadSheet->getActiveSheet();

		$activeSheet->mergeCells("B$row:C$row");
		$activeSheet->getStyle("B$row:C$row")->applyFromArray([
			'borders' => $this->borders['cell']
		]);
		$activeSheet->mergeCells("D$row:G$row");
		$activeSheet->getStyle("D$row:G$row")->applyFromArray([
			'borders' => $this->borders['cell']
		]);
		$activeSheet->mergeCells("H$row:L$row");
		$activeSheet->getStyle("H$row:L$row")->applyFromArray([
			'borders' => $this->borders['cell'],
			'fill'    => $this->fills['data']
		]);
		$activeSheet->getStyle("M$row")->applyFromArray([
			'borders' => $this->borders['cell'],
			'fill'    => $this->fills['data']
		]);
	}

	/**
	 * Adds the section which lists held lessons to the worksheet
	 *
	 * @param   int &$row  the current row number
	 *
	 * @return void
	 */
	private function addSectionA(&$row)
	{
		$this->addSectionHeader($row, "A. Lehrveranstaltungen", true);

		$startRow = $row + 2;
		$endRow   = $row + 4;

		$this->addColumnHeader("B14", "B16", 'ModulNr');

		$vParaText = '„Die Lehrenden teilen jeweils am Ende eines Semesters unter thematischer Bezeichnung der ';
		$vParaText .= 'einzelnen Lehrveranstaltungen Art und Umfang ihrer Lehrtätigkeit und die Zahl der ';
		$vParaText .= 'gegebenenfalls mitwirkenden Lehrkräfte, bei Lehrveranstaltungen mit beschränkter ';
		$vParaText .= 'Teilnehmerzahl auch die Zahl der teilnehmenden Studierenden sowie der betreuten ';
		$vParaText .= 'Abschlussarbeiten und vergleichbaren Studienarbeiten der Fachbereichsleitung schriftlich mit. ';
		$vParaText .= 'Wesentliche Unterbrechungen, die nicht ausgeglichen worden sind, sind anzugeben. Bei ';
		$vParaText .= 'Nichterfüllung der Lehrverpflichtung unterrichtet die Fachbereichsleitung die ';
		$vParaText .= 'Hochschulleitung.“';

		$vComments = [
			['title' => 'Nur auszufüllen, wenn entsprechende Module definiert und bezeichnet sind.'],
			['title' => 'LVVO vom 10.9.2013, § 4 (5)', 'text' => $vParaText]
		];
		$this->addColumnHeader("C$startRow", "E$endRow", 'Lehrveranstaltung', $vComments);
		// Comment
		$this->addColumnHeader("F$startRow", "F$endRow", 'Art (Kürzel)');
		// Comment and differing text styles
		$this->addColumnHeader("G$startRow", "G$endRow", "Lehrumfang gemäß SWS Lehrumfang");
		// Comment
		$this->addColumnHeader("H$startRow", "H$endRow", "Studien-\ngang");
		$this->addColumnHeader("I$startRow", "I$endRow", 'Semester');
		// Two comments
		$this->addColumnHeader("J$startRow", "J$endRow", "Pflicht-\nstatus\n(Kürzel)");
		// Two comments
		$this->addColumnHeader("K$startRow", "L$endRow", "Wochentag u. Stunde\n(bei Blockveranst. Datum)");
		// Comment
		$this->addColumnHeader("M$startRow", "M$endRow", "Gemeldetes\nDeputat\n(SWS)");
		$row      = $row + 5;
		$ownRange = ['start' => $row, 'end' => $row + 11];
		for ($current = $ownRange['start']; $current <= $ownRange['end']; $current++)
		{
			$this->addLessonRow($current);
			$row++;
		}

		$this->addLessonSubHeadRow($row++, 'Mehrere Fachbereiche');
		$otherRange = ['start' => $row, 'end' => $row + 3];
		for ($current = $otherRange['start']; $current <= $otherRange['end']; $current++)
		{
			$this->addLessonRow($current);
			$row++;
		}

		$this->addLessonSubHeadRow($row++, 'Nicht vorgegeaben');
		$unknownRange = ['start' => $row, 'end' => $row + 1];
		for ($current = $unknownRange['start']; $current <= $unknownRange['end']; $current++)
		{
			$this->addLessonRow($current);
			$row++;
		}

		$ranges = [$ownRange, $otherRange, $unknownRange];

		$this->addSumRow($row++, 'A', $ranges);

		$activeSheet = $this->spreadSheet->getActiveSheet();
		$activeSheet->getRowDimension($row++)->setRowHeight($this->heights['spacer']);
	}

	/**
	 * Adds the section which lists thesis supervisions
	 *
	 * @param   int &$row  the current row number
	 *
	 * @return void
	 */
	private function addSectionB(&$row)
	{
		$comments = [
			['title' => 'Olaf Berger:', 'text' => 'Laut LVVO und HMWK ist eine maximale Grenze von 2 SWS zu beachten.']
		];
		$this->addSectionHeader($row, "B. Betreuung von Studien- und Abschlussarbeiten", true, $comments);

		$startRow = $row + 2;
		$endRow   = $row + 4;

		$this->addColumnHeader("B$startRow", "B$endRow", 'Rechtsgrundlage gemäß LVVO');
		$this->addColumnHeader("C$startRow", "F$endRow", 'Art der Abschlussarbeit (nur bei Betreuung als Referent/in)');
		$this->addColumnHeader(
			"G$startRow",
			"J$endRow",
			"Umfang der Anrechnung in SWS je Arbeit (insgesamt max. 2 SWS)"
		);
		$this->addColumnHeader("K$startRow", "L$endRow", "Anzahl der Arbeiten");
		$this->addColumnHeader("M$startRow", "M$endRow", "Gemeldetes\nDeputat\n(SWS)");
		$row = $endRow + 1;

		$startRow = $row;
		$bachelor = ['text' => 'Betreuung von Bachelorarbeit(en) ', 'weight' => .3];
		$this->addSupervisionRow($row++, $bachelor);
		$master = ['text' => 'Betreuung von Masterarbeit(en)', 'weight' => .6];
		$this->addSupervisionRow($row++, $master);
		$projects = ['text' => 'Betreuung von Projekt- und Studienarbeiten, BPS', 'weight' => .15];
		$this->addSupervisionRow($row++, $projects);
		$endRow = $row;
		$doctor = ['text' => 'Betreuung von Promotionen (bis max 6 Semester)', 'weight' => .65];
		$this->addSupervisionRow($row++, $doctor);

		$ranges = [['start' => $startRow, 'end' => $endRow]];
		$this->addSumRow($row++, 'B', $ranges);

		$activeSheet = $this->spreadSheet->getActiveSheet();
		$activeSheet->getRowDimension($row++)->setRowHeight($this->heights['spacer']);
	}

	/**
	 * Adds the section which lists roles for which deputat is calculated
	 *
	 * @param   int &$row  the current row number
	 *
	 * @return void
	 */
	private function addSectionC(&$row)
	{
		$this->addSectionHeader($row++, "C. Deputatsfreistellungen", true);

		// For the table headers
		$startRow = ++$row;
		$endRow   = ++$row;

		$this->addColumnHeader("B$startRow", "C$endRow", 'Rechtsgrundlage gemäß LVVO');
		$this->addColumnHeader("D$startRow", "G$endRow", 'Grund für Deputatsfreistellung');
		$this->addColumnHeader(
			"H$startRow",
			"L$endRow",
			"Bezeichnung aus dem Genehmigungsschreiben bzw. Dekanatsunterlagen"
		);
		$this->addColumnHeader("M$startRow", "M$endRow", "Gemeldetes\nDeputat\n(SWS)");
		$row++;

		// For the table
		$startRow = $row;

		$activeSheet = $this->spreadSheet->getActiveSheet();

		$this->addRoleRow($row);

		$activeSheet->setCellValue("B$row", "LVVO § 5 (1)");
		$title = "LVVO § 5 (1):";
		$text  = "„Bei Wahrnehmung einer Funktion in der Hochschulleitung kann die Lehrverpflichtung um bis zu 100 ";
		$text  .= "Prozent, bei Wahrnehmung einer Funktion in der Fachbereichsleitung um bis zu 75 Prozent ermäßigt ";
		$text  .= "werden. Soweit eine Ermäßigung für mehrere Personen in der Fachbereichsleitung erfolgt, ";
		$text  .= "darf die durchschnittliche Ermäßigung 50 Prozent nicht übersteigen.“";
		$this->addComment("B$row", ['title' => $title, 'text' => $text]);

		$activeSheet->setCellValue("D$row", 'Dekanatsamt (Dekan, Studiendekan, Prodekan)');
		$row++;

		$this->addRoleRow($row);

		$activeSheet->setCellValue("B$row", "LVVO § 5 (2, 4 und 5)");
		$title = "LVVO vom 10.9.2013, § 5 (2):";
		$text  = "„Die Lehrverpflichtung kann für die Wahrnehmung weiterer Aufgaben und Funktionen innerhalb der ";
		$text  .= "Hochschule, insbesondere für besondere Aufgaben der Studienreform, für die Leitung von ";
		$text  .= "Sonderforschungsbereichen und für Studienfachberatung unter Berücksichtigung des Lehrbedarfs im ";
		$text  .= "jeweiligen Fach ermäßigt werden; die Ermäßigung soll im Einzelfall zwei Lehrveranstaltungsstunden ";
		$text  .= "nicht überschreiten. Für die Teilnahme an der Entwicklung und Durchführung von hochschuleigenen ";
		$text  .= "Auswahlverfahren und von Verfahren nach § 54 Abs. 4 des Hessischen Hochschulgesetzes sowie für die ";
		$text  .= "Wahrnehmung der Mentorentätigkeit nach § 14 Satz 5 des Hessischen Hochschulgesetzes erhalten ";
		$text  .= "Professorinnen und Professoren keine Ermäßigung der Lehrverpflichtung.“";
		$this->addComment("B$row", ['title' => $title, 'text' => $text]);
		$activeSheet->getComment("B$row")->getText()->createTextRun("\r\n");
		$title = 'LVVO vom 10.9.2006, §5 (4):';
		$text  = '„An Fachhochschulen kann die Lehrverpflichtung für die Wahrnehmung von Forschungs- und ';
		$text  .= 'Entwicklungsaufgaben, für die Leitung und Verwaltung von zentralen Einrichtungen der Hochschule, ';
		$text  .= 'die Betreuung von Sammlungen einschließlich Bibliotheken sowie die Leitung des Praktikantenamtes ';
		$text  .= 'ermäßigt werden; die Ermäßigung soll zwölf Prozent der Gesamtheit der Lehrverpflichtungen der ';
		$text  .= 'hauptberuflich Lehrenden und bei einzelnen Professorinnen und Professoren vier ';
		$text  .= 'Lehrveranstaltungsstunden nicht überschreiten. Die personenbezogene Höchstgrenze gilt nicht im ';
		$text  .= "Falle der Wahrnehmung von Forschungs- und Entwicklungsaufgaben. Soweit aus Einnahmen von ";
		$text  .= "Drittmitteln für Forschungs- und Entwicklungsaufträge oder Projektdurchführung Lehrpersonal ";
		$text  .= "finanziert wird, kann die Lehrverpflichtung von Professorinnen und Professoren in dem ";
		$text  .= "entsprechenden Umfang auf bis zu vier Lehrveranstaltungsstunden reduziert werden; diese ";
		$text  .= "Ermäßigungen sind auf die zulässige Höchstgrenze der Ermäßigung der Gesamtlehrverpflichtung ";
		$text  .= "nicht anzurechnen. Voraussetzung für die Übernahme von Verwaltungsaufgaben ist, dass ";
		$text  .= "diese Aufgaben von der Hochschulverwaltung nicht übernommen werden können und deren Übernahme ";
		$text  .= "zusätzlich zu der Lehrverpflichtung wegen der damit verbundenen Belastung nicht zumutbar ist.“";
		$this->addComment("B$row", ['title' => $title, 'text' => $text]);
		$activeSheet->getComment("B$row")->getText()->createTextRun("\r\n");
		$title = "LVVO vom 10.9.2013, § 5 (5):";
		$text  = "„Liegen mehrere Ermäßigungsvoraussetzungen nach Abs. 1 bis 4 Satz 2 vor, soll die Lehrtätigkeit im ";
		$text  .= "Einzelfall während eines Semesters 50 vom Hundert der jeweiligen Lehrverpflichtung nicht ";
		$text  .= "unterschreiten.“";
		$this->addComment("B$row", ['title' => $title, 'text' => $text]);

		$activeSheet->setCellValue("D$row", 'Weitere Deputatsreduzierungen');
		$row++;
		$endRow = $row;

		$this->addRoleRow($row);

		$activeSheet->setCellValue("B$row", "LVVO § 6");
		$title = "LVVO vom 10.9.2013, § 6:";
		$text  = "„Die Lehrverpflichtung schwerbehinderter Menschen im Sinne des Neunten Buches Sozialgesetzbuch - ";
		$text  .= "Rehabilitation und Teilhabe behinderter Menschen - vom 19. Juni 2001 (BGBl. I S. 1046, 1047), ";
		$text  .= "zuletzt geändert durch Gesetz vom 14. Dezember 2012 (BGBl. I S. 2598), kann auf Antrag von der ";
		$text  .= "Hochschulleitung ermäßigt werden.“";
		$this->addComment("B$row", ['title' => $title, 'text' => $text]);

		$activeSheet->setCellValue("D$row", 'Schwerbehinderung');
		$row++;

		$ranges = [['start' => $startRow, 'end' => $endRow]];
		$this->addSumRow($row++, 'C', $ranges);

		$activeSheet->getRowDimension($row++)->setRowHeight($this->heights['spacer']);
	}

	/**
	 * Creates a section header
	 *
	 * @param   int     $row        the row number
	 * @param   string  $text       the section header text
	 * @param   bool    $break      whether or not a break should be displayed
	 * @param   array   $comments   an array of tips with title and/or text
	 * @param   bool    $altHeight  whether or not the alternative section height is to be used
	 *
	 * @return void
	 */
	private function addSectionHeader($row, $text, $break = false, $comments = [], $altHeight = false)
	{
		$activeSheet = $this->spreadSheet->getActiveSheet();
		$height      = $altHeight ? $this->heights['sum'] : $this->heights['sectionHead'];
		$activeSheet->getRowDimension($row)->setRowHeight($height);
		$activeSheet->mergeCells("B$row:M$row");
		$activeSheet->getStyle("B$row:M$row")->applyFromArray([
			'borders' => $this->borders['header'],
			'fill'    => $this->fills['header'],
			'font'    => ['bold' => true]
		]);
		$activeSheet->getStyle("B$row")->getAlignment()
			->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$activeSheet->setCellValue("B$row", $text);

		if (!empty($comments))
		{
			foreach ($comments as $comment)
			{
				$this->addComment("B$row", $comment);
			}
		}

		if ($break)
		{
			$activeSheet->getRowDimension(++$row)->setRowHeight($this->heights['sectionSpacer']);
		}
	}

	/**
	 * Adds a row summing section values
	 *
	 * @param   int     $row      the row where the sum will be added
	 * @param   string  $section  the section being summed (used for the label)
	 * @param   array   $ranges   the row ranges to be summed
	 *
	 * @return void
	 */
	private function addSumRow($row, $section, $ranges = [])
	{
		$activeSheet = $this->spreadSheet->getActiveSheet();
		$activeSheet->getStyle("L$row")->applyFromArray([
			'borders' => $this->borders['header'],
			'fill'    => $this->fills['header']
		]);
		$activeSheet->getStyle("L$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$activeSheet->setCellValue("L$row", "Summe $section:");
		$activeSheet->getStyle("L$row")->getFont()->setBold(true);

		$activeSheet->getStyle("M$row")->applyFromArray([
			'borders' => $this->borders['header'],
			'fill'    => $this->fills['index']
		]);

		if (count($ranges) === 1)
		{
			$formula = "=SUM(M{$ranges[0]['start']}:M{$ranges[0]['end']})";
		}
		else
		{
			$sums = [];
			foreach ($ranges as $range)
			{
				$sums[] = "SUM(M{$range['start']}:M{$range['end']})";
			}
			$formula = '=SUM(' . implode(',', $sums) . ')';
		}

		$activeSheet->setCellValue("M$row", $formula);
	}

	/**
	 * Creates a row evaluating the valuation of a type and quantity of supervisions
	 *
	 * @param   int    $row       the row number
	 * @param   array  $category  an array containing the category text and it's calculation weight
	 *
	 * @return void
	 */
	private function addSupervisionRow($row, $category)
	{
		$title   = "LVVO vom 10.9.2013, §2 (5):";
		$text    = "„Die Betreuung von Abschlussarbeiten und vergleichbaren Studienarbeiten kann durch die ";
		$text    .= "Hochschule unter Berücksichtigung des notwendigen Aufwandes bis zu einem Umfang von zwei ";
		$text    .= "Lehrveranstaltungsstunden auf die Lehrverpflichtung angerechnet werden;…“";
		$comment = ['title' => $title, 'text' => $text];

		$columnText = "LVVO § 2 (5)";

		$activeSheet = $this->spreadSheet->getActiveSheet();
		$activeSheet->getStyle("B$row")->applyFromArray([
			'borders' => $this->borders['cell']
		]);
		$activeSheet->setCellValue("B$row", $columnText);
		$this->addComment("B$row", $comment);

		$activeSheet->mergeCells("C$row:F$row");
		$activeSheet->getStyle("C$row:F$row")->applyFromArray([
			'borders' => $this->borders['cell']
		]);
		$activeSheet->setCellValue("C$row", $category['text']);

		$activeSheet->mergeCells("G$row:J$row");
		$activeSheet->getStyle("G$row:J$row")->applyFromArray([
			'borders' => $this->borders['cell']
		]);
		$activeSheet->getStyle("G$row")->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
		$activeSheet->setCellValue("G$row", $category['weight']);

		$activeSheet->mergeCells("K$row:L$row");
		$activeSheet->getStyle("K$row:L$row")->applyFromArray([
			'borders' => $this->borders['cell']
		]);
		$activeSheet->getStyle("K$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);

		$activeSheet->getStyle("M$row")->applyFromArray([
			'borders' => $this->borders['cell']
		]);
		$activeSheet->getStyle("M$row")->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
		$activeSheet->setCellValue("M$row", '=IF(K' . $row . '<>"",G' . $row . '*K' . $row . ',0)');
	}

	/**
	 * Creates an instructions sheet
	 *
	 * @return void
	 */
	private function addWorkSheet()
	{
		$this->spreadSheet->createSheet();
		$this->spreadSheet->setActiveSheetIndex(1);
		$activeSheet = $this->spreadSheet->getActiveSheet();

		$activeSheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
		$activeSheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
		$activeSheet->getPageSetup()->setFitToPage(true);

		$activeSheet->setTitle('B-Bogen');
		$activeSheet->setShowGridlines(false);
		$activeSheet->getColumnDimension('A')->setWidth(2);
		$activeSheet->getColumnDimension('B')->setWidth(18);
		$activeSheet->getColumnDimension('C')->setWidth(10.71);
		$activeSheet->getColumnDimension('D')->setWidth(10.71);
		$activeSheet->getColumnDimension('E')->setWidth(9.71);
		$activeSheet->getColumnDimension('F')->setWidth(10.71);
		$activeSheet->getColumnDimension('G')->setWidth(11.71);
		$activeSheet->getColumnDimension('H')->setWidth(10.86);
		$activeSheet->getColumnDimension('I')->setWidth(10.71);
		$activeSheet->getColumnDimension('J')->setWidth(10.71);
		$activeSheet->getColumnDimension('K')->setWidth(11.43);
		$activeSheet->getColumnDimension('L')->setWidth(13.29);
		$activeSheet->getColumnDimension('M')->setWidth(14.29);
		$activeSheet->getColumnDimension('N')->setWidth(2.29);

		$activeSheet->getRowDimension('1')->setRowHeight('66');

		$this->addLogo('B1', 60, 10);

		$activeSheet->getRowDimension('2')->setRowHeight('22.5');
		$activeSheet->mergeCells("B2:M2");
		$activeSheet->getStyle("B2:M2")->applyFromArray([
			'borders' => $this->borders['header'],
			'fill'    => $this->fills['header']
		]);

		$headerText = 'Bericht über die Erfüllung der Lehrverpflichtung gemäß';
		$headerText .= '§ 4 (5) LVVO (Version 1.4; Stand 07.02.2018)';
		$activeSheet->setCellValue('B2', $headerText);
		$cellStyle = $activeSheet->getStyle('B2');
		$cellStyle->getFont()->setSize('14');
		$cellStyle->getFont()->setBold(true);
		$cellStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$cellStyle->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

		$activeSheet->getRowDimension('3')->setRowHeight($this->heights['sectionSpacer']);

		$activeSheet->getRowDimension('4')->setRowHeight($this->heights['basicField']);
		$this->addBasicField(4, 'Fachbereich');
		$activeSheet->getRowDimension('5')->setRowHeight($this->heights['spacer']);

		$activeSheet->getRowDimension('6')->setRowHeight($this->heights['basicField']);
		$this->addBasicField(6, 'Semester');
		$activeSheet->getRowDimension('7')->setRowHeight($this->heights['spacer']);

		$activeSheet->getRowDimension('8')->setRowHeight($this->heights['basicField']);
		$this->addBasicField(8, 'Name');
		$activeSheet->getRowDimension('9')->setRowHeight($this->heights['spacer']);

		$activeSheet->getRowDimension('10')->setRowHeight($this->heights['basicField']);
		$this->addBasicField(10, 'Vorname');
		$activeSheet->getRowDimension('11')->setRowHeight($this->heights['spacer']);

		$this->addWorkSheetInstructions();

		$row = 12;
		$this->addSectionA($row);
		$this->addSectionB($row);
		$this->addSectionC($row);
		$sectionDText = "D. Gemeldetes Gesamtdeputat (A + B + C) für das Semester";
		$this->addSectionHeader(61, $sectionDText, false, [], true);
		$this->addSectionHeader(63, "E. Deputatsübertrag aus den Vorsemestern");
		$this->addSectionHeader(67, "F. Soll-Deputat");
		$sectionGText = "G. Saldo zum Ende des Semesters und Deputatsübertrag für Folgesemester";
		$this->addSectionHeader(71, $sectionGText, false, [], true);
		$this->addSectionHeader(73, "H. Sonstige Mitteilungen");
	}

	/**
	 * Adds Instructions to the worksheet.
	 *
	 * @return void
	 */
	private function addWorkSheetInstructions()
	{
		$activeSheet = $this->spreadSheet->getActiveSheet();

		$activeSheet->mergeCells("G4:K10");
		$color = '9C132E';
		$activeSheet->getStyle("G4:K10")->applyFromArray([
			'borders' => [
				'left'   => [
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => ['rgb' => $color]
				],
				'right'  => [
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => ['rgb' => $color]
				],
				'bottom' => [
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => ['rgb' => $color]
				],
				'top'    => [
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => ['rgb' => $color]
				]
			],
			'font'    => [
				'bold'  => true,
				'color' => ['rgb' => $color]
			]
		]);

		$alignment = $activeSheet->getStyle("G4")->getAlignment();
		$alignment->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$alignment->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$alignment->setWrapText(true);

		$text = 'Die Tabelle soll in Excel ausgefüllt werden. Durch Kontakt des Cursors mit der kleinen roten ';
		$text .= 'Markierung in einem entsprechenden Feld öffntet sich ein Infofeld und Sie erhalten weiterführende ';
		$text .= 'Informationen.';
		$activeSheet->setCellValue('G4', $text);
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
		$docTitle = ApplicationHelper::stringURLSafe(Languages::_('THM_ORGANIZER_DEPUTAT') . '_' . date('Ymd'));
		header("Content-Disposition: attachment;filename=$docTitle.xlsx");
		$objWriter->save('php://output');
		exit();
	}

	/**
	 * Sets often used style elements, specifically borders and fills.
	 *
	 * @return void
	 */
	private function setStyles()
	{
		$this->borders = [
			'data'   => [
				'left'   => [
					'style' => PHPExcel_Style_Border::BORDER_MEDIUM
				],
				'right'  => [
					'style' => PHPExcel_Style_Border::BORDER_MEDIUM
				],
				'bottom' => [
					'style' => PHPExcel_Style_Border::BORDER_THIN
				],
				'top'    => [
					'style' => PHPExcel_Style_Border::BORDER_THIN
				]
			],
			'cell'   => [
				'left'   => [
					'style' => PHPExcel_Style_Border::BORDER_THIN
				],
				'right'  => [
					'style' => PHPExcel_Style_Border::BORDER_THIN
				],
				'bottom' => [
					'style' => PHPExcel_Style_Border::BORDER_THIN
				],
				'top'    => [
					'style' => PHPExcel_Style_Border::BORDER_THIN
				]
			],
			'header' => [
				'left'   => [
					'style' => PHPExcel_Style_Border::BORDER_MEDIUM
				],
				'right'  => [
					'style' => PHPExcel_Style_Border::BORDER_MEDIUM
				],
				'bottom' => [
					'style' => PHPExcel_Style_Border::BORDER_MEDIUM
				],
				'top'    => [
					'style' => PHPExcel_Style_Border::BORDER_MEDIUM
				]
			]
		];

		$this->fills = [
			'header' => [
				'type'  => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => ['rgb' => '80BA24']
			],
			'index'  => [
				'type'  => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => ['rgb' => 'FFFF00']
			],
			'data'   => [
				'type'  => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => ['rgb' => 'DFEEC8']
			]
		];

		$this->heights = [
			'basicField'    => '18.75',
			'sectionHead'   => '13.5',
			'sectionSpacer' => '8.25',
			'spacer'        => '6.25',
			'sum'           => '18.75'
		];
	}
}
