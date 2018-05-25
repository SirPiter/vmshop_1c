<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: vmshop_1c.php - Основной модуль 
//			    Amator  (email: amatoravg@gmail.com)
//***********************************************************************
//Системные параметры
define ( 'VM_VERSION', '3.0.1.Amator.SirPiter' ); 	// Версия скрипта. Будет обновляться!

define ( 'VM_HTTP_VERS', 1 ); 	// Использовать модуль http (через браузер) 1- да, 0- нет (в случае 0 - настройте config.php)
								// Можно сначало включить, настроить, а потом выключить!
//-------------------------------Далее редактировать на свой страх и риск!!!!--------------------------------------
set_time_limit (0);

define ( 'VM_1CEXPORT', true );
ini_set ( 'display_errors', '1' );
error_reporting ( E_ALL );
define ( '_JEXEC', 1 );
//define ( 'DS', DIRECTORY_SEPARATOR );
define ( 'DS', '/' ); // Аматор. Картинки загружаются с неправильным слешем

define ( 'JPATH_BASE', dirname ( __FILE__ ) . '' );
define ( 'JPATH_BASE_1C', JPATH_BASE . DS .'components'.DS.'com_virtuemart'.DS.'1cexport' );

require (JPATH_BASE_1C . DS . 'system' .DS . 'config.php');

define ( 'VM_CODING', $config['VM_CODING'] ); 	// Кодировка выгрузки заказов (пока не применяется)
define ( 'VM_DB', $config['VM_DB'] ); 			// Обнулять таблицы перед выгрузкой?
define ( 'VM_ZIP', $config['VM_ZIP'] ); 		// Использование zip архивов
define ( 'VM_ZIPSIZE', $config['VM_ZIPSIZE'] ); 	// максимальный размер архива в байтах
define ( 'VM_LOG', $config['VM_LOG'] ); 		// вести логи по времени (time), по дате (date), один лог (one)
define ( 'VM_LANG', $config['VM_LANG'] ); 		// Язык 1С - требуется для характеристик (см adapt.php)
//Параметры изображения категории
define ( 'VM_CAT_IMG', $config['VM_CAT_IMG'] ); 	// Применять картинку к категории
define ( 'VM_CAT_RAND', $config['VM_CAT_RAND'] ); 		// Выбор картинки ( r - рандомом, p - первая )
//Параметры отвечающие за налог
define ( 'VM_NDS', $config['VM_NDS'] ); 		// Учитывать в цене из 1С налог НДС? 
define ( 'VM_NDS_COUNTRY', $config['VM_NDS_COUNTRY'] ); // Страна учета НДС?
//Параметры отвечающие за поставки товара
define ( 'VM_POSTAVKA_E', $config['VM_POSTAVKA_E'] ); 	// Использовать модуль поставки (заменяет картинку в случае отсутствия товара)
define ( 'VM_POSTAVKA', $config['VM_POSTAVKA'] ); 	// Вставляет текст или картинку (например: on-order.gif или в наличии)
define ( 'VM_POSTAVKA_TIME', $config['VM_POSTAVKA_TIME'] ); 	// Ориентировачное время поставки в секундах, т.е. 5д*24ч*60м*60с = 432000
//Параметры thumbnails изображения
define ( 'VM_TBN_H', $config['VM_TBN_H'] ); 		// Высота thumbnails изображения
define ( 'VM_TBN_W', $config['VM_TBN_W'] ); 		// Ширина thumbnails изображения
define ( 'VM_TBN_RED', $config['VM_TBN_RED'] ); 	// Подложка thumbnails изображения (Красный - 255)	-	>			
define ( 'VM_TBN_GREEN', $config['VM_TBN_GREEN'] ); 	// Подложка thumbnails изображения (Зеленый - 255)	-		>	Все вместе - белый
define ( 'VM_TBN_BLUE', $config['VM_TBN_BLUE'] ); 	// Подложка thumbnails изображения (Синий - 255)	-	>
define ( 'VM_TBN_QTY', $config['VM_TBN_QTY'] ); 		// Качество thumbnails изображения (максимум 100)
define ( 'VM_JPG', $config['VM_JPG'] ); 			// Заменять ли JPEG на JPG
//Параметры отвечающие за каталог
define ( 'VM_LIST_CAT', $config['VM_LIST_CAT'] ); 		// Сколько отображать товаров в каталоге 
define ( 'VM_DEF_CASHGR', $config['VM_DEF_CASHGR'] ); 		// название основной категории цен (у меня это Розничная)
define ( 'VM_MANUFACTURE', $config['VM_MANUFACTURE'] ); 	// Обозначение свойства производителя
//Параметры отвечающие за выгрузку заказов
define ( 'VM_USER_SHOP', $config['VM_USER_SHOP'] );	// Создать поля для регистрации новых клиентов (Банковские реквизиты: ИНН, КПП и т.д.)
define ( 'VM_CLIENT', $config['VM_CLIENT'] ); 		// 0 - Выгружать всех клиентов в 1С на контрагента "Физ лицо"  1- Выгружать всех клиентов в 1С как есть
define ( 'VM_NDS_SHIP', $config['VM_NDS_SHIP'] );		// Ставка НДС для услуги доставки

