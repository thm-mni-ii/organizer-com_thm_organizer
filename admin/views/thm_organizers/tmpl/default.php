<?php
/** *
 * PHP version 5
 *
 * @category Joomla Programming Weeks SS2008: FH Giessen-Friedberg
 * @package  com_thm_organizer
 * @author   James Antrim <james.antrim@yahoo.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @link     http://www.mni.fh-giessen.de
 **/
// no direct access
defined('_JEXEC') or die('Restricted access');?>

<div id="thm_organizer_main" >
    <div id="thm_organizer_main_description">
        COM_THM_ORGANIZER_MAIN_DESCRIPTION
    </div>
    <div id="cpanel">
        <div class="thm_organizer_main_submenu" >
            <div class="thm_organizer_main_linkdiv" >
                <div class="icon">
                    <a href="index.php?option=<?php echo $this->option; ?>&amp;view=category_manager"><?php
                        echo JHTML::_('image',
                                      'components/com_thm_organizer/assets/images/category_manager.png',
                                      JText::_( 'Category Manager' ),
                                      array( 'class' => 'thm_organizer_main_image'));
                        ?><span><?php echo JText::_( COM_THM_ORGANIZER_CM_NAME ); ?></span></a>
                </div>
            </div>
            <div class="thm_organizer_main_descriptiondiv" >
                COM_THM_ORGANIZER_CM_DESCRIPTION
            </div>
        </div>
        <div class="thm_organizer_main_submenu" >
            <div class="thm_organizer_main_linkdiv" >
                <div class="icon">
                    <a href="index.php?option=<?php echo $this->option; ?>&amp;view=monitor_manager"><?php
                        echo JHTML::_('image',
                                      'components/com_thm_organizer/assets/images/monitor_manager.png',
                                      JText::_( 'Monitor Manager' ),
                                      array( 'class' => 'thm_organizer_main_image'));
                        ?><span><?php echo JText::_( COM_THM_ORGANIZER_MM_NAME ); ?></span></a>
                </div>
            </div>
            <div class="thm_organizer_main_descriptiondiv" >
                COM_THM_ORGANIZER_MM_DESCRIPTION
            </div>
        </div>
        <div class="thm_organizer_main_submenu" >
            <div class="thm_organizer_main_linkdiv" >
                <div class="icon">
                    <a href="index.php?option=<?php echo $this->option; ?>&amp;view=semester_manager"><?php
                        echo JHTML::_('image',
                                      'components/com_thm_organizer/assets/images/semester_manager.png',
                                      JText::_( 'Semester Manager' ),
                                      array( 'class' => 'thm_organizer_main_image'));
                        ?><span><?php echo JText::_( COM_THM_ORGANIZER_SM_NAME ); ?></span></a>
                </div>
            </div>
            <div class="thm_organizer_main_descriptiondiv" >
                COM_THM_ORGANIZER_SM_DESCRIPTION
            </div>
        </div>
        <div class="thm_organizer_main_submenu" >
            <div class="thm_organizer_main_linkdiv" >
                <div class="icon">
                    <a href="index.php?option=<?php echo $this->option; ?>&amp;view=scheduler_application_settings"><?php
                        echo JHTML::_('image',
                                      'components/com_thm_organizer/assets/images/scheduler_application_settings.jpg',
                                      JText::_( 'Scheduler Application Settings' ),
                                      array( 'class' => 'thm_organizer_main_image'));
                        ?><span><?php echo JText::_( COM_THM_ORGANIZER_SAS_NAME ); ?></span></a>
                </div>
            </div>
            <div class="thm_organizer_main_descriptiondiv" >
                COM_THM_ORGANIZER_SAS_DESCRIPTION
            </div>
        </div>
        <div class="thm_organizer_main_submenu" >
            <div class="thm_organizer_main_linkdiv" >
                <div class="icon">
                    <a href="index.php?option=<?php echo $this->option; ?>&amp;view=virtual_schedule_manager"><?php
                        echo JHTML::_('image',
                                      'components/com_thm_organizer/assets/images/virtual_manager.png',
                                      JText::_( 'Virtual Schedule Manager' ),
                                      array( 'class' => 'thm_organizer_main_image'));
                        ?><span><?php echo JText::_( COM_THM_ORGANIZER_VSM_NAME ); ?></span></a>
                </div>
            </div>
            <div class="thm_organizer_main_descriptiondiv" >
                COM_THM_ORGANIZER_VSM_DESCRIPTION
            </div>
        </div>
    </div>
</div>
