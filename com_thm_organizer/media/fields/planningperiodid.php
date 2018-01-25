<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        JFormFieldPlanningPeriodID
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/planning_periods.php';

/**
 * Class creates a form field for subject-degree program association
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class JFormFieldPlanningPeriodID extends JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'planningPeriodID';

    /**
     * Returns a select box where stored degree programs can be chosen
     *
     * @return  array  the available degree programs
     */
    public function getOptions()
    {
        $baseOptions = parent::getOptions();
        $dbo         = JFactory::getDbo();
        $query       = $dbo->getQuery(true);

        $query->select("DISTINCT pp.id, pp.name");
        $query->from('#__thm_organizer_planning_periods AS pp');
        $query->innerJoin('#__thm_organizer_schedules AS s ON s.planningPeriodID = pp.id');

        $allowFuture = $this->getAttribute('allowFuture', 'true');

        if ($allowFuture !== 'true') {
            $query->where('pp.startDate <= CURDATE()');
        }

        $query->order('pp.startDate DESC');
        $dbo->setQuery($query);

        try {
            $planningPeriods = $dbo->loadAssocList();
        } catch (Exception $exc) {
            return $baseOptions;
        }

        if (empty($planningPeriods)) {
            return $baseOptions;
        }

        $options = [];
        foreach ($planningPeriods as $planningPeriod) {

            $options[] = JHtml::_('select.option', $planningPeriod['id'], $planningPeriod['name']);

        }

        return array_merge($baseOptions, $options);
    }
}
