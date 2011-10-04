<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Abstrakter Erbauer
abstract class abstrakterBauer
{
    abstract protected function erstelleStundenplan( $arr, $username, $title );
}
?>
