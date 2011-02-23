<?php
/**
 * This file contains the data type class Image.
 *
 * PHP version 5
 *
 * @category Joomla Programming Weeks SS2008: FH Giessen-Friedberg
 * @package  com_staff
 * @author   Sascha Henry <sascha.henry@mni.fh-giessen.de>
 * @author   Christian Gueth <christian.gueth@mni.fh-giessen.de>
 * @author   Severin Rotsch <severin.rotsch@mni.fh-giessen.de>
 * @author   Martin Karry <martin.karry@mni.fh-giessen.de>
 * @author   Rene Bartsch <rene.bartsch@mni.fh-giessen.de>
 * @author   Dennis Priefer <dennis.priefer@mni.fh-giessen.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @link     http://www.mni.fh-giessen.de
 **/
defined('_JEXEC') or die ('Restricted access');
?>

<div id="thm_organizer_resource" >
    <div id="thm_organizer_resource_description">
        THM - Resource Manager is designed to handle the resource management of the Univeristy of Applied Sciences at Giessen, Germany.
    </div>
    <div id="cpanel">
        <div class="thm_organizer_resource_submenu" >
            <div class="thm_organizer_resource_linkdiv" >
                <div class="icon">
                    <a href="index.php?option=<?php echo $this->option; ?>&amp;view=resource_class_manager"><?php
                        echo JHTML::_('image',
                                      'components/com_thm_organizer/assets/images/resource_class_manager.png',
                                      JText::_( 'Class Manager' ),
                                      array( 'class' => 'thm_organizer_resource_class_image'));
                        ?><span><?php echo JText::_( 'Class Manager' ); ?></span></a>
                </div>
            </div>
            <div class="thm_organizer_resource_descriptiondiv" >
                Sitzen zwei Muffins im Backrohr.
            </div>
        </div>
        <div class="thm_organizer_resource_submenu" >
            <div class="thm_organizer_resource_linkdiv" >
                <div class="icon">
                    <a href="index.php?option=<?php echo $this->option; ?>&amp;view=resource_teacher_manager"><?php
                        echo JHTML::_('image',
                                      'components/com_thm_organizer/assets/images/resource_teacher_manager.png',
                                      JText::_( 'Teacher Manager' ),
                                      array( 'class' => 'thm_organizer_teacher_class_image'));
                        ?><span><?php echo JText::_( 'Teacher Manager' ); ?></span></a>
                </div>
            </div>
            <div class="thm_organizer_resource_descriptiondiv" >
                Da sagt der eine zum anderen:"hey, langsam wirds hier ziemlich heiß."
            </div>
        </div>
        <div class="thm_organizer_resource_submenu" >
            <div class="thm_organizer_resource_linkdiv" >
                <div class="icon">
                    <a href="index.php?option=<?php echo $this->option; ?>&amp;view=resource_room_manager"><?php
                        echo JHTML::_('image',
                                      'components/com_thm_organizer/assets/images/resource_room_manager.png',
                                      JText::_( 'Room Manager' ),
                                      array( 'class' => 'thm_organizer_resource_room_image'));
                        ?><span><?php echo JText::_( 'Room Manager' ); ?></span></a>
                </div>
            </div>
            <div class="thm_organizer_resource_descriptiondiv" >
                Anwortet der andere:"oh mein gott, ein sprechendes Muffin!"
            </div>
        </div>
    </div>
</div>
