<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Class loads lesson statistic information into the display context.
 */
class LessonStatistics extends BaseHTMLView
{
    public $columns = [];

    public $form = null;

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
        // Use language_selection layout
        $this->languageParams = ['view' => 'lesson_statistics'];
        $this->state          = $this->get('State');
        $this->form           = $this->get('Form');
        $this->form->setValue('termID', null, $this->state->get('termID'));
        $this->form->setValue('departmentID', null, $this->state->get('departmentID'));
        $this->form->setValue('categoryID', null, $this->state->get('categoryID'));

        $model         = $this->getModel();
        $this->columns = $model->columns;
        $this->rows    = $model->rows;
        $this->lessons = $model->lessons;
        $this->total   = $model->total;

        Factory::getDocument()->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/lesson_statistics.css');

        parent::display($tpl);
    }
}
