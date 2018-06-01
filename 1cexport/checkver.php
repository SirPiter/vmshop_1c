<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: checkver.php - Адаптация VMSHOP 2.x и 1.1.9

//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	print "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

//Проверяем версию магазина

// Пока не проверяем - просто устанавливаем. SirPiter

/*

if (!class_exists( 'vmVersion' ))
{
//	require (JPATH_BASE . DS . 'administrator' . DS . 'components' . DS . 'com_virtuemart' . DS . 'version.php');
	//$VMVERSION = explode ( " ", $shortversion )	;
	
// надо попробовать так :
 require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');
 require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'calculationh.php');
 //$version = substr(vmVersion::$RELEASE, 0, 1);
//
	
	
	
	$VMVERSION = vmVersion::$RELEASE;
}
$version = $VMVERSION[1];
$version_status = $VMVERSION[2];
$sh_version = str_replace(".", "", $version);

//if (($sh_version >= "200" and $sh_version <= "299") and $version != "2.0.0-RC-2M" and $version_status == "Final")
if (true)
{
	define ( 'VM_VERVM', '2' );
	define ( 'VM_VERVM_S', 'F' );
	if (VM_LANG == 'RU' or VM_LANG == 'UA')
	{
		define ( 'LANG', '_ru_ru' ); // для россии!!!
	}
	elseif (VM_LANG == 'EN')
	{
		define ( 'LANG', '_en_gb' ); // !!!
	}
//$log->addEntry ( array ('comment' => 'Аматор: Версия 2');
//print 'Аматор: Версия 2';
}
elseif ($version == "1.1.9" and $version_status == "stable")
{
	define ( 'VM_VERVM', '1' );
	define ( 'VM_VERVM_S', 'S' );
}
elseif ($version == "2.0.0-RC-2M" and $version_status == "release candidate") //Candidat 2.0.0-RC-2M
{
	define ( 'VM_VERVM', '2' );
	define ( 'VM_VERVM_S', 'C' );
}
else // <1.1.9
{
	define ( 'VM_VERVM', '1' );
	define ( 'VM_VERVM_S', 'S' );
}


*/

	define ( 'VM_VERVM', '2' );
	define ( 'VM_VERVM_S', 'F' );
	define ( 'LANG', '_ru_ru' ); // для россии!!!

	$version = "3.2.14";
	$version_status = "Release";
	$sh_version = str_replace(".", "", $version);

?>