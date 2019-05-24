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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Organizer\Helpers\HTML;
use Organizer\Helpers\OrganizerHelper;

FormHelper::loadFieldClass('list');

/**
 * Class creates a select box for terms.
 */
class JFormFieldTermID extends \JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'termID';

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

        $query->select('DISTINCT term.id, term.name');
        $query->from('#__thm_organizer_terms AS term');
        $query->innerJoin('#__thm_organizer_schedules AS s ON s.termID = term.id');

        $allowFuture = $this->getAttribute('allowFuture', 'true');

        if ($allowFuture !== 'true') {
            $query->where('term.startDate <= CURDATE()');
        }

        $query->order('term.startDate DESC');
        $dbo->setQuery($query);

        $terms = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($terms)) {
            return $baseOptions;
        }

        $options = [];
        foreach ($terms as $term) {

            $options[] = HTML::_('select.option', $term['id'], $term['name']);

        }

        return array_merge($baseOptions, $options);
    }
}
