<?php
/**
 * @package  	Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @author   	Markus Baier <markus.baier@mni.fh-giessen.de>
 * @copyright	THM Mittelhessen 2011
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @link     	http://www.mni.fh-giessen.de
 * @version		$Id$
 **/

defined( '_JEXEC' ) or die;
jimport('joomla.application.component.controllerform');

class THM_OrganizerControllerSemester extends JControllerForm {

	public function save($key = null, $urlVar = null) {
		$retVal = parent::save($key, $urlVar);
		
		if ($retVal) {
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=semesters', false));
		}
	}
	
	public function cancel() {
		$retVal = parent::cancel();
		
		if ($retVal) {
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=semesters', false));
		}
	}

	public function delete() {
		$db =& JFactory::getDBO();
		$cid = JRequest::getVar( 'cid',   array(), 'post', 'array' );
		
		foreach($cid as $id) {
			$query = 'DELETE FROM #__thm_organizer_curriculum_semesters'
			. ' WHERE id = '.$id.';';
			$db->setQuery( $query );
			$db->query();
		}
		
		$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=semesters', false));
	}
}
    
?>