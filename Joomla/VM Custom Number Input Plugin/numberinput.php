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

if (!class_exists('vmCustomPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmcustomplugin.php');

$document = JFactory::getDocument();

$style = "
	table.range_val_table tr th{
		background-color: grey;
		width : 23px;
		height: 20px;
		font-size:12px;
		border: 1px solid black !important;
	}
	table.range_val_table td{
		background-color: white;
		width : 23px;
		height: 20px;
		font-size:12px;
		border: 1px solid black !important;
	}";
$document->addStyleDeclaration($style);

class plgVmCustomNumberinput extends vmCustomPlugin {


	function __construct(& $subject, $config) {
		parent::__construct($subject, $config);

		$this->loadLanguage();

		$varsToPush = array(	'numeric_type'=>array('int','string'),
								'custom_price_by_value'=>array(1,'int'),
								'range_values'=>array('','string'),
								'min_value'=>array(0.0,'number'),
								'max_value'=>array(0.0,'number'),
								'default_value'=>array(0,'number')
		);

		$this->setConfigParameterable('custom_params',$varsToPush);
	
	}

	// get product param for this plugin on edit
	function plgVmOnProductEdit($field, $product_id, &$row,&$retValue) {
		if ($field->custom_element != $this->_name) return '';
		$this->parseCustomParams($field);

		$numeric_type_options[] = JHTML::_ ('select.option', 'int', JText::_ ('VMCUSTOM_NUMBERINPUT_NUMERIC_TYPE_INT'));
		$numeric_type_options[] = JHTML::_ ('select.option', 'dec', JText::_ ('VMCUSTOM_NUMBERINPUT_NUMERIC_TYPE_DEC'));

		$price_options[] = JHTML::_ ('select.option', 0, JText::_ ('VMCUSTOM_NUMBERINPUT_PRICE_BY_INPUT'));
		$price_options[] = JHTML::_ ('select.option', 1, JText::_ ('VMCUSTOM_NUMBERINPUT_PRICE_BY_VALUE'));
		$price_options[] = JHTML::_ ('select.option', 2, JText::_ ('VMCUSTOM_NUMBERINPUT_PRICE_FOR_VALUE_RANGE'));
		$price_options[] = JHTML::_ ('select.option', 3, JText::_ ('VMCUSTOM_NUMBERINPUT_PRICE_TIMES_VALUE_RANGE'));
		$price_options[] = JHTML::_ ('select.option', 4, JText::_ ('VMCUSTOM_NUMBERINPUT_CUSTOM_FUNCTION'));
		
		$html ='
			<fieldset>
				<legend>'. JText::_('VMCUSTOM_NUMBERINPUT') .'</legend>
				<table class="admintable" id="numberinput_'.$row.'">'.
					VmHTML::row('input','VMCUSTOM_NUMBERINPUT_DEFAULT_VALUE','custom_param['.$row.'][default_value]',$field->default_value).
					VmHTML::row('input','VMCUSTOM_NUMBERINPUT_MIN_VALUE','custom_param['.$row.'][min_value]',$field->min_value).
					VmHTML::row('input','VMCUSTOM_NUMBERINPUT_MAX_VALUE','custom_param['.$row.'][max_value]',$field->max_value).
					VmHTML::row ('select', 'VMCUSTOM_NUMBERINPUT_NUMERIC_TYPE', 'custom_param['.$row.'][numeric_type]', $numeric_type_options, $field->numeric_type, VmHTML::validate ('R'),'value' ,'text',false,false).
					VmHTML::row ('select', 'VMCUSTOM_NUMBERINPUT_PRICE_BY_VALUE_OR_INPUT', 'custom_param['.$row.'][custom_price_by_value]', $price_options, $field->custom_price_by_value, 'class="validate[required] custom_price_by_value" rel="'.$row.'"','value' ,'text',false,false).
					VmHTML::row('input','VMCUSTOM_NUMBERINPUT_RANGE_VALUES','custom_param['.$row.'][range_values]',$field->range_values,'class="range_values"').
					'<tr><td colspan=2><div id="range_table"></div></td></tr>'.
					VmHTML::row('input','VMCUSTOM_NUMBERINPUT_CUSTOM_FUNCTION','custom_param['.$row.'][custom_function]',$field->custom_function,'class="custom_function"').
					'<tr class="range_values_tip">
						<td colspan="2">'.JText::_('VMCUSTOM_NUMBERINPUT_RANGE_VALUES_TIP').'</td>
					</tr>
					<tr class="range_set_price_tip">
						<td colspan="2">'.JText::_('VMCUSTOM_NUMBERINPUT_PRICE_FOR_VALUE_RANGE_TIP').'</td>
					</tr>					
					<tr class="range_multiply_price_tip">
						<td colspan="2">'.JText::_('VMCUSTOM_NUMBERINPUT_PRICE_TIMES_VALUE_RANGE_TIP').'</td>
					</tr>					
					<tr class="custom_function_tip">
						<td colspan="2">'.JText::_('VMCUSTOM_NUMBERINPUT_CUSTOM_FUNCTION_TIP').'</td>
					</tr>					
				</table>
			</fieldset>
			<script language="javascript">
				jQuery(document).ready(function($) {
					$(".custom_price_by_value").change(function() {
						var selectedOption = $(this).val();
						if (selectedOption == 2) {
							$("table#numberinput_'.$row.'").find(".range_values").parent("td").parent("tr").show();
							$("table#numberinput_'.$row.'").find("tr.range_values_tip").show();
							$("table#numberinput_'.$row.'").find("tr.range_set_price_tip").show();
							$("table#numberinput_'.$row.'").find("tr.range_multiply_price_tip").hide();
							$("table#numberinput_'.$row.'").find(".custom_function").parent("td").parent("tr").hide();
							$("table#numberinput_'.$row.'").find("tr.custom_function_tip").hide();
						} else if (selectedOption == 3) {
							$("table#numberinput_'.$row.'").find(".range_values").parent("td").parent("tr").show();
							$("table#numberinput_'.$row.'").find("tr.range_values_tip").show();
							$("table#numberinput_'.$row.'").find("tr.range_set_price_tip").hide();
							$("table#numberinput_'.$row.'").find("tr.range_multiply_price_tip").show();
							$("table#numberinput_'.$row.'").find(".custom_function").parent("td").parent("tr").hide();
							$("table#numberinput_'.$row.'").find("tr.custom_function_tip").hide();
						} else if (selectedOption == 4) {
							$("table#numberinput_'.$row.'").find(".range_values").parent("td").parent("tr").hide();
							$("table#numberinput_'.$row.'").find("tr.range_values_tip").hide();
							$("table#numberinput_'.$row.'").find("tr.range_set_price_tip").hide();
							$("table#numberinput_'.$row.'").find("tr.range_multiply_price_tip").hide();
							$("table#numberinput_'.$row.'").find(".custom_function").parent("td").parent("tr").show();
							$("table#numberinput_'.$row.'").find("tr.custom_function_tip").show();
						} else {
							$("table#numberinput_'.$row.'").find(".range_values").parent("td").parent("tr").hide();
							$("table#numberinput_'.$row.'").find("tr.range_values_tip").hide();
							$("table#numberinput_'.$row.'").find("tr.range_set_price_tip").hide();
							$("table#numberinput_'.$row.'").find("tr.range_multiply_price_tip").hide();
							$("table#numberinput_'.$row.'").find(".custom_function").parent("td").parent("tr").hide();
							$("table#numberinput_'.$row.'").find("tr.custom_function_tip").hide();
						}
					});
					$(".custom_price_by_value").trigger("change");
					var init_data = "";
					init_data += "'.$field->range_values.'";
					if (init_data == "") {
						init_data = "[0-0]:[0-0]:0";
					}
					init_data = init_data.replace(/\[/g,"");
					init_data = init_data.replace(/\]/g,"");
					var array_data = init_data.split(";");
					var temp_width = (array_data[0].split(":"))[0];
					var width_count = 0;
										
					for (width_count = 1; width_count < array_data.length ; width_count++) {
						var parse_unit = (array_data[width_count].split(":"))[0];
						if (temp_width == parse_unit)
							break;
					}

 					var height_count = array_data.length / width_count;
					
					var row_data = [];
					var col_data = [];
					for (p = 0; p < width_count; p++) {
						 row_data[p] = (((array_data[p].split(":"))[0]).split("-"))[0];
					}					
					for (p = 0; p < array_data.length; p += width_count) {
						 col_data[p / width_count] = (((array_data[p].split(":"))[1]).split("-"))[0];	
					}
					
					var inner_data = [];
					
					for (p = 0; p < array_data.length; p++) {
						inner_data[p] = (array_data[p].split(":"))[2];
					}					
					var row_init=[160,170,180,190,200];
					var col_init=[150,210,270,330];
					
					var str = "<table class=range_val_table id=range_val_table>";
					for(i=0; i<21; i++){
						str += "<tr>";
					    for (j=0; j<21; j++) {
							if (i == 0 && j == 0)
								str += "<th></th>";
							else if (i == 0 && j > 0 && j <= width_count)
								str += "<th>"+row_data[j - 1]+"</th>";
							else if (i == 0 && j > width_count)
								str += "<th></th>"
							else if (j == 0 && i > 0 && i <= height_count)
								str += "<th>"+col_data[i - 1]+"</th>";
							else if (j == 0 && i > height_count)
								str += "<th></th>"
							else{
								if (i >= 1 && i <= height_count &&
									j >= 1 && j <= width_count) {
									str += "<td>"+inner_data[(i - 1)*width_count + (j - 1)]+"</td>";
								}
								else {
									str += "<td></td>";
								}
							}
						}
						str += "</tr>";
					}
					
					str += "</table>";
					$("#range_table").append(str);	
					
					$(function () {
					 	$("#range_val_table tr th").dblclick(function () {
						    var OriginalContent = $(this).text();
						    $(this).addClass("cellEditing");
						    $(this).html("<input type=text value="+OriginalContent+">");
						    $(this).children().first().focus();
						   	$(this).children().first().keypress(function (e) {
						        if (e.which == 13) {
						            var newContent = $(this).val();
						            $(this).parent().text(newContent);
						            $(this).parent().removeClass("cellEditing");
						            myLoadData();
						        }
						    });
						 	$(this).children().first().blur(function(){
						 		var newContent = $(this).val();
							    $(this).parent().text(newContent);
							   	$(this).parent().removeClass("cellEditing");
							   	myLoadData();
						 	});
					 	});
					 	$("#range_val_table tr td").dblclick(function () {
						    var OriginalContent = $(this).text();
						    $(this).addClass("cellEditing");
						    $(this).html("<input type=text value="+OriginalContent+">");
						    $(this).children().first().focus();
						   	$(this).children().first().keypress(function (e) {
						        if (e.which == 13) {
						            var newContent = $(this).val();
						            $(this).parent().text(newContent);
						            $(this).parent().removeClass("cellEditing");
						            myLoadData();
						        }
						    });
						 	$(this).children().first().blur(function(){
						 		var newContent = $(this).val();
							    $(this).parent().text(newContent);
							   	$(this).parent().removeClass("cellEditing");
							   	myLoadData();
						 	});
					 	});					 	
					});
					function myLoadData() {
						var oTable = document.getElementById("range_val_table");
						var steps_width = "";
						var steps_height = "";
					    var rowLength = oTable.rows.length;
					    for (i = 0; i < rowLength; i++){
					       var oCells = oTable.rows.item(i).cells;
					       var cellLength = oCells.length;
					       for(var j = 0; j < cellLength; j++){
								var cellVal = oCells.item(j).innerHTML;
								if (i == 0 && j > 0 && cellVal != "") {
									steps_width += cellVal + ";";
								}
								if (j == 0 && i > 0 && cellVal != "") {
									steps_height += cellVal + ";";
								}
					       }
					    }
					    var steps_width_array = steps_width.split(";");
					    var steps_height_array = steps_height.split(";");
					    var row_count = steps_width_array.length - 1;
					    var col_count = steps_height_array.length - 1;

					    for (i = 0; i < row_count; i++) {
					    	if (i < row_count - 1)
					    		steps_width_array[i] = "[" + steps_width_array[i]+ "-" + steps_width_array[i + 1] + "]";
					    	if (i == row_count - 1) 
					    		steps_width_array[i] = "[" + steps_width_array[i]+ "-" + steps_width_array[i] + "]";
					    }
					    for (j = 0; j < col_count; j++) {
					    	if (j < col_count - 1)		    	
					    		steps_height_array[j] = "[" + steps_height_array[j]+ "-" + steps_height_array[j + 1] + "]";
					    	if (j == col_count - 1) 
					    		steps_height_array[j] = "[" + steps_height_array[j]+ "-" + steps_height_array[j] + "]";
					    }
					    
					    var parseData = "";
						for (i = 1; i <= col_count; i++){
							var oCells = oTable.rows.item(i).cells;
							for (j = 1 ; j <= row_count; j++) {
								var cellVal = oCells.item(j).innerHTML;
								parseData += steps_width_array[j - 1] + ":" + steps_height_array[i - 1] + ":" + cellVal + ";";
							} 
					    }
					    parseData = parseData.substring(0,parseData.length-1);
					    $(".range_values").val(parseData); 
					}
				});
			</script>
			';
		$retValue .= $html;
		$row++;
		return true ;
	}

	/**
	 * @ idx plugin index
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::onDisplayProductFE()
	 * @author Patrick Kohl
	 * eg. name="customPlugin['.$idx.'][comment] save the comment in the cart & order
	 */
	function plgVmOnDisplayProductVariantFE($field,&$idx,&$group) {
		// default return if it's not this plugin
		 if ($field->custom_element != $this->_name) return '';
		$this->getCustomParams($field);
		$group->display .= $this->renderByLayout('default',array($field,&$idx,&$group ) );

		return true;
//         return $html;
    }
	//function plgVmOnDisplayProductFE( $product, &$idx,&$group){}
	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnViewCartModule()
	 * @author Patrick Kohl
	 */
	function plgVmOnViewCartModule( $product,$row,&$html) {
		return;
		//return $this->plgVmOnViewCart($product,$row,$html);
    }

	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnViewCart()
	 * @author Patrick Kohl
	 */
	function plgVmOnViewCart($product,$row,&$html) {
		if (empty($product->productCustom->custom_element) or $product->productCustom->custom_element != $this->_name) return '';
		if (!$plgParam = $this->GetPluginInCart($product)) return '' ;

		foreach($plgParam as $k => $item){

			if(!empty($item['comment'])){
				if($product->productCustom->virtuemart_customfield_id==$k){
					$html .='<span>'.JText::_($product->productCustom->custom_title).' '.$item['comment'].'</span>';
				}
			}
		 }

		return true;
    }


	/**
	 *
	 * vendor order display BE
	 */
	function plgVmDisplayInOrderBE($item, $row, &$html) {
		if(!empty($productCustom)){
			$item->productCustom = $productCustom;
		}
		if (empty($item->productCustom->custom_element) or $item->productCustom->custom_element != $this->_name) return '';
		$this->plgVmOnViewCart($item,$row,$html); //same render as cart
    }

	/**
	 *
	 * shopper order display FE
	 */
	function plgVmDisplayInOrderFE($item, $row, &$html) {

		if (empty($item->productCustom->custom_element) or $item->productCustom->custom_element != $this->_name) return '';
		$this->plgVmOnViewCart($item,$row,$html); //same render as cart
    }

	/**
	 * We must reimplement this triggers for joomla 1.7
	 * vmplugin triggers note by Max Milbers
	 */
	public function plgVmOnStoreInstallPluginTable($psType) {
		//Should the textinput use an own internal variable or store it in the params?
		//Here is no getVmPluginCreateTableSQL defined
// 		return $this->onStoreInstallPluginTable($psType);
	}


	function plgVmDeclarePluginParamsCustom($psType,$name,$id, &$data){
		return $this->declarePluginParams('custom', $name, $id, $data);
	}

	function plgVmSetOnTablePluginParamsCustom($name, $id, &$table){
		return $this->setOnTablePluginParams($name, $id, $table);
	}

	/**
	 * Custom triggers note by Max Milbers
	 */
	function plgVmOnDisplayEdit($virtuemart_custom_id,&$customPlugin){
		return $this->onDisplayEditBECustom($virtuemart_custom_id,$customPlugin);
	}

	public function plgVmCalculateCustomVariant($product, &$productCustomsPrice,$selected){
		if ($productCustomsPrice->custom_element !==$this->_name) return ;
		$customVariant = $this->getCustomVariant($product, $productCustomsPrice,$selected);

		$this->getCustomParams($productCustomsPrice);
		$plgParams = $this->params;
		if ($plgParams->custom_price_by_value == 2) {
			if (!empty($customVariant['comment'])) {
				$value = explode(',',$customVariant['comment']);
				$value_width = $value[0];
				$value_height = $value[1];

				if ($plgParams->numeric_type =='dec') {
					$value_width = floatval($value_width);
					$value_height = floatval($value_height);
				} else {
					$value_width = intval($value_width);
					$value_height = intval($value_height);
				}
				
				$custom_price_for_range = 0;
				$range_values = $plgParams->range_values;

				$range_values = str_replace('[','',$range_values);
				$range_values = str_replace(']','',$range_values);
				$parts = explode(';',$range_values);

				foreach ($parts as $range) {
					$data = explode(':',$range);
					$minmax_width = explode('-',$data[0]);
					$min_width = $minmax_width[0];
					$max_width = $minmax_width[1];
					$minmax_height = explode('-',$data[1]);					
					$min_height = $minmax_height[0];
					$max_height = $minmax_height[1];
					if (($value_width >= $min_width && $value_width < $max_width ||
						$min_width == $max_width &&  $value_width == $max_width) && 
						($value_height >= $min_height && $value_height < $max_height ||
						$min_height == $max_height && $value_height == $max_height)) {
						if (strpos($data[2],",") != false) {
							$data[2] = str_replace(',','.',$data[2]);
						}
						$custom_price_for_range = floatval($data[2]);
						$productCustomsPrice->custom_price = $custom_price_for_range ;
						return true;
					}
					else {
						$productCustomsPrice->custom_price = 0;
					}
				}
			}
		
		} else if ($plgParams->custom_price_by_value == 3) {
			if (!empty($customVariant['comment'])) {
				$value = $customVariant['comment'];
				if ($plgParams->numeric_type =='dec') {
					$value = floatval($value);
				} else {
					$value = intval($value);
				}
				$custom_price_for_range = 0;
				$range_values = $plgParams->range_values;
				$range_values = str_replace('[','',$range_values);
				$range_values = str_replace(']','',$range_values);
				$parts = explode(';',$range_values);
				foreach ($parts as $range) {
					$data = explode(':',$range);
					$minmax = explode('-',$data[0]);
					$min = floatval($minmax[0]);
					$max = floatval($minmax[1]);
					if ($value >= $min && $value <= $max) {
						$custom_price_for_range = floatval($data[1]);
						$productCustomsPrice->custom_price = $value * $custom_price_for_range ;
						return true;
					}
				}
			}
		} else if ($plgParams->custom_price_by_value == 4) {
			if (!empty($customVariant['comment'])) {
				$customFiles = JFolder::files(JPATH_PLUGINS.'/vmcustom/numberinput/custom','.php',false,true);
				foreach ($customFiles as $file) {
					try {
						include_once($file);
					} catch (Exception $e) {
					}
				}
				$value = $customVariant['comment'];
				if ($plgParams->numeric_type =='dec') {
					$value = floatval($value);
				} else {
					$value = intval($value);
				}
				$custom_price_for_range = 0;
				$custom_function = $plgParams->custom_function;
				if (is_callable($custom_function)) {
					$productCustomsPrice->custom_price = call_user_func($custom_function,$value);
				} else {
					$productCustomsPrice->custom_price = 0;
				}
			}
		} else if (!empty($plgParams->custom_price)) {
			//TODO adding % and more We should use here $this->interpreteMathOp
			// eg. to calculate the price * comment text length

			if (!empty($customVariant['comment'])) {

				if ($plgParams->custom_price_by_value ==1) {
					$value = $customVariant['comment'];
					if ($plgParams->numeric_type =='dec') {
						$value = floatval($value);
					} else {
						$value = intval($value);
					}
					$productCustomsPrice->custom_price = $value * $productCustomsPrice->custom_price ;
				} else {
					$productCustomsPrice->custom_price = $productCustomsPrice->custom_price ;
				}
			} else {
				$productCustomsPrice->custom_price = 0.0;
			}

		}
		return true;
	}

	public function plgVmDisplayInOrderCustom(&$html,$item, $param,$productCustom, $row ,$view='FE'){
		$this->plgVmDisplayInOrderCustom($html,$item, $param,$productCustom, $row ,$view);
	}

	public function plgVmCreateOrderLinesCustom(&$html,$item,$productCustom, $row ){
// 		$this->createOrderLinesCustom($html,$item,$productCustom, $row );
	}
	function plgVmOnSelfCallFE($type,$name,&$render) {
		$render->html = '';
	}
	
	public function plgVmOnAddToCart(&$product) {
		//This will do server side validation in case the javascript validation was bypassed
		//Retreive the value sent to the cart
		if (!isset($product->customPlugin)) {
			return;
		}
		$custom_plugin_json = $product->customPlugin;
		$custom_plugins = json_decode($custom_plugin_json);
		$app = JFactory::getApplication();
		$errors = array();
		$db	=& JFactory::getDBO();
		
		foreach ($custom_plugins as $customfield_id => $customfield_value) {
			if (isset($customfield_value->numberinput) && isset($customfield_value->numberinput->comment)) {

				$sql = $db->getQuery(true)
					->select('cfp.*,cf.custom_title')
					->from('#__virtuemart_product_customfields AS cfp')
					->join('INNER', '#__virtuemart_customs AS cf ON (cfp.virtuemart_custom_id = cf.virtuemart_custom_id)')
					->where('cfp.virtuemart_customfield_id = '.$db->q($customfield_id));
				$db->setQuery($sql);
		
				$customfield = $db->loadObject();
				$field_params_json = $customfield->custom_param;
				$custom_params = json_decode($field_params_json);
				$custom_title = $customfield->custom_title;
				
				$value = $customfield_value->numberinput->comment;
				$continueParsing = true;
				
				if (!is_numeric($value)) {
					$plgParams = $this->params;
					if ($plgParams->custom_price_by_value == 2) {
						$continueParsing = true;
					}
					else 
					{
						if ($custom_params->numeric_type == 'int') {
							$errors[] = JText::sprintf('VMCUSTOM_NUMBERINPUT_INT_ERROR', $custom_title );
						} else {
							$errors[] = JText::sprintf('VMCUSTOM_NUMBERINPUT_DEC_ERROR', $custom_title );
						}
						$continueParsing = false;
					}
				} else {
					if ($custom_params->numeric_type == 'dec') {
						$value = floatval($value);
					} else {
						$value = intval($value);
					}
				}
				
				if ($continueParsing) {
					$plgParams = $this->params;
					if ($plgParams->custom_price_by_value != 2) 
					{
						if ($value < $custom_params->min_value) {
							$errors[] = JText::sprintf('VMCUSTOM_NUMBERINPUT_MIN_VALUE_ERROR', $custom_params->min_value, $custom_title );
						}
						if ($value > $custom_params->max_value) {
							$errors[] = JText::sprintf('VMCUSTOM_NUMBERINPUT_MAX_VALUE_ERROR', $custom_params->max_value, $custom_title );
						}
					}
				}
			}
		}
		if (count($errors)) {
			if (VmConfig::get('addtocart_popup',1)) {
				$this->json = new stdClass();
				$this->json->msg = implode('<br/>',$errors);
				$this->json->stat = '2';
				echo json_encode($this->json);
				jExit();
			}
			foreach ($errors as $error) {
				$app->enqueueMessage($error,'error');
			}
			return false;
		}
	}

}

// No closing tag