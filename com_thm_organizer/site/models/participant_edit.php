<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/course.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class provides methods for getting information about course participants
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelParticipant_Edit extends JModelForm
{
	/**
	 * Loads user registration information from the database
	 *
	 * @return  object  filled with user registration data on success, otherwise empty
	 */
	public function getItem()
	{
		$query  = $this->_db->getQuery(true);
		$userID = JFactory::getUser()->id;

		$query->select('u.id as userID ,ud.id, ud.address, ud.zip_code, ud.city, ud.programID, ud.forename, ud.surname');
		$query->from('#__users AS u');
		$query->leftJoin('#__thm_organizer_user_data AS ud ON ud.userID = u.id');
		$query->where("u.id = '$userID'");

		$this->_db->setQuery($query);

		try
		{
			$item = $this->_db->loadObject();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(THM_OrganizerHelperLanguage::getLanguage()->_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return new stdClass;
		}

		return empty($item->userID) ? new stdClass : $item;
	}

	/**
	 * Method to get the form
	 *
	 * @param array $data     Data         (default: array)
	 * @param bool  $loadData Load data  (default: true)
	 *
	 * @return  mixed  JForm object on success, False on error.
	 */
	public function getForm($data = [], $loadData = true)
	{
		$form = $this->loadForm(
			"com_thm_organizer.participant_edit",
			"participant_edit",
			['control' => 'jform', 'load_data' => $loadData]
		);

		return !empty($form) ? $form : false;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  object  loaded object
	 */
	protected function loadFormData()
	{
		return $this->getItem();
	}
}
