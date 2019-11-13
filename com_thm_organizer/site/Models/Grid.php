<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Organizer\Helpers\Access;
use Organizer\Helpers\Input;

/**
 * Class which manages stored grid data.
 */
class Grid extends BaseModel
{
	/**
	 * Attempts to save the resource.
	 *
	 * @param   array  $data  form data which has been preprocessed by inheriting classes.
	 *
	 * @return mixed int id of the resource on success, otherwise boolean false
	 * @throws Exception => unauthorized access
	 */
	public function save($data = [])
	{
		if (!Access::isAdmin())
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		$data = empty($data) ? Input::getFormItems()->toArray() : $data;

		// Save grids in json by foreach because the index is not numeric
		$periods = [];
		$index   = 1;
		if (!empty($data['grid']))
		{
			foreach ($data['grid'] as $row)
			{
				$periods[$index] = $row;
				++$index;
			}
		}

		$grid         = ['periods' => $periods, 'startDay' => $data['startDay'], 'endDay' => $data['endDay']];
		$data['grid'] = json_encode($grid, JSON_UNESCAPED_UNICODE);

		$table = $this->getTable();

		return $table->save($data) ? $table->id : false;
	}
}
