<?php 
/**
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view thm_curriculum default
 * @description THM_Curriculum component admin view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
?>
<div
	class="descriptiontext">

	<!-- now Main Page Menu from Giessen_Staff -->
	<div class="description1">
		<?php echo JText::_('COM_THM_ORGANIZER_MAIN_INFO');?>
	</div>

	<div id="gimenu1">

		<!-- Manage Entries -->
		<hr />
		<div class="menuitem">
			<div class="icon"
				onclick="location.href='index.php?option=com_thm_organizer&view=semesters';">
				<div class="picture2">
					<img class="pic"
						src="components/com_thm_organizer/assets/images/semester_manager_main.png"
						alt="Entries Manager" />
				</div>
				<div class="description2">
					<?php echo JText::_('COM_THM_ORGANIZER_SUBMENU_SEMESTERS');?>
				</div>
			</div>


		</div>


		<div class="menuitem">
			<div class="icon"
				onclick="location.href='index.php?option=com_thm_organizer&view=lecturers';">
				<div class="picture2">
					<img class="pic"
						src="components/com_thm_organizer/assets/images/dozenten_main.png"
						alt="Group Manager" />
				</div>
				<div class="description2">
					<?php echo JText::_('COM_THM_ORGANIZER_SUBMENU_LECTURERS');?>
				</div>
			</div>

		</div>
		<div class="menuitem">
			<div class="icon"
				onclick="location.href='index.php?option=com_thm_organizer&view=assets';">
				<div class="picture2">
					<img class="pic"
						src="components/com_thm_organizer/assets/images/modules_main.png"
						alt="Role Manager" />
				</div>

				<div class="description2">
					<?php echo JText::_('COM_THM_ORGANIZER_SUBMENU_ASSETS');?>
				</div>
			</div>

		</div>
		<div class="menuitem">
			<div class="icon"
				onclick="location.href='index.php?option=com_thm_organizer&view=colors';">
				<div class="picture2">
					<img class="pic"
						src="components/com_thm_organizer/assets/images/colors_main.png"
						alt="Structure" />
				</div>

				<div class="description2">
					<?php echo JText::_('COM_THM_ORGANIZER_SUBMENU_COLORS');?>
				</div>

			</div>

		</div>
		<div class="menuitem">
			<div class="icon"
				onclick="location.href='index.php?option=com_thm_organizer&view=degrees';">
				<div class="picture2">
					<img class="pic"
						src="components/com_thm_organizer/assets/images/degrees_main.png"
						alt="Structure" />
				</div>

				<div class="description2">
					<?php echo JText::_('COM_THM_ORGANIZER_SUBMENU_DEGREES');?>
				</div>

			</div>

		</div>
		<div class="menuitem">
			<div class="icon"
				onclick="location.href='index.php?option=com_thm_organizer&view=majors';">
				<div class="picture2">
					<img class="pic"
						src="components/com_thm_organizer/assets/images/curriculums.png"
						alt="Structure" />
				</div>

				<div class="description2">
					<?php echo JText::_('COM_THM_ORGANIZER_SUBMENU_MAJORS');?>
				</div>

			</div>

		</div>

	</div>
</div>
