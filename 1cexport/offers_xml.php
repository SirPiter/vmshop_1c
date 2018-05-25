<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: offers_xml.php - Импорт содержимого файла offers.xml

//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

$logs_http[] = "<strong>Загрузка цен</strong> - Проверка базы данных совместимости 1с и VMSHOP";
//$log->addEntry ( array ('comment' => 'Этап 4.2.1) Проверка базы данных совместимости 1с и VMSHOP') );
JLog::add ( 'Этап 4.2.1) Проверка базы данных совместимости 1с и VMSHOP', JLog::INFO, 'vmshop_1c' );

$res4 = $db->setQuery ( 'SHOW COLUMNS FROM #__'.$dba['cashgroup_to_1c_db'] );

if( !$db->query($res4)) 
{
	//$log->addEntry ( array ('comment' => 'Этап 4.2.1) База cashgroup_to_1c не существует. SQL: '.'SHOW COLUMNS FROM "#__'.$dba['cashgroup_to_1c_db'].'"' ) );			
    JLog::add ( 'Этап 4.2.1) База cashgroup_to_1c не существует. SQL: '.'SHOW COLUMNS FROM "#__'.$dba['cashgroup_to_1c_db'].'"' , JLog::INFO, 'vmshop_1c' );
    $db->setQuery ( 
			'CREATE TABLE 
			`#__'.$dba['cashgroup_to_1c_db'].'` ( 
			`cashgroup_id` int(10) unsigned NOT NULL,
			`c_id` varchar(255) NOT NULL,
			KEY (`cashgroup_id`),
			KEY `c_id` (`c_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	 );
	$db->query ();
	
	$logs_http[] = "<strong>Загрузка цен</strong> - База cashgroup_to_1c создана";
	//$log->addEntry ( array ('comment' => 'Этап 4.2.1) База cashgroup_to_1c создана') );
	JLog::add ( 'Этап 4.2.1) База cashgroup_to_1c создана' , JLog::INFO, 'vmshop_1c' );
	
}
else
{
	$logs_http[] = "<strong>Загрузка цен</strong> - База cashgroup_to_1c существует";
	//$log->addEntry ( array ('comment' => 'Этап 4.1.1) База cashgroup_to_1c существует') );
	JLog::add ( 'Этап 4.1.1) База cashgroup_to_1c существует' , JLog::INFO, 'vmshop_1c' );
	
}
$logs_http[] = "<strong>Загрузка цен</strong> - Добавление цен к товарам";
//$log->addEntry ( array ('comment' => 'Этап 4.2.1) Добавление цен к товарам') );
JLog::add ( 'Этап 4.2.1) Добавление цен к товарам' , JLog::INFO, 'vmshop_1c' );


$offersFile = JPATH_BASE_PICTURE. DS . 'offers.xml';

$reader = new XMLReader();
$reader->open($offersFile);

$offer = new XMLReader();

$cash_group = new XMLReader();

$base = new XMLReader();
$base->open($offersFile);


if(!$reader and !$base)
{
	//$log->addEntry ( array ('comment' => 'Этап 4.2.1) Неудача: Ошибка открытия XML') );	
    JLog::add ( 'Этап 4.2.1) Неудача: Ошибка открытия XML' , JLog::ERROR, 'vmshop_1c' );
    $logs_http[] = "<strong>Загрузка цен</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка открытия XML";
	if(!defined( 'VM_SITE' ))
	{
		echo 'failure\n';
	}
	die();
}
else
{
	//$log->addEntry ( array ('comment' => 'Этап 4.2.1) XML offers.xml загружен') );
    JLog::add ( 'Этап 4.2.1) XML offers.xml загружен' , JLog::INFO, 'vmshop_1c' );
    $logs_http[] = "<strong>Загрузка цен</strong> - XML <strong>offers.xml</strong> загружен";
}

//$log->addEntry ( array ('comment' => 'Этап 4.2.1) Базы созданы, переходим к процесу отчистки') );
JLog::add ( 'Этап 4.2.1) Базы созданы, переходим к процесу отчистки' , JLog::INFO, 'vmshop_1c' );

$logs_http[] = "<strong>Загрузка цен</strong> - Все базы созданы, переходим к процесу отчистки";

$data = array();

$CAT = array();

while($base->read()) 
{
	if($base->nodeType == XMLReader::ELEMENT) 
	{
		switch($base->name) 
		{
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
				
				//$log->addEntry ( array ('comment' => 'Этап 4.2.1) Версия схемы XML '.$vers_xml. ' VM_XML_VERS = '.VM_XML_VERS) );
				JLog::add ( 'Этап 4.2.1) Версия схемы XML '.$vers_xml. ' VM_XML_VERS = '.VM_XML_VERS , JLog::INFO, 'vmshop_1c' );
				$logs_http[] = '<strong>Загрузка цен</strong> - Версия схемы XML '.$vers_xml. ' VM_XML_VERS = '.VM_XML_VERS;
				//$base->next();
				break;
				
			case 'ПакетПредложений':
				require_once(JPATH_BASE_1C .DS.'system'.DS.'clearbase.php');
				clearBase($base->getAttribute("СодержитТолькоИзменения"),'2');
				$modif = $base->getAttribute("СодержитТолькоИзменения");
				
				//$base->next();
				break;			
		}
	}
}
$base->close();

if ($modif == 'false')
{
	//$log->addEntry ( array ('comment' => 'Этап 4.2.2) Базы отчищены, переходим к процесу создания категорий') );
    JLog::add ( 'Этап 4.2.2) Базы отчищены, переходим к процесу создания категорий' , JLog::INFO, 'vmshop_1c' );
    
	$logs_http[] = "<strong>Загрузка цен</strong> - Все базы созданы, переходим к процесу создания категорий";
}

while($reader->read()) 
{
	if($reader->nodeType == XMLReader::ELEMENT) 
	{
		switch($reader->name) 
		{
			case 'ТипЦены':
				// Подочернее добавление групп
				require_once(JPATH_BASE_1C .DS.'system'.DS.'cashgroup.php');
				inserCashgroup($reader->readOuterXML());
				$reader->next();
				break;
			
			case 'Предложение':
				// Подочернее добавление товара
				require_once(JPATH_BASE_1C .DS.'system'.DS.'offers.php');
				inserOffers($reader->readOuterXML());
				//$reader->next();
				break;
		}
	}
}



//$log->addEntry ( array ('comment' => 'Этап 4.2.4) Все цены добавленны (обновленны)') );
JLog::add ( 'Этап 4.2.4) Все цены добавленны (обновленны)' , JLog::INFO, 'vmshop_1c' );
$logs_http[] = "<strong>Загрузка цен</strong> - ---------------- Все цены добавленны (обновленны) ----------------";
$reader->close();

if (isset($handle)) 
{
	fclose($handle);
	unset($handle);
}

if (unlink ( JPATH_BASE_PICTURE.DS.'import.xml' ))
{
	$logs_http[] = "<strong>Финал</strong> - ---------------- import.xml удален ----------------";	
}
if (unlink ( JPATH_BASE_PICTURE.DS.'offers.xml' ))
{
	$logs_http[] = "<strong>Финал</strong> - ---------------- offers.xml удален ----------------";
}
if (unlink ( JPATH_BASE_1C .DS.'login.tmp' ))
{
	$logs_http[] = "<strong>Финал</strong> - ---------------- login.tmp удален ----------------";
}

if(!defined( 'VM_SITE' ))
{
	echo "success\n";
}
?>