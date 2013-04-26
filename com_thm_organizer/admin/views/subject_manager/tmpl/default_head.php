<?php
defined('_JEXEC') or die('Restricted Access');

$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$saveOrder	= $listOrder=='ordering';


JHTML::_('behavior.modal', 'a.modal-button');
?>


