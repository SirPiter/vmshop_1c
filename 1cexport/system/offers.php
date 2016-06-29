<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: system/offers.php - Класс создания цен

//			    Amator  (email: amatoravg@gmail.com)

//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

function inserOffers($xml_of) 
{
	global $log, $db, $offer, $dba, $lang_1c;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
	
	$offer->XML($xml_of);
	
	$offer_xml = new XMLReader();
				
	$data = array();
	$data['id'] = "";
	$data['name'] = "";
	$data['id_cashgr'] = "";
	$data['price'] = array();
	$data['val'] = array();
	$data['quantity'] = "";
	$data['discount'] = 0;
	$harakt[]='';
	$custom_id='';
	
	$PROPERTIES = array();
		
	while($offer->read()) 
	{
		if($offer->nodeType == XMLReader::ELEMENT ) 
		{
	
			switch($offer->name)
			 {
				case 'Ид': 
					$data['id'] = $offer->readString();
					$off_id = explode("#", $data['id']);
					if (count($off_id) == 1)
					{
						$offers['id'] = $off_id[0];
						$offers['har'] = '';
					}
					else
					{
						$offers['id'] = $off_id[0];	
						$offers['har'] = $off_id[1];	
					}
					//$offer->next();
					break;
									
				case 'Наименование':
					$data['name'] = trim($offer->readString());
					//$offer->next();
					break;
									
				case 'Цена': 
					$offer_xml->XML($offer->readOuterXML());
					
					while($offer_xml->read()) 
					{
						if($offer_xml->nodeType == XMLReader::ELEMENT ) 
						{
							switch($offer_xml->name) 
							{
								case 'ИдТипаЦены':
									$data['id_cashgr'] = (string)$offer_xml->readString();
									
									$sql = "SELECT cashgroup_id  FROM #__".$dba['cashgroup_to_1c_db']." where `c_id` = '" . $db->getEscaped($data['id_cashgr']) . "'";
									$db->setQuery ( $sql );
									$rows_sub_Count = $db->loadResult ();
									if (isset ( $rows_sub_Count ))
									{
										$data['cashgr'] = (int)$rows_sub_Count;
									}
									else
									{
										$log->addEntry ( array ('comment' => 'Этап 4.2.3) Неудача: Ошибка запроса, нет id группы цен ' ) );
										if(!defined( 'VM_SITE' ))
										{
											echo 'failure\n';
											echo 'error mysql';
										}
										else
										{
											$logs_http[] = "<strong>Загрузка цен</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка запроса, нет id группы цен";
										}
										die;
									}
									break;
								
								case 'ЦенаЗаЕдиницу':
									$data['price'][$data['cashgr']]  = $offer_xml->readString();
									break;
									
								case 'Валюта':
									$data['val'][$data['cashgr']]  = $offer_xml->readString();
									break;
								
							}
						}
					}
					$offer->next();
					break;
									
				case 'ХарактеристикиТовара':
					$xml = simplexml_load_string($offer->readOuterXML());
			
					if(VM_XML_VERS == '204')
					{
						foreach($xml as $harakteristiki)
						{
							$namehar = ( string )$harakteristiki->Наименование;	
							$znachhar = ( string )$harakteristiki->Значение;
							
							for ($q=0; $q < count($lang_1c); $q++)
							{
								if($lang_1c[$q] == $namehar)
								{
									$harakt[$q] = $znachhar;
								}
							}
						}
	
						unset($xml);
					}
					//$offer->next();
					break;
				
				case 'Количество': 
					$data['quantity'] = $offer->readString();
					//$offer->next();
					break;
				//+Аматор
				case 'СкидкаНаценка': 
				$offer_xml->XML($offer->readOuterXML());
					
					while($offer_xml->read()) 
					{
						if($offer_xml->nodeType == XMLReader::ELEMENT ) 
						{
							switch($offer_xml->name) 
							{
								
									
								case 'Процент':
									$data['discount']  = (double)$offer_xml->readString();
									
								break;
								
							}
						}
					}
					$offer->next();
				
				break;										
								
			}
								
		}
						
	}
	
	if (VM_VERVM == '2' and isset($namehar) and $namehar != '')
	{
		require_once(JPATH_BASE_1C .DS.'system'.DS.'customfields.php');
		$custom_id = customfields();
	}
						
	createOffers($data,$custom_id,$offers,$harakt);

}

