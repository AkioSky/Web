<?php
/*------------------------------------------------------------------------
# plgVmCustomNumberinput - Custom number field for Virtuemart
# ------------------------------------------------------------------------
# author    Jeremy Magne
# copyright Copyright (C) 2010 Daycounts.com. All Rights Reserved.
# Websites: http://www.daycounts.com
# Technical Support: http://www.daycounts.com/en/contact/
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
-------------------------------------------------------------------------*/
defined('_JEXEC') or 	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;



/************************************/
/* This is a test function to be used with custom function mode
/* You can create another file in the same folde and define your own functions there.
/* Each function must take the entered value as parameter and will return the calculated price.
/************************************/

function times2($value) {
	return $value*2;
}