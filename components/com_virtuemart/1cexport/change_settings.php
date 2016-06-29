<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: change_settings.php - Модуль изменения настроек
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

if (isset($_REQUEST['act']))
{
	if($_REQUEST['act'] == "save")
	{
		if (isset($_POST['VM_FORM']) and $_POST['VM_FORM'] == 'True')
		{
			$sPathFile = JPATH_BASE_1C . DS . 'system' .DS . 'config.php';
			
			if (is_file($sPathFile))
			{
				$file="<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: system/config.php - Настройки системы
// Автор оригинала: Дуденков М.В. (email: mihail@termservis.ru)
// Помогали разрабатывать:	Alexandr Datsiuk
//							Павел Михнев 
//                          CALEORT
// Авторские права: Использовать, а также распространять данный скрипт
// 					разрешается только с разрешением автора скрипта
//***********************************************************************\r\n
if ( !defined( 'VM_1CEXPORT' ) )
{
	echo \"<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.\";
	die();
}\r\n
\$config = array();\r\n\r\n";
			
				foreach($_POST as $key => $value) 
				{
					$file.="\$config['".$key."'] = \"".$value."\";\r\n";
				}
				$file.="\r\n?>";
				$fp=fopen($sPathFile,"w+");
				fwrite($fp,$file);
				fclose($fp);
			}
			
			$logs_http[] = "<strong>Настройки сохранены</strong>";
			
			$http_content .= '
			<META HTTP-EQUIV="Refresh" CONTENT="3; URL=vmshop_1c.php?mode=checkauth">';
						
			$http_content .= '
				<center><strong>Процесс выгрузки:</strong><br></center>
				<ul type="circle">
			';
						
			foreach($logs_http as $logs_pr)
			{
				$http_content .= '
					<li>'.$logs_pr.'</li>
				';
			}
			
			$http_content .= '
				</ul>
			';
			
			$http_content .= '
				<br><br><hr size="2"><center><a href="vmshop_1c.php?mode=checkauth">Вернуться назад</a> или дождитесь автоматического возврата через 5 секунд</center>
			';
		}
	}
}
?>