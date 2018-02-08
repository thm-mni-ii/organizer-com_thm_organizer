<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldPoolID
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
JFormHelper::loadFieldClass('list');

/**
 * Class creates a form field for subject-degree program association
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
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
     * @return  array  the pool options
     */
    public function getOptions()
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
        $query->select("p.id AS value, p.name_$shortTag AS text");
        $query->from('#__thm_organizer_pools AS p');
        $query->innerJoin('#__thm_organizer_mappings AS m ON p.id = m.poolID');
        $query->where("lft > '{$programRanges[0]['lft']}'");
        $query->where("rgt < '{$programRanges[0]['rgt']}'");
        $query->order('text ASC');
        $dbo->setQuery($query);

        try {
            $pools   = $dbo->loadAssocList();
            $options = [];
            foreach ($pools as $pool) {
                $options[] = JHtml::_('select.option', $pool['value'], $pool['text']);
            }

            return array_merge(parent::getOptions(), $options);
        } catch (Exception $exc) {
            return parent::getOptions();
        }
    }
}
