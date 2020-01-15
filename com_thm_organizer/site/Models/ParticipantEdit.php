<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Organizer\Helpers\Can;
use Organizer\Helpers\Input;
use Organizer\Tables\Participants as ParticipantsTable;

/**
 * Class loads a form for editing participant data.
 */
class ParticipantEdit extends EditModel
{
	/**
	 * Checks for user authorization to access the view
	 *
	 * @return bool  true if the user can access the view, otherwise false
	 */
	protected function allowEdit()
	{
		return Can::edit('participant', $this->item->id);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $participantID  The id of the primary key.
	 *
	 * @return mixed    Object on success, false on failure.
	 * @throws Exception => unauthorized access
	 */
	public function getItem($participantID = null)
	{
		$userID               = Factory::getUser()->id;
		$participantID        = empty($participantID) ? Input::getSelectedID($userID) : $participantID;
		$this->item           = parent::getItem($participantID);
		$this->item->referrer = Input::getInput()->server->getString('HTTP_REFERER');
		$this->item->id       = $this->item->id ? $this->item->id : Factory::getUser()->id;

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
		return new ParticipantsTable;
	}
}
