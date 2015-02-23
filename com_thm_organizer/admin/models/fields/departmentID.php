<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldDepartmentID
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
JFormHelper::loadFieldClass('list');

/**
 * Class creates a form field for subject-degree program association
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldDepartmentID extends JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'departmentID';

    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return Select box
     */
    public function getOptions()
    {
        $allowedIDs = THM_OrganizerHelperComponent::getAccessibleDepartments();
        if (empty($allowedIDs))
        {
            return parent::getOptions();
        }

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("id AS value, short_name AS text");
        $query->from('#__thm_organizer_departments');
        $query->where("id IN ( '" . implode("', '", $allowedIDs). "' )");
        $query->order('text ASC');
        $dbo->setQuery((string) $query);

        try
        {
            $departments = $dbo->loadAssocList();
        }
        catch (Exception $exc)
        {
            return parent::getOptions();
        }

        $options = array();
        foreach ($departments as $department)
        {
            $options[] = JHtml::_('select.option', $department['value'], $department['text']);
        }
        return array_merge(parent::getOptions(), $options);
    }
}
