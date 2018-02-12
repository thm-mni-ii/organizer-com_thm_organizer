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
jimport('joomla.form.formfield');

/**
 * Class uses the grid.checkall field.
 *
 * @todo replace uses of this
 */
class JFormFieldCheckAll extends JFormField
{
    protected $type = 'checkAll';

    /**
     * Makes a checkbox
     *
     * @return string  a HTML checkbox
     */
    public function getInput()
    {
        return JHtml::_('grid.checkall');
    }
}