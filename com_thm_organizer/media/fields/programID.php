<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldProgramID
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.helpers.corehelper');
JFormHelper::loadFieldClass('list');

/**
 * Class creates a form field for subject-degree program association
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldProgramID extends JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'programID';

    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return Select box
     */
    public function getOptions()
    {
        $shortTag = THM_CoreHelper::getLanguageShortTag();
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $nameParts = array("dp.name_$shortTag", 'd.abbreviation', 'dp.version' );
        $nameSelect = $query->concatenate($nameParts, ', ') ." AS text";

        $query->select("dp.id AS value, $nameSelect");
        $query->from('#__thm_organizer_programs AS dp');
        $query->innerJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON dp.id = m.programID');
        $query->order('text ASC');
        $dbo->setQuery((string) $query);

        try
        {
            $programs = $dbo->loadAssocList();
            $options = array();
            foreach ($programs as $program)
            {
                $options[] = JHtml::_('select.option', $program['value'], $program['text']);
            }

            return array_merge(parent::getOptions(), $options);
        }
        catch (Exception $exc)
        {
            return parent::getOptions();
        }
    }
}
