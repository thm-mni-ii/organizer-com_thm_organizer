<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use Organizer\Helpers\HTML;

HTML::_('searchtools.form', '#adminForm', []);
?>
<div class="js-stools clearfix">
    <div class="clearfix">
        <div class="js-stools-container-list">
            <div class="ordering-select">
                <div class="js-stools-field-list">
                    <?php echo $this->form->getField('languageTag')->input; ?>
                </div>
            </div>
        </div>
    </div>
</div>
