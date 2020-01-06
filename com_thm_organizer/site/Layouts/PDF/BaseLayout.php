<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Layouts\PDF;

define('K_PATH_IMAGES', JPATH_ROOT . '/components/com_thm_organizer/images/');

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use TCPDF;

/**
 * Base PDF export class used for the generation of various course exports.
 */
abstract class BaseLayout extends TCPDF
{
	// Alignment & Borders
	const ALL = 1,
		BOTTOM = 'B',
		CENTER = 'C',
		GINSBERG = 'RB',
		HORIZONTAL = 'BT',
		JUSTIFY = 'J',
		LEFT = 'L',
		NONE = 0,
		RIGHT = 'R',
		TOP = 'T',
		VERTICAL = 'LR';

	// Font Families
	const COURIER = 'courier', CURRENT_FAMILY = '', HELVETICA = 'helvetica', TIMES = 'times';

	const CURRENT_SIZE = null;

	// Font Styles
	const BOLD = 'B',
		BOLD_ITALIC = 'BI',
		BOLD_UNDERLINE = 'BU',
		ITALIC = 'I',
		OVERLINE = 'O',
		REGULAR = '',
		STRIKE_THROUGH = 'D',
		UNDERLINE = 'U';

	// Orientation
	const LANDSCAPE = 'l', PORTRAIT = 'p';

	// UOM point (~0.35 mm)
	const CENTIMETER = 'cm', INCH = 'in', MILLIMETER = 'mm', POINT = 'pt';

	protected $border = ['width' => '.1', 'color' => 220];

	protected $dataFont = ['helvetica', '', 8];

	protected $filename;

	protected $headerFont = ['helvetica', '', 10];

	/**
	 * Performs initial construction of the TCPDF Object.
	 *
	 * @param   string  $orientation  page orientation
	 * @param   string  $unit         unit of measure
	 * @param   mixed   $format       page format; possible values: string - common format name, array - parameters
	 *
	 * @see \TCPDF_STATIC::getPageSizeFromFormat(), setPageFormat()
	 */
	public function __construct($orientation = self::PORTRAIT, $unit = 'mm', $format = 'A4')
	{
		parent::__construct($orientation, $unit, $format);
		$this->SetAuthor(Factory::getUser()->name);
		$this->SetCreator('THM Organizer');
		$this->SetCellPaddings(1, 1.5, 1, 1.5);
		$this->SetHeaderFont($this->headerFont);
		$this->setImageScale(1.25);
		$this->SetFooterFont($this->dataFont);
	}

	/**
	 * Changes the current font settings used for rendering.
	 *
	 * @param   string  $style   the font style abbreviation
	 * @param   int     $size    the font size, document default is 12
	 * @param   string  $family  the font family name
	 *
	 * @return void sets the font attribute values for use in rendering until set otherwise
	 */
	public function changeFont($style = self::REGULAR, $size = self::CURRENT_SIZE, $family = self::CURRENT_FAMILY)
	{
		$this->SetFont($family, $style, $size);
	}

	/**
	 * Defines the abscissa and ordinate of the current position.
	 * If the passed values are negative, they are relative respectively to the right and bottom of the page.
	 *
	 * @param   int  $horizontal  the horizontal coordinate
	 * @param   int  $vertical    the vertical coordinate
	 *
	 * @return void repositions the documents point of reference
	 */
	public function changePosition($horizontal, $vertical)
	{
		$this->SetXY($horizontal, $vertical);
	}

	/**
	 * Changes the current font size used for rendering.
	 *
	 * @param   int  $size  the font size
	 *
	 * @return void sets the font size value for use in rendering until set otherwise
	 */
	public function changeSize($size)
	{
		$this->SetFontSize($size);
	}

	/**
	 * Adds the contents of the document to it.
	 *
	 * @param   mixed  $data  the data used to fill the contents of the document
	 *
	 * @return void modifies the document
	 */
	abstract public function fill($data);

	/**
	 * Defines the left, top and right margins.
	 *
	 * @param   int  $left   th left margin.
	 * @param   int  $top    the top margin.
	 * @param   int  $right  the right margin (defaults to left value)
	 *
	 * @public
	 * @since 1.0
	 * @see   SetAutoPageBreak(), SetFooterMargin(), setHeaderMargin(), SetLeftMargin(), SetRightMargin(), SetTopMargin()
	 */
	public function margins($left = 15, $top = 27, $right = -1, $bottom = 25, $header = 5, $footer = 10)
	{
		$this->SetAutoPageBreak(true, $bottom);
		$this->setFooterMargin($footer);
		$this->setHeaderMargin($header);
		$this->SetMargins($left, $top, $right);
	}

	/**
	 * Renders the document.
	 *
	 * @return void renders the document and closes the application
	 */
	public function render()
	{
		$this->Output($this->filename, 'I');
		ob_flush();
	}

	/**
	 * Renders a cell. Borders
	 *
	 * @param   int     $width   the cell width
	 * @param   int     $height  the cell height
	 * @param   string  $text    the cell text
	 * @param   string  $hAlign  the cell's horizontal alignment
	 * @param   mixed   $border  number 0/1: none/all,
	 *                           string B/L/R/T: corresponding side
	 *                           array border settings coded by side
	 * @param   bool    $fill    true if the cell should render a background color, otherwise false
	 * @param   string  $vAlign  the cell's vertical alignment
	 * @param   mixed   $link    URL or identifier returned by AddLink().
	 *
	 * @return void renders the cell
	 * @see   AddLink()
	 */
	protected function renderCell(
		$width,
		$height,
		$text,
		$hAlign = self::LEFT,
		$border = self::NONE,
		$fill = false,
		$vAlign = self::CENTER,
		$link = ''
	) {
		$this->Cell($width, $height, $text, $border, 0, $hAlign, $fill, $link, 0, false, self::TOP, $vAlign);
	}

	protected function renderMultiCell($width, $height, $text)
	{
		// width        height      text            border      align           fill    ln      x       y       reset   stretch     ishtml      autopad     maxh    valign      fitcell
		// $this...,    5,          $value,         $border,    self::LEFT,     0,      0
		// 80,          5,          $headerLine,    0,          self::CENTER,   0,      2
		// 80,          5,          text,           0,          self::CENTER
		// pdw,         $height,    '',             'RB',       self::CENTER,   0,      0
		/*$this->MultiCell(
			$width,
			$height,
			$text,
			$border, //0 - used
			$hAlign, //center - used
			$fill, //false - not used atm
			0 //never
		);*/
		//$w, $h, $txt, $border=0, $align='J', $fill=false, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=false
	}

	/**
	 * Sets the document title and file name properties. File name defaults to a safe revision of the document title.
	 *
	 * @param   string  $documentTitle  the document title
	 * @param   string  $fileName       the file name
	 */
	protected function setNames($documentTitle, $fileName = '')
	{
		$this->SetTitle($documentTitle);

		$fileName = $fileName ? $fileName : $documentTitle;
		$fileName = str_replace(' ', '', $fileName);

		$this->filename = ApplicationHelper::stringURLSafe($fileName) . '.pdf';
	}

	/**
	 * Enables display of the document header and footer.
	 *
	 * @param   bool  $display  true if the document should display a header and footer, otherwise false
	 *
	 * @see SetPrintFooter(), SetPrintHeader()
	 */
	protected function showPrintOverhead($display)
	{
		$this->SetPrintHeader($display);
		$this->SetPrintFooter($display);
	}
}
