<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: file.php - Выгрузка файлов или архива и его распаковка
// Автор оригинала: Дуденков М.В. (email: mihail@termservis.ru)
// Помогали разрабатывать:	Alexandr Datsiuk
//							Павел Михнев
//                          CALEORT
// Авторские права: использовать, а также распространять данный скрипт
//                  разрешается только с разрешением автора скрипта
//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}
if(!defined( 'VM_SITE' ))
{
	$filename_to_save = JPATH_BASE_PICTURE . DS . $_REQUEST ['filename'];
	$fnm = $_REQUEST ['filename'];
}
else
{
	$filename_to_save = JPATH_BASE_PICTURE . DS . $_FILES['filename']['name'];
	$fnm = $_FILES['filename']['name'];
	$resultat = false;
}

// Проверяем на наличие имени файла
$log->addEntry ( array ('comment' => 'Этап 3.1) Проверка наличия имени файла ' . $filename_to_save ) );		
if( !isset($fnm) or $fnm == "" ) 
{
	$log->addEntry ( array ('comment' => 'Этап 3.1) Неудача! Отсутствует файл') );
	if(!defined( 'VM_SITE' ))
	{
		echo "failure\n";
		echo "ERROR 10: No file name variable";
	}
	else
	{
		$logs_http[] = "<strong>Отправка файлов</strong> - Неудача! Отсутствует файл";
	}
	if(!defined( 'VM_SITE' ))
	{
		die;
	}
	else
	{
		$die = true;
	}
	
}

$big_zip = false;

if(VM_ZIP == 'no')
{		
		// Проверяем XML или изображения
		if( strpos( $_REQUEST['filename'], 'import_files') !== false ) 
		{
			$path = stristr( $_REQUEST['filename'], 'import_files');
			
			$log->addEntry ( array ('comment' => 'Этап 3.1) Проверяем наличие старых временных папок и файлов') );	
			
			$logs_http[] = "<strong>Отправка файлов</strong> - Проверяем наличие старых временных папок и файлов";	
			
			foreach( explode('/', $path) as $name) 
			{
				if( substr ( $name, - 4 ) != 'jpeg' )
				{
					if($name != 'import_files')
					{
						$name = 'import_files'.DS.$name;
					}
					
					$log->addEntry ( array ('comment' => 'Этап 3.1) Проверяем наличие папки: '.$name) );
					$logs_http[] = "<strong>Отправка файлов</strong> - Проверяем наличие папки: ".$name;	
					if(!is_dir( JPATH_BASE_PICTURE.DS.$name ) ) 
					{
						$log->addEntry ( array ('comment' => 'Этап 3.1) Создаем папку: '.$name) );
						$logs_http[] = "<strong>Отправка файлов</strong> - Создаем папку: ".$name;
						mkdir( JPATH_BASE_PICTURE.DS.$name, 0777, true );
					}
				}
			}

		}
}
else
{
	$file_zip = scandir ( JPATH_BASE_PICTURE.DS );
	foreach ( $file_zip as $filename_zip )
	{
		if(substr ( $filename_zip, - 3 ) == 'zip')
		{
			$zip = zip_open ( JPATH_BASE_PICTURE.DS.$filename_zip );
			if(is_resource($zip))
			{
				zip_close($zip);
				unlink ( JPATH_BASE_PICTURE.DS.$filename_zip );
				$log->addEntry ( array ('comment' => 'Этап 3.1) Старый архив '.$filename_zip.' удален') );
				$logs_http[] = "<strong>Отправка файлов</strong> - Старый архив ".$filename_zip." удален";
			}
		}
	}
}


if(!defined( 'VM_SITE' ))
{
	$filename_to_save = JPATH_BASE_PICTURE . DS . $_REQUEST ['filename'];
}
else
{
	$filename_to_save = JPATH_BASE_PICTURE . DS . $_FILES['filename']['name'];
}
$log->addEntry ( array ('comment' => 'Этап 3.2) Загружаем файл: '.$filename_to_save) );	
$logs_http[] = "<strong>Отправка файлов</strong> - Загружаем файл: ".$filename_to_save;	

