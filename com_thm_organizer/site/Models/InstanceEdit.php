<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Organizer\Helpers\Instances;

/**
 * Class loads a form for editing instance data.
 */
class InstanceEdit extends EditModel
{
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key
	 *
	 * @return mixed Object on success, false on failure
	 */
	public function getItem($pk = null)
	{
		$this->item = parent::getItem($pk);

		$instance = ['instanceID' => $this->item->id];

		Instances::setPersons($instance, ['delta' => '']);

		$this->item->resources = $instance['resources'];

		return $this->item;

	}
}