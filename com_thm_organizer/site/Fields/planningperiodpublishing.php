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

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/OrganizerHelper.php';

use Joomla\CMS\Factory;

/**
 * Class creates a form field for enabling or disabling publishing for specific plan (subject) pools for specific
 * planning periods.
 */
class JFormFieldPlanningPeriodPublishing extends \Joomla\CMS\Form\FormField
{
    /**
     * @var  string
     */
    protected $type = 'planningPeriodPublishing';

    /**
     * Returns a select box where resource attributes can be selected
     *
     * @return string  the HTML select box
     */
    protected function getInput()
    {
        $dbo         = Factory::getDbo();
        $periodQuery = $dbo->getQuery(true);
        $periodQuery->select('id, name')->from('#__thm_organizer_planning_periods')->order('startDate ASC');
        $dbo->setQuery($periodQuery);

        $periods = OrganizerHelper::executeQuery('loadAssocList', [], 'id');
        if (empty($periods)) {
            return '';
        }

        $poolID    = OrganizerHelper::getInput()->getInt('id');
        $poolQuery = $dbo->getQuery(true);
        $poolQuery->select('planningPeriodID, published')
            ->from('#__thm_organizer_plan_pool_publishing')
            ->where("planPoolID = '$poolID'");
        $dbo->setQuery($poolQuery);

        $publishingEntries = OrganizerHelper::executeQuery('loadAssocList', [], 'planningPeriodID');

        $return = '<div class="publishing-container">';
        foreach ($periods as $period) {
            $pID   = $period['id'];
            $pName = $period['name'];

            $return .= '<div class="period-container">';
            $return .= '<div class="period-label">' . $pName . '</div>';
            $return .= '<div class="period-input">';
            $return .= '<select id="jform_publishing_' . $pID . '" name="jform[publishing][' . $pID . ']" class="chzn-color-state">';

            // Implicitly (new) and explicitly published entries
            if (!isset($publishingEntries[$period['id']]) or $publishingEntries[$period['id']]['published']) {
                $return .= '<option value="1" selected="selected">' . Languages::_('JYES') . '</option>';
                $return .= '<option value="0">' . Languages::_('JNO') . '</option>';
            } else {
                $return .= '<option value="1">' . Languages::_('JYES') . '</option>';
                $return .= '<option value="0" selected="selected">' . Languages::_('JNO') . '</option>';
            }

            $return .= '</select>';
            $return .= '</div>';
            $return .= '</div>';
        }
        $return .= '</div>';

        return $return;
    }
}
