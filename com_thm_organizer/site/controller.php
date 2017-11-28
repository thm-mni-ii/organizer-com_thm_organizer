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
	 * Sends an circular email to all course participants
	 *
	 * @return void
	 */
	public function circular()
	{
		$lang = THM_OrganizerHelperLanguage::getLanguage();
		$app  = JFactory::getApplication();

		$data            = JFactory::getApplication()->input->get('jform', [], 'array');
		$includeWaitList = $data["includeWaitList"] === "1" ? true : false;

		$success = $this->getModel('participant')->circular($data, $includeWaitList);

		if (empty($success))
		{
			$msgText = $lang->_('COM_THM_ORGANIZER_MESSAGE_MAIL_SEND_FAIL');
			$msgType = 'error';
		}
		else
		{
			$msgText = $lang->_('COM_THM_ORGANIZER_MESSAGE_MAIL_SEND_SUCCESS');
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
		$lang = THM_OrganizerHelperLanguage::getLanguage();

		$input    = JFactory::getApplication()->input;
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
	 * Builds a the base url for redirection
	 *
	 * @return string the root url to redirect to
	 */
	private function getRedirectBase()
	{
		$app    = JFactory::getApplication();
		$url    = JUri::base();
		$menuID = $app->input->getInt('Itemid');

		if (!empty($menuID))
		{
			$url .= $app->getMenu()->getItem($menuID)->route . '?';
		}
		else
		{
			$url .= '?option=com_thm_organizer&';
		}

		if (!empty($app->input->getInt('languageTag')))
		{
			$url .= '&languageTag' . THM_OrganizerHelperLanguage::getShortTag();
		}

		return $url;
	}

	/**
	 * Check if a course was selected and is valid. Check if the required participant data exists, if not redirect to the
	 * participant edit view. Otherwise register/deregister the user from the course.
	 *
	 * @return void
	 */
	public function register()
	{
		$app      = JFactory::getApplication();
		$input    = $app->input;
		$lessonID = $input->getInt('lessonID');

		// No chosen lesson => should not occur
		if (empty($lessonID) OR !THM_OrganizerHelperCourse::isRegistrationOpen())
		{
			$input->set('view', "course_list");
			parent::display();

			return;
		}

		$lang               = THM_OrganizerHelperLanguage::getLanguage();
		$url                = $this->getRedirectBase();
		$formData           = $input->get('jform', [], 'array');
		$participantModel   = $this->getModel('participant');
		$participantEditURL = "{$url}view=participant_edit&lessonID=$lessonID";

		if (!empty($formData))
		{
			if (!empty($formData['id']) AND $formData['id'] == JFactory::getUser()->id)
			{
				$participantSaved = $participantModel->save();

				if (empty($participantSaved))
				{
					$app->enqueueMessage($lang->_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL'), 'error');
					$app->redirect(JRoute::_($participantEditURL, false));

					return;
				}
			}
			else
			{
				$app->enqueueMessage($lang->_('COM_THM_ORGANIZER_MESSAGE_NO_ACCESS_ACTION'), 'error');
				$app->redirect(JRoute::_($url, false));

				return;
			}
		}

		// Always based on the current user, no further validation required.
		$participant = parent::getModel('participant_edit')->getItem();

		// Participant entry does not yet exist => create one
		if (empty($participant->id))
		{
			$app->redirect(JRoute::_($participantEditURL));

			return;
		}

		$type = 'error';

		$userState = THM_OrganizerHelperCourse::getUserState();
		$action    = $userState ? DEREGISTER : REGISTER;

		$return = $participantModel->register($participant->id, $action, $lessonID);

		if ($return)
		{
			$type = 'success';

			if ($userState)
			{
				$msg = $lang->_("COM_THM_ORGANIZER_DEREGISTRATION_SUCCESS");
			}
			else
			{
				$newUserState = THM_OrganizerHelperCourse::getUserState();
				$status       = $newUserState["status"] ? "COM_THM_ORGANIZER_COURSE_REGISTERED" : "COM_THM_ORGANIZER_WAIT_LIST";
				$msg          = THM_OrganizerHelperLanguage::sprintf("COM_THM_ORGANIZER_REGISTRATION_SUCCESS", $lang->_($status));
			}
		}
		else
		{
			$msg = $lang->_("COM_THM_ORGANIZER_STATUS_FAILURE");
		}

		if (empty($menuID))
		{
			$url .= '&view=course_list';
		}

		$app->enqueueMessage($msg, $type);
		$app->redirect(JRoute::_($url, false));
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
		$app  = JFactory::getApplication();

		$langTag = THM_OrganizerHelperLanguage::getShortTag();
		$success = $this->getModel('participant')->changeStatus();
		$app->input->set('languageTag', $langTag);

		if (empty($success))
		{
			$msgText = $lang->_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
			$msgType = 'error';
		}
		else
		{
			$msgText = $lang->_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
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
		$app       = JFactory::getApplication();
		$input     = $app->input;
		$formData = $input->get('jform', [], 'array');
		$lang      = THM_OrganizerHelperLanguage::getLanguage();
		$modelName = explode('.', $input->get('task', ''))[0];
		$model     = $this->getModel($modelName);

		// Request manipulation
		if (empty($model) OR empty($formData['id']))
		{
			$app->enqueueMessage($lang->_("COM_THM_ORGANIZER_MESSAGE_INVALID_REQUEST"), "error");
			$app->redirect(JUri::base());
		}

		$user = JFactory::getUser();
		$authorized = false;
		$url        = $this->getRedirectBase();

		if ($modelName == 'course_edit')
		{
			$authorized = ($user->authorise('core.admin') OR THM_OrganizerHelperCourse::teachesCourse($formData['id']));
		}
		elseif ($modelName == 'participant')
		{
			$authorized = $user->id == $formData['id'];
		}

		if (empty($authorized))
		{
			$app->enqueueMessage($lang->_("COM_THM_ORGANIZER_MESSAGE_NO_ACCESS_ACTION"), "error");
			$app->redirect(JUri::base());
		}

		$success = $model->save();

		if (empty($success))
		{
			$app->enqueueMessage($lang->_("COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL"), 'error');

			if ($modelName == 'course_edit')
			{
				$url .= '&view=course_edit';
			}
			elseif ($modelName == 'participant')
			{
				$url .= '&view=participant_edit';
			}
		}
		else
		{
			$app->enqueueMessage($lang->_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS'), 'success');

			if ($modelName == 'course_edit')
			{
				$url .= '&view=course_manager';
			}
			elseif ($modelName == 'participant')
			{
				$url .= '&view=course_list';
			}
		}

		if ($modelName == 'course_edit')
		{
			$lessonID = $input->getInt('lessonID');
			$url .= "&lessonID=$lessonID";
		}

		$app->redirect(JRoute::_($url, false));
	}
}
