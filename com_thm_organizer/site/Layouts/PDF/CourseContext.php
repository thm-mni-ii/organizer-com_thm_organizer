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

use Organizer\Helpers;
use Organizer\Tables;

/**
 * Base PDF export class used for the generation of various course exports.
 */
trait CourseContext
{
	/**
	 * The campus where the course takes place
	 * @var string
	 */
	protected $campus;

	/**
	 * The dates as displayed in the generated document.
	 * @var string
	 */
	protected $dates;

	/**
	 * The course end date
	 * @var string
	 */
	protected $endDate;

	/**
	 * The fee required for participation in the course
	 * @var int
	 */
	protected $fee;

	/**
	 * The name of the course
	 * @var string
	 */
	protected $name;

	/**
	 * The course start date
	 * @var string
	 */
	protected $startDate;

	/**
	 * The name of the term
	 * @var string
	 */
	protected $term;

	/**
	 * THM_OrganizerTemplateCourse_List_PDF constructor.
	 *
	 * @param   int  $courseID  the id of the course providing context for the generated document
	 *
	 * @return void sets the course property
	 */
	protected function setCourse($courseID)
	{
		$dates           = Helpers\Courses::getDates($courseID);
		$this->endDate   = Helpers\Dates::formatDate($dates['endDate']);
		$this->startDate = Helpers\Dates::formatDate($dates['startDate']);
		$this->dates     = $this->startDate === $this->endDate ? $this->startDate : "$this->startDate - $this->endDate";


		$this->name = Helpers\Courses::getName($courseID);

		$table = new Tables\Courses;
		$table->load($courseID);

		$this->campus = Helpers\Campuses::getName($table->campusID);
		$this->fee    = $table->fee;

		$termID     = $table->termID;
		$this->term = Helpers\Courses::isPreparatory($courseID) ?
			Helpers\Terms::getName(Helpers\Terms::getNextID($termID)) : Helpers\Terms::getName($termID);
	}

	/**
	 * Create a new TCPDF document and format the header with course information
	 *
	 * @return void
	 */
	protected function setHeader()
	{
		$header    = "$this->name $this->term";
		$subHeader = "{$this->campus} {$this->dates}";

		$this->SetHeaderData('thm_logo.png', '50', $header, $subHeader);
	}
}
