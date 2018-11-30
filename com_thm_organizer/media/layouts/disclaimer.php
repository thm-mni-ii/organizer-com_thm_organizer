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

$lang = $displayData['language'];

$lsfLink   = HTML::link(
    'https://studien-sb-service.th-mittelhessen.de/docu/online.html',
    $lang->_('COM_THM_ORGANIZER_DISCLAIMER_LSF_TITLE')
);
$lsfText   = $lang->_('COM_THM_ORGANIZER_DISCLAIMER_LSF_TEXT');
$lsfOutput = sprintf($lsfText, $lsfLink);

$ambLink   = HTML::link(
    'http://www.thm.de/amb/pruefungsordnungen',
    $lang->_('COM_THM_ORGANIZER_DISCLAIMER_AMB_TITLE')
);
$ambText   = $lang->_('COM_THM_ORGANIZER_DISCLAIMER_AMB_TEXT');
$ambOutput = sprintf($ambText, $ambLink);

$poLink   = HTML::link(
    'http://www.thm.de/site/studium/sie-studieren/pruefungsordnung.html',
    $lang->_('COM_THM_ORGANIZER_DISCLAIMER_PO_TITLE')
);
$poText   = $lang->_('COM_THM_ORGANIZER_DISCLAIMER_PO_TEXT');
$poOutput = sprintf($poText, $poLink);
?>
<div class="legal-disclaimer">
    <h4><?php echo $lang->_('COM_THM_ORGANIZER_DISCLAIMER_HEADER'); ?></h4>
    <ul>
        <li><?php echo $lsfOutput; ?></li>
        <li><?php echo $ambOutput; ?></li>
        <li><?php echo $poOutput; ?></li>
    </ul>
</div>