function createOffers($data='',$custom_id='0',$offers='',$harakt='') 
{
	global $log, $db, $dba, $id_admin, $lang_1c;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
	
	$skip = false;
	
	if (!isset($offers['id']) or $offers['id'] == '')
	{
		$log->addEntry ( array ('comment' => 'Этап 4.2.3) Неудача: Ошибка запроса, нет id товара из 1С' ) );
		if(!defined( 'VM_SITE' ))
		{
			echo 'failure\n';
			echo 'error mysql';
		}
		else
		{
			$logs_http[] = "<strong>Загрузка цен</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка запроса, нет id товара из 1С";
		}
		die;
	}
	
	/*if ($offers['har'] == '')
	{*/
		$sql = "SELECT product_id FROM #__".$dba['product_to_1c_db']." where `c_id` = '" . $db->getEscaped($offers['id']) . "'";
		$db->setQuery ( $sql );
		$rows_sub_Count = $db->loadResult ();
	/*}
	else
	{
		$sql = "SELECT product_id FROM #__".$dba['product_to_1c_db']." where `c_id` = '" . $db->getEscaped($offers['id']) . "_har_" . $db->getEscaped($offers['har']) . "'";
		$db->setQuery ( $sql );
		$rows_sub_Count = $db->loadResult ();
	}*/
	
	if(!isset ( $rows_sub_Count )) 
	{
		$log->addEntry ( array ('comment' => 'Этап 4.2.3) Товар '.$data['name'].' с пометкой Удален, его цены не загружаются!' ) );
		$logs_http[] = "<strong>Загрузка цен</strong> - Товар ".$data['name']." с пометкой Удален, его цены не загружаются!";
		return;		
	}
	
	$product_id = (int) $rows_sub_Count;

	if (isset($data['price']) or $data['price'] <> '' or $data['price'] != '' or count($data['price']) >= 1)
	{
		
		foreach ($data['price'] as $cash_gr => $price)
		{
			
			$price = str_replace(",", ".", $price);
			
			$price = str_replace(" ", "", $price);
			
			if (!is_float ($price))
			{
				settype($price, "float");
			}
			
			if($data['val'][$cash_gr] == 'евр' or $data['val'][$cash_gr] == 'Евр' or $data['val'][$cash_gr] == 'евро' or $data['val'][$cash_gr] == 'EUR')
			{
				$val = 'EUR'; 
			}
			elseif ($data['val'][$cash_gr] == 'руб' or $data['val'][$cash_gr] == 'Руб' or $data['val'][$cash_gr] == 'рубль' or $data['val'][$cash_gr] == 'RUB')
			{
				$val = 'RUB';
			}
			elseif ($data['val'][$cash_gr] == 'usd' or $data['val'][$cash_gr] == 'USD' or $data['val'][$cash_gr] == 'доллар' or $data['val'][$cash_gr] == 'Usd')
			{
				$val = 'USD';
			}
			elseif ($data['val'][$cash_gr] == 'грн' or $data['val'][$cash_gr] == 'Грн' or $data['val'][$cash_gr] == 'Гривна')
			{
				$val = 'UAH';
			}
			else
			{
				$val = 'RUB';
			}			
			if (VM_VERVM == '2')
			{
				$sql = "SELECT virtuemart_currency_id FROM #__virtuemart_currencies where `currency_code_3` = '" . $val . "'";
				$db->setQuery ( $sql );
				$rows_sub_Count = $db->loadResult ();
				$val = $rows_sub_Count;
				
				$sql = "SELECT tax_id FROM #__".$dba['product_to_1c_db']." where `product_id` = '" . $product_id . "'";
				$db->setQuery ( $sql );
				$rows_sub_Count = $db->loadResult ();
				$product_tax_id = $rows_sub_Count;
			}
			
			if (VM_NDS == 'yes' and VM_VERVM == '1')
			{
				$sql = "SELECT product_tax_id FROM #__".$dba['product_db']." where `product_id` = '" . $product_id . "'";
				$db->setQuery ( $sql );
				$rows_sub_Count = $db->loadResult ();
				$product_tax_id = $rows_sub_Count;
				
				$sql = "SELECT tax_rate FROM #__".$dba['tax_rate_db']."  where `tax_rate_id` = '" . $product_tax_id . "'";
				$db->setQuery ( $sql );
				$rows_sub_Count = $db->loadResult ();
				$tax_rate =  $rows_sub_Count;
				
				$sql = "SELECT show_price_including_tax FROM #__".$dba['shopper_group_db']."  where `shopper_group_id` = '" . $cash_gr . "'";
				$db->setQuery ( $sql );
				$rows_sub_Count = $db->loadResult ();
				$show_price_including_tax =  $rows_sub_Count;
				
				if($show_price_including_tax == '1')
				{
					$price = $price * 100 / ($tax_rate * 100 + 100);
				}
			}
			$sql = "SELECT ".$dba['pristavka']."product_price_id FROM #__".$dba['product_price_db']." where `".$dba['pristavka']."shopper_group_id` = '" . $cash_gr . "' AND `".$dba['pristavka']."product_id` = '" . $product_id . "'";
			$db->setQuery ( $sql );
			$rows_sub_Count = $db->loadResult ();
			
			if(isset ( $rows_sub_Count ) and $offers['har'] == '') 
			{
				//Обновляем прайс
				$sql = "SELECT * FROM #__".$dba['product_price_db']." where `".$dba['pristavka']."product_price_id` = '" . $rows_sub_Count . "'";
				$db->setQuery ( $sql );
				$rows = $db->loadObject ();
				$update = array();
				if($rows) 
				{
					$product_currency = $rows->product_currency;
					$product_price = $rows->product_price;
					if (VM_VERVM == '2')
					{
						$shopper_group_id = $rows->virtuemart_shoppergroup_id;
						$product_id = $rows->virtuemart_product_id;
					}
					else
					{
						$shopper_group_id = $rows->shopper_group_id;
						$product_id = $rows->product_id;
					}
										
					
					if ($product_price != $price)
					{
						$update['product_price'] = "`product_price`='".(string)$price."'";
					}
					
					if ($shopper_group_id != $cash_gr)
					{
						$update['shopper_group_id'] = "`".$dba['pristavka']."shopper_group_id`='".(string)$cash_gr."'";
					}
					
					if ($product_currency != $val)
					{
						$update['product_currency'] = "`product_currency`='".(string)$val."'";
					}
					
					if ($product_id != $product_id)
					{
						$update['product_id'] = "`".$dba['pristavka']."product_id`='".(int)$product_id."'";
					}
					
					if(!empty($update))
					{
						$sql_upd = "";
						
						foreach($update as $upd )
						{
							$sql_upd .= $upd.", ";
						}
						
						$sql = "UPDATE #__".$dba['product_price_db']." SET ".$sql_upd."".$dba['modifdate']." where ".$dba['pristavka']."product_price_id='".$rows_sub_Count."'";
						$db->setQuery ( $sql );
						if (!$db->query ())
						{
							$log->addEntry ( array ('comment' => 'Этап 4.2.3) Неудача: Невозможно обновить прайс id - ' . $rows_sub_Count ) );
							$log->addEntry ( array ('comment' => 'Этап 4.2.3) ' . $sql ) );
							if(!defined( 'VM_SITE' ))
							{
								echo 'failure\n';
								echo 'error mysql update\n';
							}
							else
							{
								$logs_http[] = "<strong>Загрузка цен</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно обновить прайс id - <strong>".$rows_sub_Count."</strong>";
								$logs_http[] = "<strong>Загрузка цен</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка в запросе - <strong>".$sql."</strong>";
							}
							die;
						}
						
						$log->addEntry ( array ('comment' => 'Этап 4.2.3) Прайс id='.$rows_sub_Count.' товара '.$data['name'].' обновлен!' ) );
						$logs_http[] = "<strong>Загрузка цен</strong> - Прайс id=<strong>".$rows_sub_Count."</strong>  товара <strong>".$data['name']."</strong> обновлен!";
						
					}
				}
				
				$skip = false;
			}
			elseif (!isset ( $rows_sub_Count ) and $offers['har'] == '')
			{
				$ins = new stdClass ();
				if (VM_VERVM == '2')
				{
					$ins->virtuemart_product_price_id = NULL;
					$ins->virtuemart_product_id = (int)$product_id;
					$ins->virtuemart_shoppergroup_id = 0;//Аматор. У нас цены видны для всех. Было - (string)$cash_gr;
					
					//if ($data['discount'] == 0) {
					//		$ins->override = "0";
					//		$ins->product_override_price = "0";
					//}
					//else
					//{
							//Аматор подсчитаем цену со скидкой исходя из процента скидки
							$ins->override = "1";
							$ins->product_override_price = (string)($price*(100 - $data['discount'])/100);
					//}
					
					$ins->product_tax_id = (int)$product_tax_id;
					$ins->product_discount_id = "";
					$ins->created_on = date ('Y-m-d H:i:s');
					$ins->created_by = $id_admin;
					$ins->modified_on = date ('Y-m-d H:i:s');
					$ins->modified_by = $id_admin;
				}
				else
				{
					$ins->product_price_id = NULL;
					$ins->product_id = (int)$product_id;
					$ins->shopper_group_id = (string)$cash_gr;
					$ins->cdate = time ();
					$ins->mdate = time ();
				}
				$ins->product_price = (string)$price;
				$ins->product_currency = (string)$val;
				//$ins->product_price_vdate = "0";	
				//$ins->product_price_edate = "0";
 				$ins->product_price_publish_up = "0";   
            			$ins->product_price_publish_down = "0";				
				
				$ins->price_quantity_start = "0";
				$ins->price_quantity_end = "0";
				
				$query = 'DELETE FROM #__' . $dba['product_price_db'] . ' WHERE virtuemart_product_id = \''. (int)$product_id . '\''; 
                                $db->setQuery($query);
                                $result = $db->query();

				if (! $db->insertObject ( '#__'.$dba['product_price_db'], $ins )) 
				{
					$log->addEntry ( array ('comment' => 'Этап 4.2.3) Неудача: Невозможно вставить запись в таблицу - '.$dba['product_price_db'] ) );
					if(!defined( 'VM_SITE' ))
					{
						echo 'failure\n';
						echo 'error mysql\n';
					}
					else
					{
						$logs_http[] = "<strong>Загрузка цен</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['product_price_db']."</strong>";
					}
					die;
				}
				else
				{
					$logs_http[] = "<strong>Загрузка цен</strong> - Прайс для продукта id - <strong>".$product_id.") ".$data['name']."</strong> создан.";
				}
				
				$skip = false;
			}
			else
			{
				$skip = true;	
			}
		}
		
	}
	else
	{
		$skip = true;
	}
	
	if (VM_XML_VERS == 204 and isset($custom_id) and $custom_id != '')
	{
		makecustoms($data,$product_id,$custom_id,$harakt);
	}
	
	if ($skip == false)
	{
		if (!isset($data['quantity']) or $data['quantity'] == '' or $data['quantity'] == '0')
		{
			$data['quantity'] = "0";
		}
		else
		{
			$data['quantity'] = str_replace(",", ".", $data['quantity']);
		}
		
		if (VM_POSTAVKA_E == 'yes' and $data['quantity'] != "0")
		{
			if (VM_VERVM == '2')
			{
				$postavka = ", `product_availability`='on-order.gif', `product_available_date`='".date ('Y-m-d H:i:s')."'";
			}
			elseif (VM_VERVM == '1')
			{
				$postavka = ", `product_availability`='on-order.gif', `product_available_date`='".time()."'";
			}
			else
			{
				$postavka = "";
			}
		}
		else
		{
			$postavka = "";
		}
	
		$sql = "UPDATE #__".$dba['product_db']." SET `product_in_stock`='".$data['quantity']."'".$postavka." where `".$dba['pristavka']."product_id`='".$product_id."'";
		$db->setQuery ( $sql );
		if (!$db->query ())
		{
			$log->addEntry ( array ('comment' => 'Этап 4.2.3) Неудача: Невозможно обновить продукт id - ' . $rows_sub_Count ) );
			$log->addEntry ( array ('comment' => 'Этап 4.2.3) ' . $sql ) );
			if(!defined( 'VM_SITE' ))
			{
				echo 'failure\n';
				echo 'error mysql update\n';
			}
			else
			{
				$logs_http[] = "<strong>Загрузка цен</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно обновить продукт id - <strong>".$rows_sub_Count."</strong>";
				$logs_http[] = "<strong>Загрузка цен</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка в запросе - <strong>".$sql."</strong>";
			}
			die;
		}	
		/* Аматор, у нас товар должен быть виден для всех
		if (VM_VERVM == '2')
		{
			$sql = "SELECT virtuemart_shoppergroup_id FROM #__".$dba['shopper_group_db']." where `default` = '1'";
			$db->setQuery ( $sql );
			$vs_id = $db->loadResult ();
			
			if (isset($vs_id) and $vs_id != "")
			{
				$sql = "SELECT virtuemart_shoppergroup_id FROM #__virtuemart_product_shoppergroups where `virtuemart_product_id` = '".$product_id."'";
				$db->setQuery ( $sql );
				$vp_id = $db->loadResult ();
				
				if(!isset($vp_id) or $vp_id == "")
				{
					$ins = new stdClass ();
					$ins->id = NULL;
					$ins->virtuemart_product_id = (int)$product_id;
					$ins->virtuemart_shoppergroup_id = 0; //Аматор, у нас цены видны для всех. Было - (int)$vs_id;
					
					if (! $db->insertObject ( '#__virtuemart_product_shoppergroups', $ins )) 
					{
						$log->addEntry ( array ('comment' => 'Этап 4.2.3) Неудача: Невозможно вставить запись в таблицу - virtuemart_product_shoppergroups' ) );
						if(!defined( 'VM_SITE' ))
						{
							echo 'failure\n';
							echo 'error mysql\n';
						}
						else
						{
							$logs_http[] = "<strong>Загрузка цен</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>virtuemart_product_shoppergroups</strong>";
						}
						die;
					}
				}
				elseif(isset($vp_id) and $vp_id != $vs_id) 
				{
					$sql = "UPDATE #__virtuemart_product_shoppergroups SET `virtuemart_shoppergroup_id`='".$vs_id."' where `".$dba['pristavka']."product_id`='".$product_id."'";
					$db->setQuery ( $sql );
					if (!$db->query ())
					{
						$log->addEntry ( array ('comment' => 'Этап 4.2.3) Неудача: Невозможно обновить группу id - '.$vs_id.' для продукта id - ' . $product_id ) );
						$log->addEntry ( array ('comment' => 'Этап 4.2.3) ' . $sql ) );
						if(!defined( 'VM_SITE' ))
						{
							echo 'failure\n';
							echo 'error mysql update\n';
						}
						else
						{
							$logs_http[] = "<strong>Загрузка цен</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно обновить группу id - <strong>".$vs_id."</strong> для продукта id - <strong>".$product_id."</strong>";
							$logs_http[] = "<strong>Загрузка цен</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка в запросе - <strong>".$sql."</strong>";
						}
						die;
					}	
				}
			}
		} */
	}
}

?>