<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view majors default
 * @description THM_Curriculum component admin view
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Load tooltip behavior
JHtml::_('behavior.tooltip');
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=majors'); ?>"
	method="post" name="adminForm">
	<table class="adminlist">
		<thead>
			<?php echo $this->loadTemplate('head'); ?>
		</thead>
		<tfoot>
			<?php echo $this->loadTemplate('foot'); ?>
		</tfoot>
		<tbody>
			<?php echo $this->loadTemplate('body'); ?>
		</tbody>
	</table>

</form>
