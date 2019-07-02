<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class creates a form field for enabling or disabling publishing for specific plan (subject) pools for specific
 * terms.
 */
class TermPublishingField extends FormField
{
    use Translated;

    /**
     * @var  string
     */
    protected $type = 'TermPublishing';

    /**
     * Returns a select box where resource attributes can be selected
     *
     * @return string  the HTML select box
     */
    protected function getInput()
    {
        $today       = date('Y-m-d');
        $dbo         = Factory::getDbo();
        $periodQuery = $dbo->getQuery(true);
        $periodQuery->select('id, name')
            ->from('#__thm_organizer_terms')
            ->where("endDate > '$today'")
            ->order('startDate ASC');
        $dbo->setQuery($periodQuery);

        $periods = OrganizerHelper::executeQuery('loadAssocList', [], 'id');
        if (empty($periods)) {
            return '';
        }

        $groupID   = Input::getID();
        $poolQuery = $dbo->getQuery(true);
        $poolQuery->select('termID, published')
            ->from('#__thm_organizer_group_publishing')
            ->where("groupID = '$groupID'");
        $dbo->setQuery($poolQuery);

        $publishingEntries = OrganizerHelper::executeQuery('loadAssocList', [], 'termID');

        $return = '<div class="publishing-container">';
        foreach ($periods as $period) {
            $pID   = "jform_publishing_{$period['id']}";
            $pName = "jform[publishing][{$period['id']}]";

            $return .= '<div class="period-container">';
            $return .= '<div class="period-label">' . $period['name'] . '</div>';
            $return .= '<div class="period-input">';
            $return .= '<select id="' . $pID . '" name="' . $pName . '" class="chzn-color-state">';

            // Implicitly (new) and explicitly published entries
            if (!isset($publishingEntries[$period['id']]) or $publishingEntries[$period['id']]['published']) {
                $return .= '<option value="1" selected="selected">' . Languages::_('THM_ORGANIZER_YES') . '</option>';
                $return .= '<option value="0">' . Languages::_('THM_ORGANIZER_NO') . '</option>';
            } else {
                $return .= '<option value="1">' . Languages::_('THM_ORGANIZER_YES') . '</option>';
                $return .= '<option value="0" selected="selected">' . Languages::_('THM_ORGANIZER_NO') . '</option>';
            }

            $return .= '</select>';
            $return .= '</div>';
            $return .= '</div>';
        }
        $return .= '</div>';

        return $return;
    }
}
