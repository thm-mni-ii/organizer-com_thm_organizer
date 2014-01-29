<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldParent
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');
require_once JPATH_ADMINISTRATOR . '/components/com_thm_organizer/assets/helpers/mapping.php';

/**
 * Class JFormFieldParent for component com_thm_organizer
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldPrograms extends JFormField
{
    /**
     * @var string
     */
    protected $type = 'programs';

    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return Select box
     */
    public function getInput()
    {
        $programs = THM_OrganizerHelperMapping::getAllPrograms();

        return JHTML::_("select.genericlist", $programs, "jform[params][programID]", null, "value", "program", $this->value);
    }
}
