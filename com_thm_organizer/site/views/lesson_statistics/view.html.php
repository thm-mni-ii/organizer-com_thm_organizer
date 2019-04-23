<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

require_once JPATH_SITE . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class loads lesson statistic information into the display context.
 */
class THM_OrganizerViewLesson_Statistics extends \Joomla\CMS\MVC\View\HtmlView
{
    public $columns = [];

    public $form = null;

    public $lang = null;

    public $languageLinks;

    public $languageParams;

    public $lessons = [];

    public $rows = [];

    public $total = 0;

    /**
     * Execute and display a template script.
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return void sets context variables and uses the parent's display method
     */
    public function display($tpl = null)
    {
        $this->lang           = THM_OrganizerHelperLanguage::getLanguage();
        $this->languageLinks  = new \JLayoutFile('language_links', JPATH_ROOT . '/components/com_thm_organizer/Layouts');
        $this->languageParams = ['view' => 'lesson_statistics'];
        $this->state          = $this->get('State');
        $this->form           = $this->get('Form');
        $this->form->setValue('planningPeriodID', null, $this->state->get('planningPeriodID'));
        $this->form->setValue('departmentID', null, $this->state->get('departmentID'));
        $this->form->setValue('programID', null, $this->state->get('programID'));

        $model         = $this->getModel();
        $this->columns = $model->columns;
        $this->rows    = $model->rows;
        $this->lessons = $model->lessons;
        $this->total   = $model->total;

        \JFactory::getDocument()->addStyleSheet(\JUri::root() . '/media/com_thm_organizer/css/lesson_statistics.css');

        parent::display($tpl);
    }
}