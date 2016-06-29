<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: import.php - Импорт содержимого файлов

//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

// Проверяем на наличие имени файла
if( file_exists ( JPATH_BASE_PICTURE. DS . $_REQUEST['filename'] )) 
{
	$importFile = JPATH_BASE_PICTURE. DS . $_REQUEST['filename'];
} 
else 
{
	$logs_http[] = "<strong><font color='red'>Неудача:</font></strong> Файл ".$_REQUEST['filename']." отсутствует!";	
	$log->addEntry ( array ('comment' => 'Этап 4) Неудача: Файл '.$_REQUEST['filename'].' отсутствует!') );
	echo "failure\n";
	echo "ERROR 10: No file " . $_REQUEST['filename'];
	return 0;
}



require_once(JPATH_BASE_1C .DS.'importsale_xml.php');


if(defined( 'VM_SITE' ))
{
	if ($die)
	{
		$enable = ' disabled="disabled"';
	}
	else
	{
		$enable = '';
	}
	
	
		$form = 'index.php';
		$button = '<input type="submit" value="Закончить"'.$enable.'>';
	
	$http_content .= '	
	<script language="JavaScript">
	<!-- hide
	
	function openWin3() {
	  myWin= open("",  
		"width=800,height=600,status=no,toolbar=no,menubar=yes");
	
	  // открыть объект document для последующей печати 
	  myWin.document.open();
	  
	  // генерировать новый документ 
	  myWin.document.write("<html><head><title>Буфер");
	  myWin.document.write("</title></head><body>");
	  myWin.document.write("<center><font size=+3>");
	  myWin.document.write("Приведенный текст ниже нужно выделить и скопировать ");
	  myWin.document.write("</font></center>");
	  myWin.document.write("<textarea name=\"buf\" style=\"width: 100%; height: 90%\" readonly>[code]\n");
	';

	foreach($logs_http as $logs_wind)
	{
		$logs_wind = str_replace("<strong>", "[b]", $logs_wind);
		$logs_wind = str_replace("</strong>", "[/b]", $logs_wind);
		$logs_wind = str_replace("<font color='red'>", "[color=red]", $logs_wind);
		$logs_wind = str_replace("</font>", "[/color]", $logs_wind);
		$logs_wind = str_replace("\"", "&quot;", $logs_wind);
		
		$http_content .= '
		myWin.document.write("'.$logs_wind.'\n");
		';
	}
	
	$http_content .= '  
	  myWin.document.write("[/code]</textarea>");
	  myWin.document.write("</body></html>");
	
	  // закрыть документ - (но не окно!)
	  myWin.document.close();  
	}
	
	// -->
	</script>
	';	

	$http_content .= '
				<form action="'.$form.'" enctype="multipart/form-data" method="post">
					<input type="hidden" name="mode" value="import">
					<input type="hidden" name="filename" value="offers.xml">
					<center>'.$button.' &nbsp; &nbsp; <input type=button value="Скопировать лог" onClick="openWin3()"></center>
					<hr size="2"><br>
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
				<hr size="2"><br>
					<center>'.$button.' &nbsp; &nbsp; <input type=button value="Скопировать лог" onClick="openWin3()"></center>
				</form>
	';
}
?>