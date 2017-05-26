<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
?>

<div id="form-container" class="form-container">

	<form action="<?php echo JRoute::_("index.php?option=com_thm_organizer&view=course_list"); ?>"
		  method="post" name="adminForm" id="adminForm">

		<input type="hidden" name="languageTag" value="<?php echo JFactory::getApplication()->input->get('languageTag', 'de'); ?>"/>

		<div class="filter-item short-item">
			<?php echo $this->filters['filter_subject']; ?>
		</div>

		<div class="filter-item short-item">
			<?php echo $this->filters['filter_active']; ?>
		</div>

		<button class="submit-button btn" onclick="showPostLoader();form.submit();">
			<span class="icon-loop"></span>
		</button>

	</form>

</div>