// Получаем данные
if(!defined( 'VM_SITE' ))
{
	$DATA = file_get_contents("php://input");
}
else
{
	if (move_uploaded_file($_FILES['filename']['tmp_name'], $filename_to_save)) 
	{
   		$log->addEntry ( array ('comment' => 'Этап 3.2) Файл загружен: '.$filename_to_save) );
		$logs_http[] = "<strong>Отправка файлов</strong> - Файл загружен: ".$filename_to_save;
	} 
	else 
	{
		$log->addEntry ( array ('comment' => 'Этап 3.2) Неудача:</strong> Немогу открыть файл - '.$filename_to_save) );
		$logs_http[] = "<strong>Отправка файлов</strong> - <strong><font color='red'>Неудача:</font></strong> Немогу открыть файл - ".$filename_to_save;
	}
	
	$DATA = '';
}

if(isset ( $DATA ) or $DATA !== false) 
{
	if ($DATA != '')
	{
		$fp = fopen($filename_to_save, "ab");
		if($fp) 
		{
			set_file_buffer ( $fp, 20 );
			
			$result = fwrite($fp, $DATA);
			
			if($result === strlen($DATA))
			{
				$resultat = "success\n";
				
				$log->addEntry ( array ('comment' => 'Этап 3.2) Файл загружен: '.$filename_to_save) );
				
				fclose($fp);
				
				if(!chmod($filename_to_save , 0777))
				{
					$log->addEntry ( array ('comment' => 'Этап 3.2) Неудача: Невозможно применить права - '.$filename_to_save) );
					echo "failure\n";
					if(!defined( 'VM_SITE' ))
					{
						die;
					}
					else
					{
						$die = true;
					}
				}
			}
			else
			{
				$log->addEntry ( array ('comment' => 'Этап 3.2) Неудача: Пустой файл - '.$filename_to_save) );
				echo "failure\n";
				if(!defined( 'VM_SITE' ))
				{
					die;
				}
				else
				{
					$die = true;
				}
			}
		}
		else
		{
			$log->addEntry ( array ('comment' => 'Этап 3.2) Неудача: Немогу открыть файл - '.$filename_to_save) );
			echo "failure\n";
			echo "Can not open file: $filename_to_save\n";
			echo JPATH_BASE_PICTURE.DS;
			if(!defined( 'VM_SITE' ))
			{
				die;
			}
			else
			{
				$die = true;
			}
		}
		
	}
}
else
{
	$log->addEntry ( array ('comment' => 'Этап 3.2) Неудача: Нет файлов для загрузки - '.$filename_to_save) );
	
	if(!defined( 'VM_SITE' ))
	{
		echo "failure\n";
		echo "No data file\n";
	}
	else
	{
		$logs_http[] = "<strong>Отправка файлов</strong> - <strong><font color='red'>Неудача:</font></strong> Нет файлов для загрузки - ".$filename_to_save;
	}
	if(!defined( 'VM_SITE' ))
	{
		die;
	}
	else
	{
		$die = true;
	}
}