define ( 'VM_PUBLISH_NEW_PRODUCT', $config['VM_PUBLISH_NEW_PRODUCT'] );		// SirPiter : публикация нового товара
define ( 'VM_PUBLISH_NEW_CATEGORY', $config['VM_PUBLISH_NEW_CATEGORY'] );		// SirPiter : публикация новой категории


require_once (JPATH_BASE . DS . 'includes' . DS . 'defines.php');
require_once (JPATH_BASE . DS . 'includes' . DS . 'framework.php');


//  SirPiter // require ( 'libraries' .DS. 'joomla' .DS. 'factory.php');

require ( 'libraries' .DS. 'classmap.php');


$mainframe = JFactory::getApplication ( 'site' );
$mainframe->initialise ();


$db = JFactory::getDBO ();


jimport ( 'joomla.error.log' );
jimport ( 'joomla.user.helper' );
if (VM_LOG == 'time')
{
	JLog::addLogger ( array('text_file' => 'vmshop_1c_'.date('y_m_d_H_i').'.log.php' ), JLog::ALL, array('vmshop_1c'));
}
elseif (VM_LOG == 'date') 
{
    JLog::addLogger ( array('text_file' => 'vmshop_1c_'.date('y_m_d').'.log.php' ), JLog::ALL, array('vmshop_1c'));
}
elseif (VM_LOG == 'one')
{
    JLog::addLogger ( array('text_file' => 'vmshop_1c.log.php' ), JLog::ALL, array('vmshop_1c') );
}
else
{
    JLog::addLogger ( array('text_file' => 'vmshop_1c.log.php' ), JLog::ALL, array('vmshop_1c') );
}


$template = "";

require (JPATH_BASE_1C . DS . 'checkver.php');
JLog::add ( 'SirPiter 0) Проверка версии VM', JLog::INFO, 'vmshop_1c' );
JLog::add ( 'SirPiter 0) VM: '.VM_VERVM.VM_VERVM_S.LANG, JLog::INFO, 'vmshop_1c' );



if (VM_JPG == 'yes')
{
	define ( 'VM_JPG_S', 'jpg' );
}
else
{
	define ( 'VM_JPG_S', 'jpeg' );
}

if (VM_VERVM == '2')
{
	define ( 'JPATH_BASE_PICTURE', JPATH_BASE .DS.'images'.DS.'stories'.DS.'virtuemart'.DS.'product' );
	define ( 'JPATH_PICTURE', 'images'.DS.'stories'.DS.'virtuemart'.DS.'product' );
}
else
{
	define ( 'JPATH_BASE_PICTURE', JPATH_BASE .DS.'components'.DS.'com_virtuemart'.DS.'shop_image'.DS.'product' );
	define ( 'JPATH_CAT_PICTURE', JPATH_BASE .DS.'components'.DS.'com_virtuemart'.DS.'shop_image'.DS.'category' );
}
define ( 'JPATH_BASE_PICTURE_SMALL', JPATH_BASE_PICTURE .DS.'resized' );

if (VM_VERVM == '2')
{
	define ( 'DBBASE', 'virtuemart' );
	require_once(JPATH_BASE_1C .DS.'translit.php');
}
else
{
	define ( 'DBBASE', 'vm' );
}

if (file_exists(JPATH_BASE_1C .DS.'login.tmp'))
{
	$handle = fopen(JPATH_BASE_1C .DS.'login.tmp', "r");
}
else
{
	$handle = fopen(JPATH_BASE_1C .DS.'login.tmp', "w+");
}
$id = 0;

while (!feof($handle)) 
{
    $buffer[$id] = fgets($handle, 4096);
	$id++;
}

if (!empty($buffer[0]) and !empty($buffer[1]))
{
	$id_admin = $buffer[0];
	$username = $buffer[1];
}
else
{
	$id_admin = 0;
}

require_once(JPATH_BASE_1C .DS.'adapt.php');

$sql = "SELECT registration FROM #__".$dba['userfield_db']." WHERE name ='vm_fullname'";
$db->setQuery($sql);
$adapt = $db->loadResult ();

if (empty($adapt) and VM_USER_SHOP == 'yes')
{
	require_once(JPATH_BASE_1C .DS.'adaptvm.php');
}

require_once(JPATH_BASE_1C .DS.'http.php');
		
$template = $templ;

