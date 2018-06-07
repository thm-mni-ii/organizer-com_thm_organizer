<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
$logoURL = 'media/com_thm_organizer/images/thm_organizer.png';
$actions = $this->getModel()->actions;
?>
<div id="j-main-container" class="span5">
    <div class="span10 form-vertical actions">
        <div class="organizer-header">
            <div class="organizer-logo">
                <?php echo JHtml::_('image', $logoURL, JText::_('COM_THM_ORGANIZER'),
                    ['class' => 'thm_organizer_main_image']); ?>
            </div>
        </div>
        <?php if ($actions->{'organizer.menu.schedule'}): ?>
            <div class="action-group">
                <h3><?php echo JText::_('COM_THM_ORGANIZER_SCHEDULING'); ?></h3>
                <?php foreach ($this->menuItems['scheduling'] as $name => $url): ?>
                <div class="action-item">
                    <a href="<?php echo $url; ?>">
                        <?php echo $name; ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($actions->{'core.admin'} or $actions->{'organizer.menu.manage'}): ?>
            <div class="action-group">
                <h3><?php echo JText::_('COM_THM_ORGANIZER_MANAGEMENT_AND_DOCUMENTATION'); ?></h3>
                <?php foreach ($this->menuItems['documentation'] as $name => $url): ?>
                    <div class="action-item">
                        <a href="<?php echo $url; ?>">
                            <?php echo $name; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($actions->{'organizer.hr'}): ?>
            <div class="action-group">
                <h3><?php echo JText::_('COM_THM_ORGANIZER_HUMAN_RESOURCES'); ?></h3>
                <?php foreach ($this->menuItems['humanResources'] as $name => $url): ?>
                    <div class="action-item">
                        <a href="<?php echo $url; ?>">
                            <?php echo $name; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($actions->{'organizer.fm'}): ?>
            <div class="action-group">
                <h3><?php echo JText::_('COM_THM_ORGANIZER_FACILITY_MANAGEMENT'); ?></h3>
                <?php foreach ($this->menuItems['facilityManagement'] as $name => $url): ?>
                    <div class="action-item">
                        <a href="<?php echo $url; ?>">
                            <?php echo $name; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($actions->{'core.admin'}): ?>
            <div class="action-group">
                <h3><?php echo JText::_('COM_THM_ORGANIZER_ADMINISTRATION'); ?></h3>
                <?php foreach ($this->menuItems['administration'] as $name => $url): ?>
                    <div class="action-item">
                        <a href="<?php echo $url; ?>">
                            <?php echo $name; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
