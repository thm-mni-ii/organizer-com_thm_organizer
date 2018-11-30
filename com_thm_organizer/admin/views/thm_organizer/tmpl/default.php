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

use \THM_OrganizerHelperHTML as HTML;

$logoURL = 'media/com_thm_organizer/images/thm_organizer.png';
$logo    = HTML::_('image', $logoURL, JText::_('COM_THM_ORGANIZER'), ['class' => 'thm_organizer_main_image']);
?>
<div id="j-main-container" class="span5">
    <div class="span10 form-vertical actions">
        <div class="organizer-header">
            <div class="organizer-logo">
                <?php echo $logo; ?>
            </div>
        </div>
        <?php if (!empty($this->menuItems['scheduling'])): ?>
            <div class="action-group">
                <?php foreach ($this->menuItems['scheduling'] as $name => $url): ?>
                    <div class="action-item">
                        <?php if (!empty($url)): ?>
                            <a href="<?php echo $url; ?>">
                                <?php echo $name; ?>
                            </a>
                        <?php else: ?>
                            <?php echo $name; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($this->menuItems['documentation'])): ?>
            <div class="action-group">
                <?php foreach ($this->menuItems['documentation'] as $name => $url): ?>
                    <div class="action-item">
                        <?php if (!empty($url)): ?>
                            <a href="<?php echo $url; ?>">
                                <?php echo $name; ?>
                            </a>
                        <?php else: ?>
                            <?php echo $name; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($this->menuItems['humanResources'])): ?>
            <div class="action-group">
                <?php foreach ($this->menuItems['humanResources'] as $name => $url): ?>
                    <div class="action-item">
                        <?php if (!empty($url)): ?>
                            <a href="<?php echo $url; ?>">
                                <?php echo $name; ?>
                            </a>
                        <?php else: ?>
                            <?php echo $name; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($this->menuItems['facilityManagement'])): ?>
            <div class="action-group">
                <?php foreach ($this->menuItems['facilityManagement'] as $name => $url): ?>
                    <div class="action-item">
                        <?php if (!empty($url)): ?>
                            <a href="<?php echo $url; ?>">
                                <?php echo $name; ?>
                            </a>
                        <?php else: ?>
                            <?php echo $name; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($this->menuItems['administration'])): ?>
            <div class="action-group">
                <?php foreach ($this->menuItems['administration'] as $name => $url): ?>
                    <div class="action-item">
                        <?php if (!empty($url)): ?>
                            <a href="<?php echo $url; ?>">
                                <?php echo $name; ?>
                            </a>
                        <?php else: ?>
                            <?php echo $name; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
