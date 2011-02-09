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
        THM - Organizer is designed to handle the resource management and scheduling needs of the Univeristy of Applied Sciences at Giessen, Germany.
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
                        ?><span><?php echo JText::_( 'Category Manager' ); ?></span></a>
                </div>
            </div>
            <div class="thm_organizer_main_descriptiondiv" >
                The category manager associates event types with corresponding category types from the content<br />
                component. This enables the administrator to set and use the access permissions of the associated<br />
                content category.
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
                        ?><span><?php echo JText::_( 'Monitor Manager' ); ?></span></a>
                </div>
            </div>
            <div class="thm_organizer_main_descriptiondiv" >
                The monitor manager associates the IP addresses of monitors with the room where they are displayed<br />
                and the content which is to be displayed on them.
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
                        ?><span><?php echo JText::_( 'Semester Manager' ); ?></span></a>
                </div>
            </div>
            <div class="thm_organizer_main_descriptiondiv" >
                The semester manager creates semesters. Semesters, as used here, are a relation of an organizational<br />
                unit such as a university department with an actual semester. In addition a user group can be selected which<br />
                then has permission to administrate the schedule(s) associated with this semester.
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
                        ?><span><?php echo JText::_( 'Scheduler Application Settings' ); ?></span></a>
                </div>
            </div>
            <div class="thm_organizer_main_descriptiondiv" >
                Dummy text.
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
                        ?><span><?php echo JText::_( 'Virtual Schedule Manager' ); ?></span></a>
                </div>
            </div>
            <div class="thm_organizer_main_descriptiondiv" >
                Dummy text.
            </div>
        </div>
    </div>
</div>
