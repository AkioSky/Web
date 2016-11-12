<?php
/*------------------------------------------------------------------------
# author    Jeremy Magne
# copyright Copyright (C) 2010 Daycounts.com. All Rights Reserved.
# Websites: http://www.daycounts.com
# Technical Support: http://www.daycounts.com/en/contact/
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
-------------------------------------------------------------------------*/
defined('_JEXEC') or die( 'Restricted access' );

class JFormFieldTitle extends JFormField {

	public function getInput()	{

		$lang = JFactory::getLanguage();
		$lang->load('plg_vmcustom_numberinput.sys');
		
		$description = $this->description;
		$description = (JText::_($description)) ? JText::_($description) : $description;

		$html = '';
		if ($this->value) {
			$html .= '<div style="margin: 10px 0 5px 0; font-weight: bold; padding: 5px; background-color: #cacaca; float:none; clear:both;">';
			$html .= JText::_($this->value);
			$html .= '</div>';
			$html .= $description;
		}
		
		return $html;
	}	
	

}