if(VM_ZIP == 'yes')
{
	$file_txt = scandir ( JPATH_BASE_PICTURE ); 
	foreach ( $file_txt as $filename_save )
	{
		if (substr ( $filename_save, - 3 ) == 'zip') 
		{
			$zip = zip_open ( JPATH_BASE_PICTURE.DS.$filename_save );
			
			if ($zip) 
			{
				if(!is_resource($zip))
				{
					$big_zip = true;
					$resultat = "success\n";
				}
				else
				{				
					$log->addEntry ( array ('comment' => 'Этап 3.2.а) Разархивирование файла - '.JPATH_BASE_PICTURE.DS.$filename_save ) );
					
					$logs_http[] = "<strong>Отправка файлов</strong> - Разархивирование файла - ".JPATH_BASE_PICTURE.DS.$filename_save;
					
					$logs_http[] = "<strong>Отправка файлов</strong> - Построение структуры папок";
					
					while ( $zip_entry_f = zip_read ( $zip ) ) 
					{
						$name_f = JPATH_BASE_PICTURE.DS . zip_entry_name ( $zip_entry_f );
						
						$path_parts = pathinfo ( $name_f );
						# Создем отсутствующие директории
						if (! is_dir ( $path_parts ['dirname'] )) 
						{
							$log->addEntry ( array ('comment' => 'Этап 3.2.а) Создание директории ' . $path_parts ['dirname'] ) );
							$logs_http[] = "<strong>Отправка файлов</strong> - Создание директории - ".$path_parts ['dirname'];
							mkdir ( $path_parts ['dirname'], 0777, true );
						}
						
					}
					
					zip_close ( $zip );
					
					$logs_http[] = "<strong>Отправка файлов</strong> - Извлечение содержимого архива";
					
					$zip = zip_open ( JPATH_BASE_PICTURE.DS.$filename_save );
					
					while ( $zip_entry = zip_read ( $zip ) ) 
					{
				
						$name = JPATH_BASE_PICTURE.DS . zip_entry_name ( $zip_entry );
						
						if (strpos(zip_entry_name($zip_entry), '.'))
						{
							if (zip_entry_open ( $zip, $zip_entry, "r" )) 
							{
								$buf = zip_entry_read ( $zip_entry, zip_entry_filesize ( $zip_entry ) );
				
								$file = fopen ( $name, "wb" );
								if ($file) 
								{
									fwrite ( $file, $buf );
								} 
								else 
								{
									$log->addEntry ( array ('comment' => 'Этап 3.2.а) Неудача: Невозможно открыть архив - ' . $name ) );
	
									if(defined( 'VM_SITE' ))
									{
										$logs_http[] = "<strong>Отправка файлов</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно открыть архив - ".$name;
									}
									if(!defined( 'VM_SITE' ))
									{
										die;
									}
									else
									{
										$die = true;
									}
								}
								fclose ( $file );
								zip_entry_close ( $zip_entry );
							}
						}
					}
					$big_zip = false;
					zip_close ( $zip );
				}
				
			}
			else 
			{
				$log->addEntry ( array ('comment' => 'Этап 3.2.а) Неудача: Невозможно разархивировать файл - ' . $filename_save ) );
				
				if(!defined( 'VM_SITE' ))
				{
					echo "failure\n";
				}
				else
				{
					$logs_http[] = "<strong>Отправка файлов</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно разархивировать файл - ".$name;
				}
				if(!defined( 'VM_SITE' ))
				{
					die;
				}
				else
				{
					$die = true;
				}
			}
		}
	}
}

