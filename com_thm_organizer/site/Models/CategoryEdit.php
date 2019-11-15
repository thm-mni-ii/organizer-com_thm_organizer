<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Joomla\CMS\Table\Table;
use Organizer\Helpers\Categories;
use Organizer\Tables\Categories as CategoriesTable;

/**
 * Class loads a form for editing category data.
 */
class CategoryEdit extends EditModel
{
	protected $deptResource = 'program';

	/**
	 * Checks for user authorization to access the view.
	 *
	 * @return bool  true if the user can access the edit view, otherwise false
	 */
	public function allowEdit()
	{
		if (empty($this->item->id))
		{
			return false;
		}

		return Categories::allowEdit([$this->item->id]);
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
		$this->item               = parent::getItem($pk);
		$this->item->departmentID = Categories::getDepartmentIDs($this->item->id);

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
		return new CategoriesTable;
	}
}
