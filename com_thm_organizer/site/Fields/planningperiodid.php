<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Organizer\Helpers\HTML;
use Organizer\Helpers\OrganizerHelper;

FormHelper::loadFieldClass('list');

/**
 * Class creates a select box for planning periods.
 */
class JFormFieldPlanningPeriodID extends \JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'planningPeriodID';

    /**
     * Returns a select box where stored degree programs can be chosen
     *
     * @return array  the available degree programs
     */
    protected function getOptions()
    {
        $baseOptions = parent::getOptions();
        $dbo         = Factory::getDbo();
        $query       = $dbo->getQuery(true);

        $query->select('DISTINCT pp.id, pp.name');
        $query->from('#__thm_organizer_planning_periods AS pp');
        $query->innerJoin('#__thm_organizer_schedules AS s ON s.planningPeriodID = pp.id');

        $allowFuture = $this->getAttribute('allowFuture', 'true');

        if ($allowFuture !== 'true') {
            $query->where('pp.startDate <= CURDATE()');
        }

        $query->order('pp.startDate DESC');
        $dbo->setQuery($query);

        $planningPeriods = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($planningPeriods)) {
            return $baseOptions;
        }

        $options = [];
        foreach ($planningPeriods as $planningPeriod) {

            $options[] = HTML::_('select.option', $planningPeriod['id'], $planningPeriod['name']);

        }

        return array_merge($baseOptions, $options);
    }
}
