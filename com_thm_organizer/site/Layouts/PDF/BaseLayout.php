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
	protected $dataFont = ['helvetica', '', 8];

	protected $filename;

	protected $headerFont = ['helvetica', '', 10];

	/**
	 * Performs initial construction of the TCPDF Object.
	 *
	 * @param   string  $orientation  page orientation; possible values (case insensitive):
	 *                                p/portrait (default), l/landscape, '' (automatic)
	 * @param   string  $unit         unit of measure; possible values:
	 *                                mm - millimeter (default), cm - centimeter, in - inch, pt - point (~0.35 mm)
	 * @param   mixed   $format       page format; possible values: string - common format name, array - parameters
	 *
	 * @see \TCPDF_STATIC::getPageSizeFromFormat(), setPageFormat()
	 */
	public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
	{
		parent::__construct($orientation, $unit, $format);
		$this->SetAuthor(Factory::getUser()->name);
		$this->SetCreator('THM Organizer');
		$this->SetHeaderFont($this->headerFont);
		$this->setImageScale(1.25);
		$this->SetFooterFont($this->dataFont);
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
		parent::SetAutoPageBreak(true, $bottom);
		parent::setFooterMargin($footer);
		parent::setHeaderMargin($header);
		parent::SetMargins($left, $top, $right);
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
	 * Create a new TCPDF document and format the header with course information
	 *
	 * @return void
	 */
	protected function setHeader()
	{
		$header           = $this->course['name'];
		$location         = empty($this->course['place']) ? '' : "{$this->course['place']}, ";
		$dates            = "{$this->course['start']} - {$this->course['end']}";
		$participants     = Languages::_('THM_ORGANIZER_PARTICIPANTS');
		$participantCount = count($this->course['participants']);
		$subHeader        = "$location$dates\n$participants: $participantCount";

		$this->SetHeaderData('thm_logo.png', '50', $header, $subHeader);
		$this->SetFont('', '', 10);
	}
}
