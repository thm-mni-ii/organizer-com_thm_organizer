<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizersViewSoapqueries
 * @description THM_OrganizersViewSoapqueries component admin view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Class THM_OrganizersViewSoapqueries for component com_thm_organizer
 *
 * Class provides methods to display the view soap queries
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizersViewSoapqueries extends JView
{
	/**
	 * Method to get display
	 *
	 * @param   Object  $tpl  template  (default: null)
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		JToolBarHelper::title(JText::_('com_thm_organizer_SUBMENU_SOAPQUERIES_TITLE'), 'generic.png');
		JToolBarHelper::addNew('soapquery.add', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('soapquery.edit', 'JTOOLBAR_EDIT');
		JToolBarHelper::deleteList('', 'soapquery.delete', 'JTOOLBAR_DELETE');
		
		$this->items = $this->get('Items');		
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');

		parent::display($tpl);
	}
}
