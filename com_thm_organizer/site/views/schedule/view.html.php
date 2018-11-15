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

defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class loads the schedule form into the display context.
 */
class THM_OrganizerViewSchedule extends JViewLegacy
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
    protected $defaultGrid;

    /**
     * the department for this schedule, chosen in menu options
     *
     * @var string
     */
    protected $departmentID;

    /**
     * The time period in days in which removed lessons should get displayed.
     *
     * @var string
     */
    protected $deltaDays;

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
     * Contains the current languageTag
     *
     * @var string
     */
    protected $languageTag = 'de-DE';

    /**
     * Model to this view
     *
     * @var THM_OrganizerModelSchedule
     */
    protected $model;

    /**
     * Method to display the template
     *
     * @param null $tpl template
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $this->isMobile    = THM_OrganizerHelperComponent::isSmartphone();
        $this->languageTag = THM_OrganizerHelperLanguage::getShortTag();
        $this->model       = $this->getModel();
        $this->defaultGrid = $this->model->getDefaultGrid();
        $compParams        = JComponentHelper::getParams('com_thm_organizer');
        $this->dateFormat  = $compParams->get('dateFormat', 'd.m.Y');
        $this->emailFilter = $compParams->get('emailFilter', '');
        $this->modifyDocument();
        parent::display($tpl);
    }

    /**
     * Adds resource files to the document
     *
     * @return void
     * @throws Exception
     */
    private function modifyDocument()
    {
        $doc = JFactory::getDocument();

        JHtml::_('formbehavior.chosen', 'select');
        $this->addScriptOptions();
        $doc->addScript(JUri::root() . 'media/com_thm_organizer/js/schedule.js');

        $doc->addStyleSheet(JUri::root() . 'media/com_thm_organizer/fonts/iconfont-frontend.css');
        $doc->addStyleSheet(JUri::root() . 'media/com_thm_organizer/css/schedule.css');
        $doc->addStyleSheet(JUri::root() . 'media/jui/css/icomoon.css');
    }

    /**
     * Generates required params for Javascript and adds them to the document
     *
     * @return void
     * @throws Exception
     */
    private function addScriptOptions()
    {
        $user = JFactory::getUser();
        $root = JUri::root();

        $variables = [
            'SEMESTER_MODE'     => 1,
            'PERIOD_MODE'       => 2,
            'INSTANCE_MODE'     => 3,
            'ajaxBase'          => $root . 'index.php?option=com_thm_organizer&view=schedule_ajax&format=raw',
            'auth'              => !empty($user->id) ?
                urlencode(password_hash($user->email . $user->registerDate, PASSWORD_BCRYPT)) : '',
            'dateFormat'        => $this->dateFormat,
            'defaultGrid'       => $this->defaultGrid->grid,
            'exportBase'        => $root . 'index.php?option=com_thm_organizer&view=schedule_export',
            'isMobile'          => $this->isMobile,
            'menuID'            => JFactory::getApplication()->input->get('Itemid', 0),
            'registered'        => !empty($user->id),
            'subjectDetailBase' => $root . 'index.php?option=com_thm_organizer&view=subject_details&id=1',
            'username'          => !empty($user->id) ? $user->username : ''
        ];

        $grids = [];
        foreach ($this->model->grids as $grid) {
            $grids[$grid->id] = [
                'id'   => $grid->id,
                'grid' => $grid->grid
            ];
        }
        $variables['grids'] = $grids;

        if (empty($user->email)) {
            $variables['internalUser'] = false;
        } else {
            if (empty($this->emailFilter)) {
                $variables['internalUser'] = true;
            } else {
                $atSignPos                 = strpos($user->email, '@');
                $variables['internalUser'] = strpos($user->email, $this->emailFilter, $atSignPos) !== false;
            }
        }

        $doc = JFactory::getDocument();
        $doc->addScriptOptions('variables', array_merge($variables, $this->model->params));

        JText::script('APRIL');
        JText::script('AUGUST');
        JText::script('COM_THM_ORGANIZER_ACTION_GENERATE_LINK');
        JText::script('DECEMBER');
        JText::script('FEBRUARY');
        JText::script('FRI');
        JText::script('JANUARY');
        JText::script('JULY');
        JText::script('JUNE');
        JText::script('COM_THM_ORGANIZER_LUNCHTIME');
        JText::script('MARCH');
        JText::script('MAY');
        JText::script('MON');
        JText::script('COM_THM_ORGANIZER_MY_SCHEDULE');
        JText::script('NOVEMBER');
        JText::script('OCTOBER');
        JText::script('COM_THM_ORGANIZER_POOL_SELECT_PLACEHOLDER');
        JText::script('COM_THM_ORGANIZER_PROGRAM_SELECT_PLACEHOLDER');
        JText::script('COM_THM_ORGANIZER_ROOM_SELECT_PLACEHOLDER');
        JText::script('COM_THM_ORGANIZER_ROOM_TYPE_SELECT_PLACEHOLDER');
        JText::script('SAT');
        JText::script('SEPTEMBER');
        JText::script('SUN');
        JText::script('COM_THM_ORGANIZER_TEACHER_SELECT_PLACEHOLDER');
        JText::script('THU');
        JText::script('COM_THM_ORGANIZER_TIME');
        JText::script('TUE');
        JText::script('WED');
    }
}
