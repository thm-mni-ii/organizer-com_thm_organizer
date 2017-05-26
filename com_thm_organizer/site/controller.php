<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');
DEFINE('REGISTER', 1);
DEFINE('DEREGISTER', 2);
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/**
 * Site main controller
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerController extends JControllerLegacy
{
	/**
	 * Method to display
	 *
	 * @param bool  $cachable  If true, the view output will be cached
	 * @param array $urlparams An array of safe url parameters and their variable types, for valid values
	 *                         see {@link JFilterInput::clean()}.
	 *
	 * @return    void
	 */
	public function display($cachable = false, $urlparams = false)
	{
		parent::display($cachable, $urlparams);
	}

	/**
	 * Sends an circular email to all course participants
	 *
	 * @return void
	 */
	public function circular()
	{
		$lang = THM_OrganizerHelperLanguage::getLanguage();
		$app = JFactory::getApplication();

		$data = JFactory::getApplication()->input->get('jform', array(), 'array');
		$includeWaitList = $data["includeWaitList"] === "1" ? true : false;

		$success = $this->getModel('participant')->circular($data, $includeWaitList);

		if (empty($success))
		{
			$msgText  = $lang->_('COM_THM_ORGANIZER_MESSAGE_MAIL_SEND_FAIL');
			$msgType = 'error';
		}
		else
		{
			$msgText  = $lang->_('COM_THM_ORGANIZER_MESSAGE_MAIL_SEND_SUCCESS');
			$msgType = 'success';
		}

		$app->enqueueMessage($msgText, $msgType);
		$app->input->set('view', "course_manager");
		$app->input->set('lessonID', $app->input->get("lessonID"));
		parent::display();
	}

	/**
	 * Clears all Participants from a specific course
	 *
	 * @return void
	 */
	public function clear()
	{
		$lang  = THM_OrganizerHelperLanguage::getLanguage();

		$input = JFactory::getApplication()->input;
		$lessonID = $input->getString('lessonID', 0);

		$success = false;

		if (!empty($lessonID))
		{
			$langTag = THM_OrganizerHelperLanguage::getShortTag();
			$success = parent::getModel('participant')->clearParticipants($lessonID);
			$input->set("languageTag", $langTag);

			$input->set('view', "course_manager");
			$input->set('lessonID', $lessonID);
		}
		else
		{
			$input->set('view', "course_list");
		}

		if ($success)
		{
			$msgText = $lang->_("COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS");
			$msgType = 'success';
		}
		else
		{
			$msgText = $lang->_("COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL");
			$msgType = 'error';
		}

		JFactory::getApplication()->enqueueMessage($msgText, $msgType);
		parent::display();
	}

	/**
	 * Check if user information is present if thats the case only sign user in or out of course
	 * then redirect to course list view otherwise redirect to user registration view
	 *
	 * @return void
	 */
	public function register()
	{
		$lang = THM_OrganizerHelperLanguage::getLanguage();

		$app = JFactory::getApplication();
		$data =  parent::getModel('participant_edit')->getItem();

		$lessonID = JFactory::getApplication()->input->getString('lessonID', '');

		if (empty($data->id) OR empty($lessonID))
		{
			if (empty($lessonID))
			{
				$app->input->set('view', "course_list");
			}
			else
			{
				$app->input->set('view', "participant_edit");
				$app->input->set('lessonID', $lessonID);
			}

			parent::display();
			return;
		}

		if (THM_OrganizerHelperPrep_Course::isRegistrationOpen())
		{
			$regState = THM_OrganizerHelperPrep_Course::getRegistrationState();

			$action = $regState ? DEREGISTER : REGISTER;

			$model = parent::getModel('participant');

			if (empty($model))
			{
				return;
			}

			$langTag = THM_OrganizerHelperLanguage::getShortTag();
			$return = $model->applySignAction((array) $data, $action, $lessonID);
			$app->input->set("languageTag", $langTag);

			if ($return)
			{
				if ($regState)
				{
					$msgText = $lang->_("COM_THM_ORGANIZER_DEREGISTRATION_SUCCESS");
				}
				else
				{
					$status = THM_OrganizerHelperPrep_Course::getRegistrationState()["status"] ?
						"COM_THM_ORGANIZER_PREP_COURSE_STATE_REGISTERED" :
						"COM_THM_ORGANIZER_PREP_COURSE_STATE_WAIT_LIST";
					$msgText = THM_OrganizerHelperLanguage::sprintf("COM_THM_ORGANIZER_REGISTRATION_SUCCESS", $lang->_($status));
				}

				$msgType = 'success';
			}
			else
			{
				$msgText = $lang->_("COM_THM_ORGANIZER_STATUS_FAILURE");
				$msgType = 'error';
			}
		}
		else
		{
			$msgText = $lang->_("COM_THM_ORGANIZER_PREP_COURSE_NOTIFICATION_DEADLINE_EXCEEDED");
			$msgType = 'error';
		}

		$app->enqueueMessage($msgText, $msgType);
		$app->input->set('view', "course_list");
		parent::display();
	}

	/**
	 * Save user information from form and if course id defined sign in or out of course
	 * then redirect to course list view
	 *
	 * @return void
	 */
	public function changeStatus()
	{
		$lang = THM_OrganizerHelperLanguage::getLanguage();
		$app = JFactory::getApplication();

		$langTag = THM_OrganizerHelperLanguage::getShortTag();
		$success = $this->getModel('participant')->changeStatus();
		$app->input->set('languageTag', $langTag);

		if (empty($success))
		{
			$msgText  = $lang->_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
			$msgType = 'error';
		}
		else
		{
			$msgText  = $lang->_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
			$msgType = 'success';
		}

		$app->enqueueMessage($msgText, $msgType);
		$app->input->set('view', "course_manager");
		$app->input->set('lessonID', $app->input->get("lessonID"));

		parent::display();
	}

	/**
	 * Save user information from form and if course id defined sign in or out of course
	 * then redirect to course list view
	 *
	 * @return void
	 */
	public function save()
	{
		$resource = explode('.', JFactory::getApplication()->input->get('task', ''))[0];

		$data = JFactory::getApplication()->input->get('jform', array(), 'array');
		$lang = THM_OrganizerHelperLanguage::getLanguage();
		$app = JFactory::getApplication();

		$model = $this->getModel($resource);

		if (!empty($model))
		{
			$langTag = THM_OrganizerHelperLanguage::getShortTag();
			$success = $model->save($data);
			$app->input->set("languageTag", $langTag);

			if ($success)
			{
				$app->enqueueMessage($lang->_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS'), 'success');
			}
			else
			{
				$app->enqueueMessage($lang->_("COM_THM_ORGANIZER_STATUS_FAILURE"), 'error');
			}
		}
		else
		{
			$app->enqueueMessage($lang->_("COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL"), "error");
		}

		$redirect = $app->input->get("redirect", "");

		if (empty($redirect))
		{
			$app->redirect("index.php");
		}

		$app->input->set('view', $redirect);
		parent::display();
	}
}