if (isset($_REQUEST['mode'])) 
{
	JLog::add ( 'Аматор 0)'.$_REQUEST['mode'], JLog::DEBUG, 'vmshop_1c' );

	//?mode=checkauth
	if( $_REQUEST['mode'] == 'checkauth') 
	{
//		//  $log->add ( 'comment' => 'Скрипт адптации 1С и магазина Virtuemart версии: '.$version. ' Релиз: ' .$version_status.', версия скрипта: '.VM_VERSION.', обнуление базы перед выгрузкой: '.VM_DB.', выгрузка архивом: '.VM_ZIP );
//		//  $log->add ( 'comment' => 'Этап 1) Авторизация на сервере' );
	//    JLog::add ( 'Скрипт адптации 1С и магазина Virtuemart версии: '.$version.' Релиз: ' .$version_status.', версия скрипта: '.VM_VERSION.', обнуление базы перед выгрузкой: '.VM_DB.', выгрузка архивом: '.VM_ZIP, JLog::DEBUG, 'vmshop_1c' );
	    JLog::add ( 'Этап 1) Авторизация на сервере', JLog::DEBUG, 'vmshop_1c' );
	    
		if(defined( 'VM_SITE' ))
		{
			$logs_http[] = 'Скрипт адптации 1С и магазина Virtuemart версии: <strong>'.$version. '</strong> Релиз: <strong>' .$version_status.'</strong>, версия скрипта: <strong>'.VM_VERSION.'</strong>, обнуление базы перед выгрузкой: <strong>'.VM_DB.'</strong>, выгрузка архивом: <strong>'.VM_ZIP.'</strong>';
			$logs_http[] = '<strong>Авторизация на сервере</strong>';
		}
		
		require_once(JPATH_BASE_1C .DS.'checkauth.php');
		fwrite($handle, $somecontent);
		if (isset($handle)) 
		{
			fclose($handle);
			unset($handle);
		}
	} 
	//?mode=init
	elseif( $_REQUEST['mode'] == 'init') 
	{
		//  $log->add ( array ('comment' => 'Этап 2) Инициализация выгрузки: Выгружать в архиве - '.VM_ZIP.', размер - '.VM_ZIPSIZE) );
	    JLog::add ( 'Этап 2) Инициализация выгрузки: Выгружать в архиве - '.VM_ZIP.', размер - '.VM_ZIPSIZE, JLog::DEBUG, 'vmshop_1c' );
	    
	    require_once(JPATH_BASE_1C .DS.'init.php');
		if (isset($handle)) 
		{
			fclose($handle);
			unset($handle);
		}
	} 
	elseif( $_REQUEST['mode'] == 'file') 
	{
		//  $log->add ( array ('comment' => 'Этап 3) Выгрузка файлов или архива и его распаковка') );
	    JLog::add ( 'Этап 3) Выгрузка файлов или архива и его распаковка', JLog::DEBUG, 'vmshop_1c' );
	    
		$logs_http[] = '<strong>Выгрузка файлов или архива и его распаковка</strong>';
		require_once(JPATH_BASE_1C .DS.'file.php');
		if (isset($handle)) 
		{
			fclose($handle);
			unset($handle);
		}
	} 
	elseif( $_REQUEST['mode'] == 'import') 
	{
		//  $log->add ( array ('comment' => 'Этап 4) Импорт содержимого файлов (каталог)') );
	    JLog::add ( 'Этап 4) Импорт содержимого файлов (каталог)', JLog::INFO, 'vmshop_1c' );
	    require_once(JPATH_BASE_1C .DS.'import.php');
		if (isset($handle)) 
		{
			fclose($handle);
			unset($handle);
		}
	}
//+Аматор
	elseif( $_REQUEST['mode'] == 'importsale') 
	{
		//  $log->add ( array ('comment' => 'Этап 5) Импорт заказов из 1С') );
		require_once(JPATH_BASE_1C .DS.'importsale.php');
		if (isset($handle)) 
		{
			fclose($handle);
			unset($handle);
		}
//-Аматор
	} 
	elseif( $_REQUEST ['mode'] == 'success') 
	{
		//  $log->add ( array ('comment' => '1С закончила загрузку заказов') );
		print 'success\n';
		if (isset($handle)) 
		{
			fclose($handle);
			unset($handle);
		}
	}
	elseif( $_REQUEST ['mode'] == 'query') 
	{
		//  $log->add ( array ('comment' => 'Этап 2) Построение заказов') );
		require_once(JPATH_BASE_1C .DS.'createzakaz.php');
		if (isset($handle)) 
		{
			fclose($handle);
			unset($handle);
		}
	}
	elseif( $_REQUEST ['mode'] == 'settings') 
	{
		//  $log->add ( array ('comment' => 'Этап 1) Настройки сохранены') );
		require_once(JPATH_BASE_1C .DS.'change_settings.php');
		if (isset($handle)) 
		{
			fclose($handle);
			unset($handle);
		}
	}
	else 
	{
		//  $log->add ( array ('comment' => 'Операция выгрузки завершена') );
		print 'success\n';
		if (isset($handle)) 
		{
			fclose($handle);
			unset($handle);
		}
		exit;
	}
} 
else 
{
	if ($template == "")
	{
		//  $log->add ( array ('comment' => 'Операция выгрузки завершена') );
		print 'success\n';
		if (isset($handle)) 
		{
			fclose($handle);
			unset($handle);
		}
		exit;
	}
}

if(isset($http_content) and $http_content != '')
{
	$template = str_replace('[content]', $http_content, $template);
}

echo $template;

?>
