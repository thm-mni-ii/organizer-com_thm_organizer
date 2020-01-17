<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Grids;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class loads the schedule form into the display context.
 */
class ScheduleItem extends BaseHTMLView
{
	/**
	 * format for displaying dates
	 *
	 * @var string
	 */
	protected $dateFormat;

	/**
	 * default time grid, loaded first
	 *
	 * @var object
	 */
	public $grids;

	/**
	 * the department for this schedule, chosen in menu options
	 *
	 * @var string
	 */
	protected $params;

	/**
	 * The time period in days in which removed events should get displayed.
	 *
	 * @var string
	 */
	protected $delta;

	/**
	 * Filter to indicate intern emails
	 *
	 * @var string
	 */
	protected $emailFilter;

	/**
	 * mobile device or not
	 *
	 * @var boolean
	 */
	protected $isMobile = false;

	/**
	 * Contains the current language tag
	 *
	 * @var string
	 */
	protected $tag = 'de';

	/**
	 * Method to display the template
	 *
	 * @param   null  $tpl  template
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		$compParams        = Input::getParams();
		$this->dateFormat  = $compParams->get('dateFormat', 'd.m.Y');
		$this->emailFilter = $compParams->get('emailFilter', '');
		$this->grids       = Grids::getResources();
		$this->isMobile    = OrganizerHelper::isSmartphone();
		$this->params      = $this->getModel()->params;
		$this->tag         = Languages::getTag();

		$this->modifyDocument();
		parent::display($tpl);
	}

	/**
	 * Modifies document variables and adds links to external files
	 *
	 * @return void
	 */
	protected function modifyDocument()
	{
		$this->addScriptOptions();

		$doc = Factory::getDocument();
		$doc->addScript(Uri::root() . 'components/com_thm_organizer/js/schedule.js');
		$doc->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/schedule_item.css');
		$doc->addStyleSheet(Uri::root() . 'media/jui/css/icomoon.css');

		HTML::_('formbehavior.chosen', 'select');
	}

	/**
	 * Generates required params for Javascript and adds them to the document
	 *
	 * @return void
	 */
	private function addScriptOptions()
	{
		$user = Factory::getUser();
		$root = Uri::root();

		$variables = [
			'SEMESTER_MODE'   => 1,
			'BLOCK_MODE'      => 2,
			'INSTANCE_MODE'   => 3,
			'ajaxBase'        => $root . 'index.php?option=com_thm_organizer&format=json&departmentIDs=',
			'dateFormat'      => $this->dateFormat,
			'exportBase'      => $root . 'index.php?option=com_thm_organizer&view=schedule_export',
			'isMobile'        => $this->isMobile,
			'menuID'          => Input::getItemid(),
			'subjectItemBase' => $root . 'index.php?option=com_thm_organizer&view=subject_item&id=1',
			'username'        => $user->id ? $user->username : ''
		];

		if ($user->email)
		{
			$domain = substr($user->email, strpos($user->email, '@'));
			if (empty($this->emailFilter) or strpos($domain, $this->emailFilter) !== false)
			{
				$variables['userID']   = $user->id;
				$variables['auth']     = urlencode(password_hash($user->email . $user->registerDate, PASSWORD_BCRYPT));
				$variables['username'] = $user->username;
			}
		}

		if (empty($variables['userID']))
		{
			$variables['userID']   = 0;
			$variables['auth']     = '';
			$variables['username'] = '';
		}

		$variables['grids'] = [];
		foreach ($this->grids as $grid)
		{
			$gridID     = $grid['id'];
			$gridString = Grids::getGrid($gridID);

			// Set a default until when/if the real default is iterated
			$this->params['defaultGrid'] = empty($this->params['defaultGrid']) ?
				$gridString : $this->params['defaultGrid'];
			$variables['grids'][$gridID] = ['id' => $gridID, 'grid' => $gridString];

			if ($grid['defaultGrid'])
			{
				$this->params['defaultGrid'] = $gridString;
			}
		}

		$doc = Factory::getDocument();
		$doc->addScriptOptions('variables', array_merge($variables, $this->params));

		Languages::script('APRIL');
		Languages::script('AUGUST');
		Languages::script('DECEMBER');
		Languages::script('FEBRUARY');
		Languages::script('FRI');
		Languages::script('JANUARY');
		Languages::script('JULY');
		Languages::script('JUNE');
		Languages::script('MARCH');
		Languages::script('MAY');
		Languages::script('MON');
		Languages::script('NOVEMBER');
		Languages::script('OCTOBER');
		Languages::script('SAT');
		Languages::script('SEPTEMBER');
		Languages::script('THM_ORGANIZER_SPEAKER');
		Languages::script('THM_ORGANIZER_SPEAKERS');
		Languages::script('SUN');
		Languages::script('THM_ORGANIZER_SUPERVISOR');
		Languages::script('THM_ORGANIZER_SUPERVISORS');
		Languages::script('THM_ORGANIZER_GENERATE_LINK');
		Languages::script('THM_ORGANIZER_LUNCHTIME');
		Languages::script('THM_ORGANIZER_MY_SCHEDULE');
		Languages::script('THM_ORGANIZER_SELECT_CATEGORY');
		Languages::script('THM_ORGANIZER_SELECT_GROUP');
		Languages::script('THM_ORGANIZER_SELECT_ROOM');
		Languages::script('THM_ORGANIZER_SELECT_ROOMTYPE');
		Languages::script('THM_ORGANIZER_SELECT_PERSON');
		Languages::script('THM_ORGANIZER_TEACHER');
		Languages::script('THM_ORGANIZER_TEACHERS');
		Languages::script('THM_ORGANIZER_TIME');
		Languages::script('THM_ORGANIZER_TUTOR');
		Languages::script('THM_ORGANIZER_TUTORS');
		Languages::script('THU');
		Languages::script('TUE');
		Languages::script('WED');
	}
}
