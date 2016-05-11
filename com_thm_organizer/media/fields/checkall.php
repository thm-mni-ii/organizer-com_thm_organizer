<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldCheckAll
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');

/**
 * Class loads a grid check all box
 *
 * @category    Joomla.Library
 * @package     thm_core
 * @subpackage  lib_thm_core.site
 */
class JFormFieldCheckAll extends JFormField
{
    protected $type = 'checkAll';

    /**
     * Makes a checkbox
     *
     * @return  string  a HTML checkbox
     */
    public function getInput()
    {
        return JHtml::_('grid.checkall');
    }
}