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



$importFile = JPATH_BASE_PICTURE. DS . $_REQUEST['filename'];

$reader = new XMLReader();
$reader->open($importFile);

$product = new XMLReader();

$base = new XMLReader();
$base->open($importFile);


if(!$reader and !$base)
{
	$log->addEntry ( array ('comment' => 'Этап 5.1.1) Неудача: Ошибка открытия XML') );	
	$logs_http[] = "<strong><font color='red'>Неудача:</font></strong> Ошибка открытия XML";
	
	if(!defined( 'VM_SITE' ))
	{
		echo 'failure\n';
	}
	die();
}
else
{
	$log->addEntry ( array ('comment' => 'Этап 5.1.1) XML '.$_REQUEST['filename'].' загружен') );
	$logs_http[] = "<strong>Загрузка заказов</strong> - XML <strong>importsale.xml</strong> загружен";
}

$data = array();

$CAT = array();


while($reader->read()) 
{
	if($reader->nodeType == XMLReader::ELEMENT) 
	{
		switch($reader->name) 
		{
			case 'Документ':
				//$log->addEntry ( array ('comment' => 'Аматор 7) '.$reader) );
				// Подочернее добавление групп
				//require_once(JPATH_BASE_1C .DS.'system'.DS.'category.php');
				UpdateSale($reader->readOuterXML());
				$reader->next();
				break;
			
			case 'Товар':
				// Подочернее добавление товара
				//require_once(JPATH_BASE_1C .DS.'system'.DS.'product.php');
				//inserProduct($reader->readOuterXML(),$modif);
				//$reader->next();
				break;
		}
	}
}


if(!defined( 'VM_SITE' ))
{
	echo "success\n";
}

$log->addEntry ( array ('comment' => 'Этап 5.1.5) Все заказы обновленны') );
$logs_http[] = "<strong>Загрузка заказов</strong> - Все заказы обновленны";
$reader->close();




function UpdateSale($xml_pr) 
{
	global $log, $db, $product, $dba, $id_admin, $lang_1c;
	
	$product->XML($xml_pr);
	$nds_xml = new XMLReader();					
	$data = array();
	
	$PROPERTIES = array();

	$data['id'] = "";
	$data['number'] = "";
	$data['order_payment'] = 0;

						
	while($product->read()) 
	{
		if($product->nodeType == XMLReader::ELEMENT ) 
		{
			switch($product->name) 
			{
							
				case 'Ид':
					//Берем первую часть uuid т.к. могут быть и uuid#id
					$uuid = explode("#", $product->readString());
					$data['id'] = (string)$uuid[0];
					
					//$product->next();
					break;
				case 'Номер':
					$data['number'] = (string)$product->readString();	
					$log->addEntry ( array ('comment' => 'Этап 5.1.2) Обработка заказа номер: '.$data['number']) );					
					//$product->next();
					break;
				case 'Товар':
					$xml = simplexml_load_string($product->readOuterXML());
					$log->addEntry ( array ('comment' => 'Этап 5.1.2) Наименование:  '.$xml->Наименование) );		
					$log->addEntry ( array ('comment' => 'Этап 5.1.2) Сумма:  '.$xml->Сумма) );					
				if ($xml->Наименование == "Скидка")
					{     
					$data['order_payment'] = (double)$xml->Сумма;
					}
					unset($xml);
					$product->next();
					break;
			}
		}
	}
	
	
	if (!empty($data['number']) and $data['number'] != '' and isset($data['number']))
	{
		createSale($data);
	}

}


function createSale($data='') 
{
	global $log, $db, $dba, $id_admin, $lang_1c;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
	

//Аматор Сначала найдем заказ в базе по номеру
$db->setQuery ( "SELECT * FROM `#__".DBBASE."_orders` WHERE `order_number` = '" .$data['number']."'");
//$log->addEntry ( array ('comment' => 'Этап 5.2.1) '. "SELECT virtuemart_order_id FROM `#__".DBBASE."_orders` WHERE `order_number` ='" .$data['number']."'" ) );	
	//$db->setQuery ( $sql );
		$rows_sub_Count = $db->loadObject();
	
	if (! empty ( $rows_sub_Count ))
	{
		$log->addEntry ( array ('comment' => 'Этап 5.2.1) Заказ с номером '.$data['number'].' найден в базе, его id: '.$rows_sub_Count->virtuemart_order_id ) );
	
	$sale_id = (int) $rows_sub_Count->virtuemart_order_id;
	$order_total = (double)$rows_sub_Count->order_subtotal + $data['order_payment'];

	$db->setQuery ( "UPDATE  `#__".DBBASE."_orders` SET order_payment = ".$data['order_payment'] .", order_total = ".$order_total." WHERE `order_number` = '" .$data['number']."'");
if (!$db->query ())
						{
							$log->addEntry ( array ('comment' => 'Этап 5.2.3) Неудача: Невозможно обновить заказ - ' . $sale_id ) );
							
							if(!defined( 'VM_SITE' ))
							{
								echo 'failure\n';
								echo 'error mysql update\n';
							}

							die;
						}
$log->addEntry ( array ('comment' => 'Этап 5.2.2) Заказу с номером '.$data['number'].' установлена скидка: '.$data['order_payment'] ) );
	
	}
	
	//
}

?>