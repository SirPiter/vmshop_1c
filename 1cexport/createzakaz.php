<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: createzakaz.php - Создание списка заказов

//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}
$timechange = time ();

$no_spaces = '<?xml version="1.0" encoding="UTF-8"?>
							<КоммерческаяИнформация ВерсияСхемы="2.05" ДатаФормирования="' . date ( 'Y-m-d', $timechange ) . 'T' . date ( 'H:i:s', $timechange ) . '"></КоммерческаяИнформация>';

$xml = new SimpleXMLElement ( $no_spaces );

$db->setQuery ( "SELECT * FROM `#__".DBBASE."_orders` WHERE `order_status` LIKE 'U'" );  // SirPiter изменил запрос, добавив наименование статуса заказа

//$db->setQuery ( "SELECT *, #__virtuemart_orderstates.order_status_name FROM `#__".DBBASE."_orders`  
//      		LEFT JOIN #__virtuemart_orderstates ON #__".DBBASE."_orders.order_status = #__virtuemart_orderstates.order_status_code 
//		WHERE `order_status` LIKE 'P'" );

$list = $db->loadObjectList ();

//$log->addEntry ( array ('comment' => $db->explain() ) );


if (! empty ( $list )) 
{
	foreach ( $list as $zakazy )
	{
		$doc = $xml->addChild ( "Документ" );

		if (VM_VERVM == '2')
		{
			$zakazy->order_id = $zakazy->virtuemart_order_id;
			$zakazy->user_id = $zakazy->virtuemart_user_id;
			$zakazy->vendor_id = $zakazy->virtuemart_vendor_id;
			//$zakazy->userinfo_id = $zakazy->virtuemart_userinfo_id;
			$zakazy->order_shipping = $zakazy->order_shipment;
			$zakazy->ship_method_id = $zakazy->virtuemart_shipmentmethod_id;
			$zakazy->mdate = $zakazy->modified_on;
			$dattime = explode(" ", $zakazy->mdate);
			$date = $dattime[0];
			$time = $dattime[1];
			
			$sql = "SELECT currency_code_3 FROM #__virtuemart_currencies where `virtuemart_currency_id` = '" . $zakazy->order_currency . "'";
			$db->setQuery ( $sql );
			$val = $db->loadResult ();
			
		}
		else
		{
			$date = date ( 'Y-m-d', $zakazy->mdate );
			$time = date ( 'H:i:s', $zakazy->mdate );
			$val = ( string ) $zakazy->order_currency;	
		}
		# Валюта документа
		switch ($val) 
		{
			case 'руб' :
				$val = 'RUB';
				break;
			case 'RUB' :
				$val = 'RUB';
				break;
			case 'EUR' :
				$val = 'Евр';
				break;
		}

		$doc->addChild ( "Ид", $zakazy->order_id );
		$doc->addChild ( "Номер", $zakazy->order_number );
		$doc->addChild ( "Дата", $date );
		$doc->addChild ( "ХозОперация", "Заказ товара" );
		$doc->addChild ( "Роль", "Продавец" );
		$doc->addChild ( "Валюта", $val );
		$doc->addChild ( "Курс", $zakazy->order_tax );
		$doc->addChild ( "Сумма", $zakazy->order_subtotal );
		$doc->addChild ( "Время", $time );

			$sql = "SELECT order_status_name FROM #__virtuemart_orderstates where `order_status_code` = '" . $zakazy->order_status . "'";
			$db->setQuery ( $sql );
			$val = $db->loadResult ();
		$doc->addChild ( "Статус", $val );
//		$doc->addChild ( "Статус", $zakazy->order_status );
//		$doc->addChild ( "Статус", $zakazy->order_status_name );  //SirPiter Наименование статуса заказа


			// Контрагенты
// SirPiter		$db->setQuery ( "SELECT * FROM `#__".$dba['order_user_info_db']."` WHERE `address_type` = 'BT' AND `".$dba['pristavka']."order_id` =" . $zakazy->order_id . " AND `".$dba['pristavka']."user_id`=" . $zakazy->user_id );
		$db->setQuery ( "SELECT * FROM `#__".$dba['order_user_info_db']."` WHERE `".$dba['pristavka']."order_id` =" . $zakazy->order_id . " AND `".$dba['pristavka']."user_id`=" . $zakazy->user_id );

		$client = $db->loadObject ();

		if (! empty ( $client ) & (VM_CLIENT == 1)) 
		{
			$FIO = $client->last_name . " " . $client->first_name . " " . $client->middle_name;
			
			$k1 = $doc->addChild ( 'Контрагенты' );
			$k1_1 = $k1->addChild ( 'Контрагент' );
			
			if (!empty($client->company) and VM_VERVM == '1')
			{
				$client->company = str_replace("\\", "", $client->company);
				$k1_2 = $k1_1->addChild ( "Наименование", $client->company );
				$k1_2 = $k1_1->addChild ( "Роль", "Покупатель" );
				if (VM_USER_SHOP == 'yes')
				{
					if ($client->vm_fullname == NULL)
					{
						$k1_2 = $k1_1->addChild ( "ОфициальноеНаименование", $client->company );
					}
					else
					{
						$k1_2 = $k1_1->addChild ( "ОфициальноеНаименование", $client->vm_fullname );
					}
				}
				else
				{
					$k1_2 = $k1_1->addChild ( "ОфициальноеНаименование", $client->company );
				}
			}
			else
			{//Аматор, заменим Наименование на Email
		//$db->setQuery ( "SELECT * FROM `#__users` WHERE `id` =" . $zakazy->user_id );
		//$us = $db->loadObject ();

				$k1_2 = $k1_1->addChild ( "Наименование", $client->email );
				$k1_2 = $k1_1->addChild ( "Роль", "Покупатель" );
				$k1_2 = $k1_1->addChild ( "ЮрФизЛицо", "ФизЛицо" );

				$k1_2 = $k1_1->addChild ( "ПолноеНаименование", $client->title . " " . $FIO );
				$k1_2 = $k1_1->addChild ( "Имя", $client->first_name );
				$k1_2 = $k1_1->addChild ( "Фамилия", $client->last_name );
				$k1_2 = $k1_1->addChild ( "Отчество", $client->middle_name );
				$k1_2 = $k1_1->addChild ( "Адрес", $client->city . " ". $client->address_type_name . " ". $client->address_1 );  //SirPiter раскомментировал
				$k1_2 = $k1_1->addChild ( "Телефон", $client->phone_1 );							//SirPiter раскомментировал
				
				$kom = "Телефон:". $client->phone_1. ", Адрес:". $client->city . " ". $client->address_1; //SirPiter изменил $client->phone_2 на $client->phone_1

		$db->setQuery ( "SELECT * FROM `#__".$dba['order_user_info_db']."` WHERE `address_type` = 'ST' AND `".$dba['pristavka']."order_id` =" . $zakazy->order_id . " AND `".$dba['pristavka']."user_id`=" . $zakazy->user_id );
		$clientST = $db->loadObject ();
if (! empty ( $clientST ) & (VM_CLIENT == 1)) 
		{ 

$kom = $kom . ", Адрес доставки:". $clientST->city . " ". $clientST->address_type_name;

}
				$k1_2 = $k1_1->addChild ( "Комментарий", $kom);
}

		} 
		else 
		{
			$k1 = $doc->addChild ( 'Контрагенты' );
			$k1_1 = $k1->addChild ( 'Контрагент' );
			$k1_2 = $k1_1->addChild ( "Наименование", "Физ лицо" );
			$k1_2 = $k1_1->addChild ( "Роль", "Покупатель" );
			$k1_2 = $k1_1->addChild ( "ПолноеНаименование", "Физ лицо" );
			$k1_2 = $k1_1->addChild ( "Имя", "лицо" );
			$k1_2 = $k1_1->addChild ( "Фамилия", "Физ" );
		}
		
		if (VM_VERVM_S == 'F')
		{
			$product_db = $dba['product_ln_db'];
		}
		else
		{
			$product_db = $dba['product_db'];
		}
		
		$db->setQuery ( "SELECT it.".$dba['pristavka']."product_id as product_id, it.product_item_price as product_item_price,
				it.product_quantity as product_quantity, it.product_final_price as product_final_price,pd.product_name as product_name,
				c.c_id as guid 
				FROM #__".$dba['order_item_db']." AS it 
				LEFT OUTER JOIN #__".$product_db." AS pd ON it.".$dba['pristavka']."product_id = pd.".$dba['pristavka']."product_id 
                		LEFT JOIN #__virtuemart_product_to_1c AS c ON it.".$dba['pristavka']."product_id = c.product_id 
				WHERE it.".$dba['pristavka']."order_id =" . $zakazy->order_id );

		$list_z = $db->loadObjectList ();

		foreach ( $list_z as $razbor_zakaza_t ) 
		{
			
			$t1 = $doc->addChild ( 'Товары' );
			$t1_1 = $t1->addChild ( 'Товар' );
//			$t1_2 = $t1_1->addChild ( "Ид", $razbor_zakaza_t->product_id );  
			$t1_2 = $t1_1->addChild ( "Ид", $razbor_zakaza_t->guid );       //SirPiter вместо id товара из Virtuemart подставляется id товара из 1С. В запрос выше добавлена выборка из таблицы _virtuemart_product_to_1c

			$t1_2 = $t1_1->addChild ( "Наименование", $razbor_zakaza_t->product_name );
			if (VM_NDS == 'yes')
			{
				$t1_2 = $t1_1->addChild ( "ЦенаЗаЕдиницу", $razbor_zakaza_t->product_final_price );
				$summ = $razbor_zakaza_t->product_final_price * $razbor_zakaza_t->product_quantity;
			}
			else
			{
				$t1_2 = $t1_1->addChild ( "ЦенаЗаЕдиницу", $razbor_zakaza_t->product_item_price );
				$summ = $razbor_zakaza_t->product_item_price * $razbor_zakaza_t->product_quantity;
			}
			$t1_2 = $t1_1->addChild ( "Количество", $razbor_zakaza_t->product_quantity );
			$t1_2 = $t1_1->addChild ( "Сумма", $summ );
			$t1_2 = $t1_1->addChild ( "ЗначенияРеквизитов" );
			$t1_3 = $t1_2->addChild ( "ЗначениеРеквизита" );
			$t1_4 = $t1_3->addChild ( "Наименование", "ВидНоменклатуры" );
			$t1_4 = $t1_3->addChild ( "Значение", "Товар" );

			//$t1_2 = $t1_1->addChild ( "ЗначенияРеквизитов" );
			$t1_3 = $t1_2->addChild ( "ЗначениеРеквизита" );
			$t1_4 = $t1_3->addChild ( "Наименование", "ТипНоменклатуры" );
			$t1_4 = $t1_3->addChild ( "Значение", "Товар" );

		}
		//Аматор. Доставка у нас бесплатная, выгружать не будем ее
		if (false)
		//if(!empty($zakazy->order_shipping ) or $zakazy->order_shipping <> '0')
		{
			if (VM_VERVM == '2')
			{
				/*$db->setQuery ( "SELECT shipment_name, shipment_cost
				FROM #__virtuemart_shipment_plg_weight_countries 
				WHERE order_number =" . $zakazy->order_number );
				$shipment = $db->loadObject ();*/
				
				$sql = "SELECT * FROM #__virtuemart_shipment_plg_weight_countries where `order_number` = '".$zakazy->order_number."'";
				$db->setQuery ( $sql );
				$shipment = $db->loadObject ();
				$name = array();
				$name[2] = $shipment->shipment_name;
				$name[3] = $shipment->shipment_cost;
				$name[2] = str_replace('<span class="vmshipment_name">', '', $name[2]);
				$name[2] = str_replace('</span>', '', $name[2]);
			}
			else
			{
				$name = explode('|', $zakazy->ship_method_id );
			}
			$t1 = $doc->addChild ( 'Товары' );
			$t1_1 = $t1->addChild ( 'Товар' );
			$t1_2 = $t1_1->addChild ( "Ид", '1' );
			$t1_2 = $t1_1->addChild ( "Наименование", $name[2] );
			if (VM_NDS == 'yes')
			{
				$t1_2 = $t1_1->addChild ( "ЦенаЗаЕдиницу", $name[3] );
				$summ = $name[3];
			}
			else
			{
				$t1_2 = $t1_1->addChild ( "ЦенаЗаЕдиницу", $zakazy->order_shipping );
				$summ = $zakazy->order_shipping;
			}
			$t1_2 = $t1_1->addChild ( "Количество", '1' );
			$t1_2 = $t1_1->addChild ( "Сумма", $summ );	
			$t1_2 = $t1_1->addChild ( "БазоваяЕдиница", 'ч/час' );
			$t1_2 = $t1_1->addChild ( "СтавкиНалогов" );
			$t1_3 = $t1_2->addChild ( "СтавкаНалога" );
			$t1_4 = $t1_3->addChild ( "Ставка", VM_NDS_SHIP );
			$t1_2 = $t1_1->addChild ( "ЗначенияРеквизитов" );
			$t1_3 = $t1_2->addChild ( "ЗначениеРеквизита" );
			$t1_4 = $t1_3->addChild ( "Наименование", "ВидНоменклатуры" );
			$t1_4 = $t1_3->addChild ( "Значение", "Услуга" );
	
			$t1_2 = $t1_1->addChild ( "ЗначенияРеквизитов" );
			$t1_3 = $t1_2->addChild ( "ЗначениеРеквизита" );
			$t1_4 = $t1_3->addChild ( "Наименование", "ТипНоменклатуры" );
			$t1_4 = $t1_3->addChild ( "Значение", "Услуга" );
		}
	//+Аматор. Попробуем выгрузить скидку
	if((double)($zakazy->order_payment) <> '0')
		{
						
			$t1 = $doc->addChild ( 'Товары' );
			$t1_1 = $t1->addChild ( 'Товар' );
			$t1_2 = $t1_1->addChild ( "Ид", '1' );
			$t1_2 = $t1_1->addChild ( "Наименование", "Скидка" );
			
			$t1_2 = $t1_1->addChild ( "ЦенаЗаЕдиницу", $zakazy->order_payment );
							
			$t1_2 = $t1_1->addChild ( "Количество", '1' );
			$t1_2 = $t1_1->addChild ( "Сумма", $zakazy->order_payment );	
			$t1_2 = $t1_1->addChild ( "БазоваяЕдиница", 'шт' );
			$t1_2 = $t1_1->addChild ( "СтавкиНалогов" );
			$t1_3 = $t1_2->addChild ( "СтавкаНалога" );
			$t1_4 = $t1_3->addChild ( "Ставка", VM_NDS_SHIP );
			$t1_2 = $t1_1->addChild ( "ЗначенияРеквизитов" );
			$t1_3 = $t1_2->addChild ( "ЗначениеРеквизита" );
			$t1_4 = $t1_3->addChild ( "Наименование", "ВидНоменклатуры" );
			$t1_4 = $t1_3->addChild ( "Значение", "Услуга" );
	
			$t1_2 = $t1_1->addChild ( "ЗначенияРеквизитов" );
			$t1_3 = $t1_2->addChild ( "ЗначениеРеквизита" );
			$t1_4 = $t1_3->addChild ( "Наименование", "ТипНоменклатуры" );
			$t1_4 = $t1_3->addChild ( "Значение", "Услуга" );
		}

	//-Аматор
	}
} 

if (VM_CODING == 'UTF-8') 
{
	//header ( "Content-type: text/xml; charset=utf-8" );
	print iconv ( "utf-8", "windows-1251", $xml->asXML () );
} 
else 
{
	print $xml->asXML ();
}

JLog::add ( 'Этап 2) Успешно'.$xml->asXML (), JLog::INFO, 'vmshop_1c' );

unlink ( JPATH_BASE_1C .DS.'login.tmp' );

?>