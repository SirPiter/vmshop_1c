<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: system/config.php - Настройки системы

//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

$config = array();

$config['VM_FORM'] = "True";
$config['VM_CODING'] = "UTF-8";
$config['VM_DB'] = "no";
$config['VM_ZIP'] = "yes";
$config['VM_ZIPSIZE'] = "16480000";
$config['VM_LOG'] = "time";
$config['VM_LANG'] = "RU";
$config['VM_CAT_IMG'] = "no";
$config['VM_CAT_RAND'] = "r";
$config['VM_NDS'] = "yes";
$config['VM_NDS_COUNTRY'] = "RUS";
$config['VM_POSTAVKA_E'] = "yes";
$config['VM_POSTAVKA'] = "3-5d.gif";
$config['VM_POSTAVKA_TIME'] = "432000";
$config['VM_TBN_H'] = "600";
$config['VM_TBN_W'] = "600";
$config['VM_JPG'] = "no";
$config['VM_TBN_RED'] = "255";
$config['VM_TBN_GREEN'] = "255";
$config['VM_TBN_BLUE'] = "255";
$config['VM_TBN_QTY'] = "80";
$config['VM_LIST_CAT'] = "5";
$config['VM_DEF_CASHGR'] = "НДС";
$config['VM_MANUFACTURE'] = "производитель";
$config['VM_USER_SHOP'] = "no";
$config['VM_CLIENT'] = "1";
$config['VM_NDS_SHIP'] = "Без НДС";

?>