<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: import_xml.php - Импорт содержимого файла import.xml

//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

$logs_http[] = "<strong>Загрузка товара</strong> - Проверка базы данных совместимости 1с и VMSHOP";
$log->addEntry ( array ('comment' => 'Этап 4.1.1) Проверка базы данных совместимости 1с и VMSHOP') );

// SirPiter $res = $db->setQuery ( 'SHOW COLUMNS FROM "#__'.$dba['product_to_1c_db'].'"' );
$res = $db->setQuery ( 'SHOW COLUMNS FROM #__'.$dba['product_to_1c_db'] ); // SirPiter убраны кавычки 

if( !$db->query($res)) 
{
	$db->setQuery ( 
			'CREATE TABLE 
			`#__'.$dba['product_to_1c_db'].'` ( 
			`product_id` int(10) unsigned NOT NULL,
			`c_id` varchar(255) NOT NULL,
			`tax_id` int(10) unsigned NOT NULL,
			KEY (`product_id`),
			KEY `c_id` (`c_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	 );
	$db->query ();
	
	$logs_http[] = "<strong>Загрузка товара</strong> - База product_to_1c создана";
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База product_to_1c создана') );			
}
else
{
	$logs_http[] = "<strong>Загрузка товара</strong> - База product_to_1c уже существует";
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База product_to_1c уже существует') );	
}

$sql = 'SELECT `tax_id` FROM `#__'.$dba['product_to_1c_db'].'` WHERE 0';
$db->setQuery ( $sql );
$res2 = $db->query ();
if(!$res2)
{
	$sql = 'ALTER TABLE `#__'.$dba['product_to_1c_db'].'` ADD `tax_id` INT( 10 ) unsigned NOT NULL'; 
	$db->setQuery ( $sql );
	$db->query ();
	
	$logs_http[] = "<strong>Загрузка товара</strong> - Недостоющие поля таблицы product_to_1c созданы";
}

//SirPiter $res3 = $db->setQuery ( 'SHOW COLUMNS FROM "#__'.$dba['category_to_1c_db'].'"' );
$res3 = $db->setQuery ( 'SHOW COLUMNS FROM #__'.$dba['category_to_1c_db'] ); // SirPiter убраны кавычки

if( !$db->query($res3)) 
{
	$db->setQuery ( 
			'CREATE TABLE 
			`#__'.$dba['category_to_1c_db'].'` ( 
			`category_id` int(10) unsigned NOT NULL,
 			`c_category_id` varchar(255) NOT NULL,
 			KEY (`category_id`),
 			KEY `c_id` (`c_category_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	 );
	$db->query ();	
	
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База category_to_1c создана') );
	$logs_http[] = "<strong>Загрузка товара</strong> - База category_to_1c создана";			
}
else
{
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База category_to_1c существует') );
	$logs_http[] = "<strong>Загрузка товара</strong> - База category_to_1c существует";	
}

// SirPiter $res4 = $db->setQuery ( 'SHOW COLUMNS FROM "#__'.$dba['manufacturer_to_1c_db'].'"' );
$res4 = $db->setQuery ( 'SHOW COLUMNS FROM #__'.$dba['manufacturer_to_1c_db']); //SirPiter убраны кавычки

if( !$db->query($res3)) 
{
	$db->setQuery ( 
			'CREATE TABLE 
			`#__'.$dba['manufacturer_to_1c_db'].'` ( 
			`manufacturer_id` int(10) unsigned NOT NULL,
 			`c_manufacturer_id` varchar(255) NOT NULL,
 			KEY (`manufacturer_id`),
 			KEY `c_id` (`c_manufacturer_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	 );
	$db->query ();	
	
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База manufacturer_to_1c создана') );
	$logs_http[] = "<strong>Загрузка товара</strong> - База manufacturer_to_1c создана";			
}
else
{
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База manufacturer_to_1c существует') );
	$logs_http[] = "<strong>Загрузка товара</strong> - База manufacturer_to_1c существует";	
}

$importFile = JPATH_BASE_PICTURE. DS . 'import.xml';

$reader = new XMLReader();
$reader->open($importFile);

$product = new XMLReader();

$base = new XMLReader();
$base->open($importFile);


if(!$reader and !$base)
{
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) Неудача: Ошибка открытия XML') );	
	$logs_http[] = "<strong><font color='red'>Неудача:</font></strong> Ошибка открытия XML";
	
	if(!defined( 'VM_SITE' ))
	{
		echo 'failure\n';
	}
	die();
}
else
{
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) XML import.xml загружен') );
	$logs_http[] = "<strong>Загрузка товара</strong> - XML <strong>import.xml</strong> загружен";
}

$data = array();

$CAT = array();

$log->addEntry ( array ('comment' => 'Этап 4.1.1) Базы созданы, переходим к процесу отчистки') );

$logs_http[] = "<strong>Загрузка товара</strong> - Все базы созданы, переходим к процесу отчистки";

while($base->read()) 
{
	if($base->nodeType == XMLReader::ELEMENT) 
	{
		switch($base->name) 
		{
			case 'Каталог':
				require_once(JPATH_BASE_1C .DS.'system'.DS.'clearbase.php');
				clearBase($base->getAttribute("СодержитТолькоИзменения"),'1');
				$modif = $base->getAttribute("СодержитТолькоИзменения");
				
				//$base->next();
				break;
				
			case 'КоммерческаяИнформация':
				$vers_xml = $base->getAttribute("ВерсияСхемы");
				if (substr($vers_xml, 0, 4) == '2.04')
				{
					define ( 'VM_XML_VERS', '204' );
				}
				else
				{
					define ( 'VM_XML_VERS', '203' );
				}
				$log->addEntry ( array ('comment' => 'Этап 4.1.1) Версия схемы XML '.$vers_xml. ' VM_XML_VERS = '.VM_XML_VERS) );
				$logs_http[] = '<strong>Загрузка товара</strong> - Версия схемы XML '.$vers_xml. ' VM_XML_VERS = '.VM_XML_VERS;
				
				//$base->next();
				break;
				
			
				
			
		}
	}
}
$base->close();

if ($modif == 'false')
{
	$log->addEntry ( array ('comment' => 'Этап 4.1.2) Базы отчищены, переходим к процесу создания категорий') );

	$logs_http[] = "<strong>Загрузка товара</strong> - Все базы созданы, переходим к процесу создания категорий";
}


//+Аматор А теперь удалим те медиа, по которым нет данным в товарах. Чистим мусор после ручного удаления картинок с сайта

$sql2 = "SELECT * FROM `#__".DBBASE."_medias` where `file_type` = 'product'";
					$db->setQuery ( $sql2 );
					$rows2 = $db->loadObjectList ();
					foreach ( $rows2 as $row2 ) 
					{
					//continue;	
//+Аматор Проверим, нет ли ссылки на это медиа

							$sql1 = "SELECT * FROM #__".DBBASE."_product_medias where `virtuemart_media_id` = '" . $row2->virtuemart_media_id . "'";
							$db->setQuery ( $sql1 );
							$rows1 = $db->loadObject ();
							if ($rows1) {continue;}

//-Аматор


						$sql_5 = "DELETE FROM `#__".DBBASE."_medias` WHERE `virtuemart_media_id` = '".$row2->virtuemart_media_id."'";
						$db->setQuery ( $sql_5 );
						if (!$db->query ())
						{
							$log->addEntry ( array ('comment' => 'Этап 4.1.3) Неудача: Невозможно очистить медиа, id - ' . $row2->virtuemart_media_id ) );
							$log->addEntry ( array ('comment' => 'Этап 4.1.3) ' . $sql_5 ) );
							if(!defined( 'VM_SITE' ))
							{
								echo 'failure\n';
								echo 'error mysql update\n';
								echo $sql_5;
							}
							else
							{
								
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql_5."</strong>";
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

//Аматор. А теперь очистим таблицы virtuemart_product_to_1c, в них остаются данные после ручного удаления продуктов из каталога на сайте, и потом вылазит ошибка загрузки 

$sql2 = "SELECT * FROM #__".$dba['product_to_1c_db'];
		
					$db->setQuery ( $sql2 );
					$rows2 = $db->loadObjectList ();
					foreach ( $rows2 as $row2 ) 
					{
					//continue;	
//+Аматор Проверим, нет ли ссылки на этот товар

							$sql1 = "SELECT * FROM #__".DBBASE."_products where `virtuemart_product_id` = '" . $row2->product_id . "'";

							$db->setQuery ( $sql1 );
							$rows1 = $db->loadObject ();
							if ($rows1) {continue;}

//-Аматор


						$sql_5 = "DELETE FROM #__".$dba['product_to_1c_db']. " WHERE `product_id` = '".$row2->product_id."'";
						$db->setQuery ( $sql_5 );
						if (!$db->query ())
						{
							$log->addEntry ( array ('comment' => 'Этап 4.1.3) Неудача: Невозможно очистить товар, id - ' . $row2->product_id ) );
							$log->addEntry ( array ('comment' => 'Этап 4.1.3) ' . $sql_5 ) );
							if(!defined( 'VM_SITE' ))
							{
								echo 'failure\n';
								echo 'error mysql update\n';
								echo $sql_5;
							}
							else
							{
								
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql_5."</strong>";
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




//-Аматор


					}













while($reader->read()) 
{
	if($reader->nodeType == XMLReader::ELEMENT) 
	{
		switch($reader->name) 
		{
			case 'Группы':
				// Подочернее добавление групп
				require_once(JPATH_BASE_1C .DS.'system'.DS.'category.php');
				inserCategory($reader->readOuterXML());
				$reader->next();
				break;
			
			case 'Свойства':
				// Подочернее добавление производителей
				require_once(JPATH_BASE_1C .DS.'system'.DS.'manufacture.php');
				inserManufacture($reader->readOuterXML());
				//$reader->next();
				break;
			
			case 'Товар':
				// Подочернее добавление товара
				require_once(JPATH_BASE_1C .DS.'system'.DS.'product.php');
				inserProduct($reader->readOuterXML(),$modif);
				//$reader->next();
				break;
		}
	}
}

//if (VM_CAT_IMG == 'yes' and VM_VERVM == '2')
if (VM_CAT_IMG == 'yes')
{
	require_once(JPATH_BASE_1C .DS.'system'.DS.'cat_img.php');
	Ins_cat_img();
}

if(!defined( 'VM_SITE' ))
{
	echo "success\n";
}

$log->addEntry ( array ('comment' => 'Этап 4.1.5) Все товары добавленны (обновленны)') );
$logs_http[] = "<strong>Загрузка товара</strong> - Все товары добавленны (обновленны)";
$reader->close();

?>