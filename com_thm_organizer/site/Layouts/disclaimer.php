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

use THM_OrganizerHelperHTML as HTML;

$lsfLink   = HTML::link(
    'https://studien-sb-service.th-mittelhessen.de/docu/online.html',
    Languages::_('THM_ORGANIZER_DISCLAIMER_LSF_TITLE')
);
$lsfText   = Languages::_('THM_ORGANIZER_DISCLAIMER_LSF_TEXT');
$lsfOutput = sprintf($lsfText, $lsfLink);

$ambLink   = HTML::link(
    'http://www.thm.de/amb/pruefungsordnungen',
    Languages::_('THM_ORGANIZER_DISCLAIMER_AMB_TITLE')
);
$ambText   = Languages::_('THM_ORGANIZER_DISCLAIMER_AMB_TEXT');
$ambOutput = sprintf($ambText, $ambLink);

$poLink   = HTML::link(
    'http://www.thm.de/site/studium/sie-studieren/pruefungsordnung.html',
    Languages::_('THM_ORGANIZER_DISCLAIMER_PO_TITLE')
);
$poText   = Languages::_('THM_ORGANIZER_DISCLAIMER_PO_TEXT');
$poOutput = sprintf($poText, $poLink);
?>
<div class="legal-disclaimer">
    <h4><?php echo Languages::_('THM_ORGANIZER_DISCLAIMER_HEADER'); ?></h4>
    <ul>
        <li><?php echo $lsfOutput; ?></li>
        <li><?php echo $ambOutput; ?></li>
        <li><?php echo $poOutput; ?></li>
    </ul>
</div>
