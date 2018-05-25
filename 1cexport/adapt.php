<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: adapt.php - Адаптация VMSHOP 2.x и 1.1.9

//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	print "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

if (VM_LANG == 'RU')
{
	$lang_1c[0] = "Цвет";
	$lang_1c[1] = "Ширина";
	$lang_1c[2] = "Глубина";
	$lang_1c[3] = "Высота";	
}
elseif (VM_LANG == 'UA')
{
	$lang_1c[0] = "Колір";
	$lang_1c[1] = "Ширина";
	$lang_1c[2] = "Глибина";
	$lang_1c[3] = "Висота";	
}
elseif (VM_LANG == 'EN')
{
	$lang_1c[0] = "Color";
	$lang_1c[1] = "Width";
	$lang_1c[2] = "Depth";
	$lang_1c[3] = "Height";	
}

if (VM_VERVM == '2')
{
	$id_admin = str_replace("\n", "", $id_admin);
	
	$dba['userfield_db'] = DBBASE."_userfields";
	$dba['category_db'] = DBBASE."_categories";
	$dba['category_xref_db'] = DBBASE."_category_categories";
	$dba['manufacturer_db'] = DBBASE."_manufacturers";
	$dba['manufacturer_category_db'] = DBBASE."_manufacturercategories";
	$dba['product_db'] = DBBASE."_products";
	$dba['product_category_xref_db'] = DBBASE."_product_categories";
	$dba['product_mf_xref_db'] = DBBASE."_product_manufacturers";
	$dba['product_files_db'] = DBBASE."_medias";
	
	$dba['product_price_db'] = DBBASE."_product_prices";
	$dba['product_product_type_xref_db'] = DBBASE."_product_relations";
	$dba['shopper_group_db'] = DBBASE."_shoppergroups";
	
	$dba['order_user_info_db'] = DBBASE."_order_userinfos";
	$dba['order_item_db'] = DBBASE."_order_items";
	
	$dba['tax_rate_db'] = DBBASE."_calcs";
	$dba['tax_cat_db'] = DBBASE."_calc_categories";
	$dba['tax_shopgr_db'] = DBBASE."_calc_shoppergroups";
	
	$dba['pristavka'] = "virtuemart_";
	$dba['modifdate'] = "`modified_on` = '".date ('Y-m-d H:i:s')."', `modified_by` = '".$id_admin."'";
	$dba['createdate'] = "`created_on` = '".date ('Y-m-d H:i:s')."', `created_by` = '".$id_admin."'";
	$dba['tax_rate_id_t'] = "virtuemart_calc_id";
	$dba['tax_rate_value_t'] = "calc_value";
	$dba['tax_rate_name_t'] = "calc_name";  //SirPiter добавлено для определения расчета НДС по имени
	$dba['shopper_group_id_t'] = "virtuemart_shoppergroup_id";
	$dba['product_files_t'] = "_medias";
	
	if(VM_VERVM_S == 'F')
	{
		$dba['category_ln_db'] = DBBASE."_categories".LANG;
		$dba['manufacturer_ln_db'] = DBBASE."_manufacturers".LANG;
		$dba['manufacturer_category_ln_db'] = DBBASE."_manufacturercategories".LANG;
		$dba['product_ln_db'] = DBBASE."_products".LANG;
	}
	
	$dba['customs_db'] = DBBASE."_customs";
	$dba['customfields_db'] = DBBASE."_product_customfields";
}
else
{
	$dba['userfield_db'] = DBBASE."_userfield";
	$dba['category_db'] = DBBASE."_category";
	$dba['category_xref_db'] = DBBASE."_category_xref";
	$dba['manufacturer_db'] = DBBASE."_manufacturer";
	$dba['manufacturer_category_db'] = DBBASE."_manufacturer_category";
	$dba['product_db'] = DBBASE."_product";
	$dba['product_category_xref_db'] = DBBASE."_product_category_xref";
	$dba['product_mf_xref_db'] = DBBASE."_product_mf_xref";
	$dba['product_files_db'] = DBBASE."_product_files";
	
	$dba['product_price_db'] = DBBASE."_product_price";
	$dba['product_product_type_xref_db'] = DBBASE."_product_product_type_xref";
	$dba['shopper_group_db'] = DBBASE."_shopper_group";
	
	$dba['order_user_info_db'] = DBBASE."_order_user_info";
	$dba['order_item_db'] = DBBASE."_order_item";
	
	$dba['tax_rate_db'] = DBBASE."_tax_rate";
	
	$dba['pristavka'] = "";
	$dba['modifdate'] = "`mdate` = '".time ()."'";
	$dba['createdate'] = "`cdate` = '".time ()."'";
	$dba['tax_rate_id_t'] = "tax_rate_id";
	$dba['tax_rate_value_t'] = "tax_rate";
	$dba['shopper_group_id_t'] = "shopper_group_id";
	
	$dba['customs_db'] = DBBASE."_product_attribute";
	$dba['customfields_db'] = DBBASE."_product_attribute";
}

$dba['product_to_1c_db'] = DBBASE."_product_to_1c";
$dba['category_to_1c_db'] = DBBASE."_category_to_1c";
$dba['cashgroup_to_1c_db'] = DBBASE."_cashgroup_to_1c";
$dba['manufacturer_to_1c_db'] = DBBASE."_manufacturer_to_1c";
?>