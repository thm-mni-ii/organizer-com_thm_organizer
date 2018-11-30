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

use \THM_OrganizerHelperHTML as HTML;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/component.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
JFormHelper::loadFieldClass('list');

/**
 * Class creates a select box for (subject) pools.
 */
class JFormFieldPoolID extends JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'poolID';

    /**
     * Returns an array of pool options
     *
     * @return array  the pool options
     */
    protected function getOptions()
    {
        $programID = JFactory::getSession()->get('programID');
        if (empty($programID)) {
            return parent::getOptions();
        }

        $programRanges = THM_OrganizerHelperMapping::getResourceRanges('program', $programID);
        if (empty($programRanges) or count($programRanges) > 1) {
            return parent::getOptions();
        }

        $shortTag = THM_OrganizerHelperLanguage::getShortTag();
        $dbo      = JFactory::getDbo();
        $query    = $dbo->getQuery(true);
        $query->select("DISTINCT p.id AS value, p.name_$shortTag AS text");
        $query->from('#__thm_organizer_pools AS p');
        $query->innerJoin('#__thm_organizer_mappings AS m ON p.id = m.poolID');
        $query->where("lft > '{$programRanges[0]['lft']}'");
        $query->where("rgt < '{$programRanges[0]['rgt']}'");
        $query->order('text ASC');
        $dbo->setQuery($query);

        $defaultOptions = parent::getOptions();
        $pools          = THM_OrganizerHelperComponent::executeQuery('loadAssocList');
        if (empty($pools)) {
            return $defaultOptions;
        }

        // Whether or not the program display should be prefiltered according to user resource access
        $access  = $this->getAttribute('access', false);
        $options = [];

        foreach ($pools as $pool) {
            if (!$access or THM_OrganizerHelperAccess::allowDocumentAccess('pool', $pool['value'])) {
                $options[] = HTML::_('select.option', $pool['value'], $pool['text']);
            }
        }

        return array_merge($defaultOptions, $options);
    }
}