if (!$big_zip)
{
	$log->addEntry ( array ('comment' => 'Этап 3.3) Копирование картинок из временного каталога') );
	
	$logs_http[] = "<strong>Отправка файлов</strong> - Копирование картинок из временного каталога";
	
	if(is_dir(JPATH_BASE_PICTURE.DS.'import_files'))
	{
		$file_img_dir = scandir ( JPATH_BASE_PICTURE.DS.'import_files'.DS );
		foreach ( $file_img_dir as $filename_img_dir )
		{
			if (is_dir(JPATH_BASE_PICTURE.DS.'import_files'.DS.$filename_img_dir.DS) & $filename_img_dir != '.' & $filename_img_dir != '..')
			{
				$log->addEntry ( array ('comment' => 'Этап 3.3) Обрабатываем каталог: '.$filename_img_dir) );
				
				$logs_http[] = "<strong>Отправка файлов</strong> - Обрабатываем каталог: ".$filename_img_dir;
				
				$file_img = scandir ( JPATH_BASE_PICTURE.DS.'import_files'.DS.$filename_img_dir.DS );
				
				require_once(JPATH_BASE_1C .DS.'system'.DS.'imgresize.php');
				
				foreach ( $file_img as $filename_img )
				{
					if (substr ( $filename_img, - 4 ) == 'jpeg' or substr ( $filename_img, - 3 ) == 'jpg' or substr ( $filename_img, - 3 ) == 'bmp' or substr ( $filename_img, - 3 ) == 'gif' or substr ( $filename_img, - 3 ) == 'png') 
					{
						$file = JPATH_BASE_PICTURE.DS.'import_files'.DS.$filename_img_dir.DS.$filename_img;
						if (substr ( $filename_img, - 4 ) == 'jpeg')
						{
							$newfile = JPATH_BASE_PICTURE.DS.str_replace(".jpeg", "", $filename_img).".".VM_JPG_S;
						}
						else
						{
							$newfile = JPATH_BASE_PICTURE.DS.$filename_img;
						}
						$log->addEntry ( array ('comment' => 'Этап 3.3) Копирование файла ' . $file .' в ' . $newfile ) );
						
						$logs_http[] = "<strong>Отправка файлов</strong> - Копирование файла: ".$file." в ".$newfile;
						
						if (file_exists ($newfile))
						{
							unlink ( $newfile );
						}
						
						if (!rename($file, $newfile)) 
						{
							$log->addEntry ( array ('comment' => 'Этап 3.3) Неудача: Невозможно скопировать файл - ' . $filename_img ) );
							
							if(!defined( 'VM_SITE' ))
							{
								echo "failure\n";
								echo "Can not copy file: $filename_img\n";
							}
							else
							{
								$logs_http[] = "<strong>Отправка файлов</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно скопировать файл - ".$filename_img;
							}
							if(!defined( 'VM_SITE' ))
							{
								die;
							}
							else
							{
								$die = true;
							}
						}
						else
						{
							//Создаем миниизображения
							if (substr ( $filename_img, - 4 ) == 'jpeg')
							{
								$resize_img = JPATH_BASE_PICTURE.DS."resized".DS.str_replace(".jpeg", "", $filename_img)."_".VM_TBN_H."x".VM_TBN_W.".".VM_JPG_S;
							}
							else
							{
								$meta_img = substr ( $filename_img, - 3 );
								$resize_img = JPATH_BASE_PICTURE.DS."resized".DS.str_replace(".".$meta_img, "", $filename_img)."_".VM_TBN_H."x".VM_TBN_W.".".$meta_img;
							}
							
							if (file_exists ($resize_img))
							{
								unlink ( $resize_img );
							}
							
							/*if (!defined( 'VM_SITE' ))
							{*/
							
								if(!img_resize($newfile, $resize_img, VM_TBN_H, VM_TBN_W, VM_TBN_RED, VM_TBN_GREEN, VM_TBN_BLUE, VM_TBN_QTY))
								{
									$log->addEntry ( array ('comment' => 'Этап 3.3) Неудача: Невозможно создать thumbnails - ' . $resize_img ) );
									echo "failure\n";
									echo "Can not make thumbnails: $resize_img\n";
									

							/*	}
							}
							else
							{
								if(!image_resize($newfile, $resize_img, VM_TBN_H, VM_TBN_W, VM_TBN_QTY))
								{
									$log->addEntry ( array ('comment' => 'Этап 3.3) Неудача: Невозможно создать thumbnails - ' . $resize_img ) );
									*/				
									$logs_http[] = "<strong>Отправка файлов</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно создать thumbnails - ".$resize_img;
									if(!defined( 'VM_SITE' ))
									{
										die;
									}
									else
									{
										$die = true;
									}
								}
							/*}*/
						}
					}
				}
				rmdir (JPATH_BASE_PICTURE.DS.'import_files'.DS.$filename_img_dir);
			}
		}
		rmdir (JPATH_BASE_PICTURE.DS.'import_files');
		$log->addEntry ( array ('comment' => 'Этап 3.3) Папка import_files удалена ' ) );
		
		$logs_http[] = "<strong>Отправка файлов</strong> - Папка import_files удалена";	
	}
	
	$log->addEntry ( array ('comment' => 'Этап 3) Успешно') );
	
	$logs_http[] = "<strong>Отправка файлов</strong> - Успешно";	
}
if($resultat and !defined( 'VM_SITE' ))
{
	echo $resultat.$_REQUEST ['filename'];
}
	
if(defined( 'VM_SITE' ))
{
	$http_content .= '
				<form action="vmshop_1c.php?mode=import&filename=import.xml" enctype="multipart/form-data" method="post">
					<input type="hidden" name="mode" value="import">
					<input type="hidden" name="filename" value="import.xml">
	';
	if (!$die)
	{
		$http_content .= '<input type="submit" value="Далее -> Выгрузка XML">';
	}
	else
	{
		$http_content .= '<input type="button" value="Вернуться назад" onClick="history.back()">';
	}
	$http_content .= '
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
					<center>
	';
	if (!$die)
	{
		$http_content .= '<input type="submit" value="Далее -> Выгрузка XML">';
	}
	else
	{
		$http_content .= '<input type="button" value="Вернуться назад" onClick="history.back()">';
	}
	$http_content .= '</center>
				</form>
	';
}
?>