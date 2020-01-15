<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Joomla\CMS\Table\Table;
use Organizer\Helpers;
use Organizer\Tables\Courses as CoursesTable;

/**
 * Class loads forms for managing basic course attributes.
 */
class CourseEdit extends EditModel
{
	/**
	 * Provides a strict access check which can be overwritten by extending classes.
	 *
	 * @return bool  true if the user can access the view, otherwise false
	 */
	protected function allowEdit()
	{
		$courseID = Helpers\Input::getSelectedID();

		return Helpers\Can::manage('course', $courseID);
	}

	/**
	 * Method to get the form
	 *
	 * @param   array  $data      Data         (default: array)
	 * @param   bool   $loadData  Load data  (default: true)
	 *
	 * @return mixed Form object on success, False on error.
	 * @throws Exception => unauthorized access
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function getForm($data = [], $loadData = true)
	{
		$form = parent::getForm($data, $loadData);

		if (empty($this->item->id))
		{
			$form->removeField('campusID');
		}

		return empty($form) ? false : $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return mixed    Object on success, false on failure.
	 * @throws Exception => unauthorized access
	 */
	public function getItem($pk = null)
	{
		$this->item = parent::getItem($pk);

		if (empty($this->item->id))
		{
			$this->item->name   = Helpers\Languages::_('THM_ORGANIZER_NONE');
			$this->item->termID = Helpers\Terms::getNextID();
		}
		else
		{
			$this->item->name = Helpers\Courses::getName($this->item->id);
		}

		return $this->item;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return Table A Table object
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function getTable($name = '', $prefix = '', $options = [])
	{
		return new CoursesTable;
	}
}
