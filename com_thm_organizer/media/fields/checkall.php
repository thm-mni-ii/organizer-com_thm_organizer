<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
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
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
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