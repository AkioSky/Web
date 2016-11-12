<?php
/*------------------------------------------------------------------------
# plgVmCustomNumberinput - Numeric input field for Virtuemart
# ------------------------------------------------------------------------
# author    Jeremy Magne
# copyright Copyright (C) 2010 Daycounts.com. All Rights Reserved.
# Websites: http://www.daycounts.com
# Technical Support: http://www.daycounts.com/en/contact/
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
-------------------------------------------------------------------------*/
defined('_JEXEC') or die();

$class='vmcustom-numberinput';

vmJsApi::JvalideForm();
$validations = array();
if ($this->params->min_value || $this->params->max_value) {
	if ($this->params->min_value) {
		$validations[] = 'min['.$this->params->min_value.']';
	}
	if ($this->params->max_value) {
		$validations[] = 'max['.$this->params->max_value.']';
	}
	if ($this->params->numeric_type == 'int') {
		$validations[] = 'custom[integer]';
	}
	if ($this->params->numeric_type == 'dec') {
		$validations[] = 'custom[number]';
	}

	if ($validations) {
		$validate = ' validate[';
		$validate .=  implode(',',$validations);
		$validate .= ']';
	}
}

$html='';

switch ($this->params->custom_price_by_value) {
	case 0:
		$custom_price_info = '';
		$html .= '<input type="text" 
	    	id="'.$viewData[0]->virtuemart_customfield_id.'"
	    	class="'.$class.$validate.' field" 
	        name="customPlugin['.$viewData[0]->virtuemart_customfield_id.']['.$this->_name.'][comment]"
	        value="'.$this->params->default_value.'" style="width:30px;" />';
		break;
	case 1:
		$custom_price_info = '';
		$html .= '<input type="text" 
	    	id="'.$viewData[0]->virtuemart_customfield_id.'"
	    	class="'.$class.$validate.' field" 
	        name="customPlugin['.$viewData[0]->virtuemart_customfield_id.']['.$this->_name.'][comment]"
	        value="'.$this->params->default_value.'" style="width:30px;" />';
		break;
	case 2:
		$html .= '<input type="hidden" 
    	id="'.$viewData[0]->virtuemart_customfield_id.'"
    	class="'.$class.' field" 
	    name="customPlugin['.$viewData[0]->virtuemart_customfield_id.']['.$this->_name.'][comment]"
	    value="'.$this->params->default_value.'" style="width:30px;" />
    	<input type="text" placeholder="0" id="comment_width" class="comment_width" style="width:30px;" value="0">
    	<input type="text" placeholder="0" id="comment_height" class="comment_height" style="width:30px;" value="0">   
    	<input type="button" value="Calc Price" id="cal_button" style="width:100px;">	
    	<input type="button" value="Reset Inputs" id="reset_button" style="width:100px;">';
		break;
	case 3:
		$custom_price_info = '';
		$range_values = $this->params->range_values;
		$range_values = str_replace('[','',$range_values);
		$range_values = str_replace(']','',$range_values);
		$parts = explode(';',$range_values);
		$currency = CurrencyDisplay::getInstance();
		foreach ($parts as $range) {
			$data = explode(':',$range);
			$minmax = explode('-',$data[0]);
			$min = floatval($minmax[0]);
			$max = floatval($minmax[1]);
			$custom_price_for_range = floatval($data[1]);
			$custom_price_for_range = $currency->priceDisplay($custom_price_for_range);
			$custom_price_info .= $min.'~'.$max.':  x '.$custom_price_for_range.'<br>';
		}
		$html .= '<input type="text" 
	    	id="'.$viewData[0]->virtuemart_customfield_id.'"
	    	class="'.$class.$validate.' field" 
	        name="customPlugin['.$viewData[0]->virtuemart_customfield_id.']['.$this->_name.'][comment]"
	        value="'.$this->params->default_value.'" style="width:30px;" />';
		break;
}

$document = JFactory::getDocument();
?>
    <?php echo $html; ?>
<?php
	// preventing 2 x load javascript
	static $numberinputjs;
	if (defined('numberinputjs')) return true;
	define('numberinputjs',true);
	//javascript to update price
	$js = '
		jQuery(document).ready( function($) {
			var form = jQuery(".vmcustom-numberinput").parents("form");
			form.addClass("form-validate");
			if (!form.attr("id")) {
				//Make sure the form has an id for validation purpose
				form.attr("id","add-to-cart");
			}
			form.validationEngine("attach");
			$( "#cal_button" ).click(function() {
				$(".vmcustom-numberinput").val($(".comment_width").val() +","+ $(".comment_height").val());
				formProduct = $(".vmcustom-numberinput").parents("form.product");
				var id = $(".vmcustom-numberinput").attr(\'id\');
				console.log(id);
				virtuemart_product_id = formProduct.find(\'input[name="virtuemart_product_id[]"]\').val();
				Virtuemart.setproducttype(formProduct,virtuemart_product_id);
			});
			
			$( "#reset_button" ).click(function() {
				$(".comment_width").val("0");
				$(".comment_height").val("0");
			  	$(".vmcustom-numberinput").val("0,0");
			});
			
			jQuery(".comment_width")
			  .blur(function() {
					if (this.value === \'\') {
						this.value = "0";
					}
			 });
			 	 
			jQuery(".comment_height")
			  .blur(function() {
					if (this.value === \'\') {
						this.value = "0";
					}
			 }); 	 
			
			jQuery(".vmcustom-numberinput")
			  .blur(function() {
					if (this.value === \'\') {
						this.value = this.defaultValue;
					}
			 })
			  .keyup(function() {
					formProduct = $(this).parents("form.product");
					var id = $(this).attr(\'id\');
					console.log(id);
					virtuemart_product_id = formProduct.find(\'input[name="virtuemart_product_id[]"]\').val();
					Virtuemart.setproducttype(formProduct,virtuemart_product_id);
					var result = $(this).val();
			  });
			  ';
			  if ($this->params->custom_price_by_value==1) {
			  	$js .= "$('input#".$viewData[0]->virtuemart_customfield_id."').parents('span.product-field-display').find('span.price-plugin').before(' x ');";
			  }
			  if ($this->params->custom_price_by_value==2) {
			  	$js .= "$('input#".$viewData[0]->virtuemart_customfield_id."').parents('span.product-field-display').find('div.price-plugin').html('');";
			  }
			  if ($this->params->custom_price_by_value==3) {
			  	$js .= "$('input#".$viewData[0]->virtuemart_customfield_id."').parents('span.product-field-display').find('span.price-plugin').html('".$custom_price_info."');";
			  }
		$js .= '	  
		});
	';
	$document->addScriptDeclaration($js);
