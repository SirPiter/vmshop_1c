<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: http.php - Модуль работы скрипта через сайт
// Автор оригинала: Дуденков М.В. (email: mihail@termservis.ru)
// Помогали разрабатывать:	Alexandr Datsiuk
//							Павел Михнев
//                          CALEORT
// Авторские права: использовать, а также распространять данный скрипт
//                  разрешается только с разрешением автора скрипта
//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	print "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

$templ = "";
$logs_http = array();
$die = false;
$http_content = "";

if (VM_HTTP_VERS == 1)
{
	$brow = $_SERVER['HTTP_USER_AGENT'];
	
	$browsers = array ("MSIE","Firefox","Presto","Chrome","Safari");
		
	for($i = 0; $i < count($browsers); $i++) 
	{
		if(strpos($brow,$browsers[$i])) 
		{
			$templ .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
		<head>
			<title>Выгрузка XML в Virtuemart</title>
			<meta http-equiv="content-type" content="text/html; charset=utf-8">
		</head>
		<body bgcolor="#F7F7E8">';
			
			if(!isset($_SERVER['PHP_AUTH_USER']))
			{
				require_once(JPATH_BASE_1C .DS.'form'.DS.'login_form.php');
			}
			else
			{
				define ( 'VM_SITE', true );
				if (!isset($_REQUEST['mode']))
				{
					$templ .= '
				<META HTTP-EQUIV="Refresh" CONTENT="0; URL=vmshop_1c.php?mode=checkauth">';
				
				}
				else
				{
					$templ .= '
			[content]';
				}
			}
			$templ .= '
		</body>
	</html>
	';		
			break;
		}
	} 
}
?>