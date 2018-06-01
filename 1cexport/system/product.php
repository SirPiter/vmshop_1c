<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: system/product.php - Класс создания товаров

//			    Amator  (email: amatoravg@gmail.com)

//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

function inserProduct($xml_pr,$modif='false') 
{
	global $log, $db, $product, $dba, $id_admin, $lang_1c;
	
	$product->XML($xml_pr);
	$nds_xml = new XMLReader();					
	$data = array();
	
	$PROPERTIES = array();

	$data['id'] = "";
	$data['uuid'] = "";
	$data['name'] = "";
	$data['full_name'] = "";
	$data['baz_ed'] = "";
	$data['art'] = "";
	$data['model'] = "";
	$data['description'] = "";
	$data['status'] = "";
	$data['nds'] = "";
	$data['ves'] = "";
	$data['image'] = "";
	$data['category_1c_id'] = "";
	$data['slug'] = "";
	$data['metakey'] = "";
	$data['metadesc'] = "";
	$data['metarobot'] = "";
	$data['metaauthor'] = "";
	$data['manufacturer'] = "";
	$data['dlina'] = 0;
	$data['shirina'] = 0;
	$data['visota'] = 0;
	$harakt[]='';
	$cvid = array(); //Аматор Тут будем хранить Ид свойств
	$cvzn = array(); //Аматор Тут будем хранить Значения свойств
	$cvcount = 0; //Аматор Счетчик числа свойств


						
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
					JLog::add ( 'Этап 4.1.3(product.php) Считывание информации (ID) из IMPORT.XML $data[id]='.$data['id'] , JLog::INFO, 'vmshop_1c' );
					
					$data['uuid'] = (string)$uuid[0];
					//$product->next();
					break;
									
				case 'Наименование':
					$data['name'] = (string)$product->readString();	
					//$product->next();
					break;
					
				case 'ПолноеНаименование':
					$data['full_name'] = (string)$product->readString();	
					//$product->next();
					break;
					
				case 'БазоваяЕдиница':
					$data['baz_ed'] = (string)$product->readString();	
					//$product->next();
					break;
					
				case 'Артикул':
					$data['art'] = (string)$product->readString();
					//$product->next();
					break;
									
				// Изображение
				case 'Картинка':
					//Обрабатываем несколько изображений
					if (isset($data['image']) AND $data['image'] <> "") 
					{
						$data['product_image'][] = (string)$product->readString();
					}
					else 
					{
						$data['image'] = (string)$product->readString();
					}
					$product->next();
					break;
									
				case 'Группы':
					$xml = $product->readOuterXML();
					$xml = simplexml_load_string($xml);
					$data['category_1c_id'] = strval($xml->Ид);	
					
					if (!isset($data['category_1c_id']))
					{
						$data['category_1c_id'] = "";
					}
					unset($xml);
					
					$product->next();
					break;
									
				case 'Модель':
					$data['model'] = (string)$product->readString();	
					//$product->next();
					break;
									
				case 'Описание':
					$data['description'] = (string)$product->readString();	
					//$product->next();
					$data['description'] = str_replace("'", "", $data['description']);//Аматор. Описание из за апострофов не грузится
					break;
				
				case 'Статус':
					$data['status'] = (string)$product->readString();	
					
					//$product->next();
					break;
									
				case 'ЗначенияСвойства':
					$xml = simplexml_load_string($product->readOuterXML());
					
					$sql = "SELECT * FROM #__".$dba['manufacturer_to_1c_db']." where 1";
					$db->setQuery ( $sql );
					$rows = $db->loadObjectList ();
					
					$mid = "";
					
					foreach ( $rows as $row ) 
					{
					
					
						switch($xml->Ид)
						{
							case $row->c_manufacturer_id:
								$data['manufacturer'] = (string)$xml->Значение;
								$mid = $data['manufacturer']; 
								
								break;
							
												
						}
					}
					if ($mid == "" ) //Аматор :Данное свойство не является производителем! 
					{		//Тут надо массив или таблицу значений бы сделать, и заполнить ее свойствами
							
							$cvid[$cvcount] = ( string )$xml->Ид; //Аматор Тут будем хранить Ид свойств
							$cvzn[$cvcount] = ( string )$xml->Значение;//Аматор Тут будем хранить Значения свойств
							$cvcount = $cvcount + 1;
							

							
							
					}
					
					unset($xml);
					$product->next();
					break;
					
				case 'ХарактеристикиТовара':
					if(VM_XML_VERS == '203')
					{
						$xml = simplexml_load_string($product->readOuterXML());
					
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
					//$product->next();
					break;
									
				case 'СтавкаНалога':
					
					$nds_xml->XML($product->readOuterXML());
					while($nds_xml->read()) 
					{
						if($nds_xml->nodeType == XMLReader::ELEMENT ) 
						{
							switch($nds_xml->name) 
							{
								case 'Ставка':
									$data['nds_db'] = (int)$nds_xml->readString();
									$nds_xml->next();
									break;
								case 'Наименование':
									$data['nds_name'] = (string)$nds_xml->readString();
									$nds_xml->next();
									break;
							}
						}
					}
					$product->next();
					break;

				case 'ЗначениеРеквизита':
					$xml = simplexml_load_string($product->readOuterXML());
			
					switch($xml->Наименование)
					{
						case 'Вес':
						$data['ves'] = (string)$xml->Значение;
						break;
						
						case 'Длина':
						$data['dlina'] = (string)$xml->Значение;
						break;
							
						case 'Ширина':
						$data['shirina'] = (string)$xml->Значение;
						break;
						
						case 'Высота':
						$data['visota'] = (string)$xml->Значение;
						break;



						case 'Полное наименование':
						$data['full_name_2'] = (string)$xml->Значение;
						break;
					}

					unset($xml);
					$product->next();
					break;
			}
		}
	}
	
	
	if (!empty($data['name']) and $data['name'] != '' and isset($data['name']))
	{
		createProduct($data,$modif,0,$harakt,$cvid,$cvzn);
	}

}

function createProduct($data='',$modif='false', $custom_id='0',$harakt='',$cvid,$cvzn) 
{
	global $log, $db, $dba, $id_admin, $username, $lang_1c;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http, $die;
	}
	
	if (empty($data['full_name']) or $data['full_name'] == "")
	{
		$data['full_name'] = $data['full_name_2'];
	}
	
	if(!empty($data['nds_name']) and $data['nds_name'] != '' and isset($data['nds_name']))
	{
		if (!isset($data['nds_db']))
		{
			$data['nds_db'] = 0;
		}
		if (VM_VERVM == '1') 
		{
			$nds_db = $data['nds_db']/100;
		}
		else
		{
			$nds_db = $data['nds_db'];
		}
										

// SirPiter 		$sql = "SELECT ".$dba['tax_rate_id_t']."  FROM #__".$dba['tax_rate_db']." where `".$dba['tax_rate_value_t']."` = '" . $nds_db . "'";

// SirPiter Ищем налог не по ставке, а по имени
		$sql = "SELECT ".$dba['tax_rate_id_t']."  FROM #__".$dba['tax_rate_db']." where `".$dba['tax_rate_name_t']."` = '" . $data['nds_name'] . "'";

		$db->setQuery ( $sql );
		$rows_sub_Count = $db->loadResult ();
//		$log->addEntry ( array ('comment' => 'Этап 4.1.3) Ищем налог - '.$sql.' = ' .$rows_sub_Count) ); 	//SirPiter

		if (isset ( $rows_sub_Count ))
		{
			$data['nds'] = (int)$rows_sub_Count;
		}
		else
		{
			$ins = new stdClass ();
			if (VM_VERVM == '1')
			{
				$ins->tax_rate_id	=	NULL;
				$ins->vendor_id 	=	'1';
				$ins->tax_country 	=	VM_NDS_COUNTRY;
				$ins->mdate 		=	time ();
				$ins->tax_rate 		=	$nds_db;
				$ins->tax_state		=	'-';
			}
			elseif (VM_VERVM == '2')
			{
				$ins->virtuemart_calc_id	=	NULL;
				$ins->virtuemart_vendor_id	=	'1'; //Belongs to vendor
				$ins->calc_name				=	$data['nds_name']; //Name of the rule
				$ins->calc_descr			=	$data['nds_name'].' '.$nds_db.'%'; //Description
				$ins->calc_kind				=	'Tax'; //Discount/Tax/Margin/Commission	
				$ins->calc_value_mathop		=	'+%'; //the mathematical operation like (+,-,+%,-%)	
				$ins->calc_value			=	$nds_db; //The Amount	
				$ins->calc_currency			=	'131'; //Currency of the Rule	
				$ins->calc_shopper_published	=	'1'; //Visible for Shoppers	
				$ins->calc_vendor_published	=	'1'; //Visible for Vendors	
				$ins->publish_up			=	date ('Y-m-d H:i:s'); //Startdate if nothing is set = permanent	
				$ins->publish_down			=	'0000-00-00 00:00:00'; //Enddate if nothing is set = permanent	
				if(VM_VERVM_S != 'F')
				{
					$ins->calc_qualify			=	'0'; //qualifying productId's	
					$ins->calc_affected			=	'0'; //affected productId's	
					$ins->calc_amount_cond		=	'0'; //Number of affected products	
					$ins->calc_amount_dimunit	=	'0'; //The dimension, kg, m, ‚Ç¨
				}
				$ins->for_override			=	'0'; 	
				$ins->ordering				=	'0'; 	
				$ins->shared				=	'0'; 	
				$ins->published				=	'1';	
				$ins->created_on			=	date ('Y-m-d H:i:s');
				$ins->created_by			=	$id_admin;
				$ins->modified_on			=	date ('Y-m-d H:i:s');
				$ins->modified_by			=	$id_admin;
			}
			if (! $db->insertObject ( '#__'.$dba['tax_rate_db'], $ins, $dba['tax_rate_id_t'] )) 
			{
			    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - '.$dba['tax_rate_db'] , JLog::ERROR, 'vmshop_1c' );
			    if(!defined( 'VM_SITE' ))
				{
					echo 'failure\n';
					echo 'error mysql';
				}
				else
				{
					$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['tax_rate_db']."</strong>";
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
												
			if(VM_VERVM == '2')
			{
				$data['nds'] = ( int ) $ins->virtuemart_calc_id;
			}
			else
			{
				$data['nds'] = ( int ) $ins->tax_rate_id;
			}
		}
	}
	else
	{
		$data['nds'] = "";
	}
	
	if(!empty($data['image']) and $data['image'] <> '')
	{
		$data['image'] = substr ( $data['image'], 16 );
		if(substr ( $data['image'], -4 ) == 'jpeg')
		{
			$tbn_img = str_replace(".jpeg", "", $data['image']);
			$small_img = "resized".DS.$tbn_img."_".VM_TBN_H."x".VM_TBN_W.".".VM_JPG_S;
			$mimetype = 'jpeg';
		}
		else
		{
			$meta_img = substr ( $data['image'], - 3 );
			$tbn_img = str_replace(".".$meta_img, "", $data['image']);
			$small_img = "resized".DS.$tbn_img."_".VM_TBN_H."x".VM_TBN_W.".".$meta_img;
			if ($meta_img == 'jpg')
			{
				$mimetype = 'jpeg';
			}
			else
			{
				$mimetype = $meta_img;
			}
		}
		$change = true;
		$del_img = false;
		
	}
	elseif ($data['image'] == '' and $modif=='true')
	{
		$change = false;
		$data['image'] = "";
		$small_img = "";
		$del_img = false;
	}
	else
	{
		$data['image'] = "";
		$small_img = "";
		$change = true;
		$del_img = true;
	}
	
	if(empty($data['art']) or $data['art'] == '')
	{
		$data['art'] = substr((string)$data['id'],0,8);
	}
	
	if(empty($data['ves']) or $data['ves'] == '')
	{
		$data['ves'] = "0";
	}
	
	if (empty($data['status']) or $data['status'] == '')
	{
		$data['status'] = "нет";
	}
	
	if (empty($data['description']) or $data['description'] == '' or !isset($data['description']))
	{
		$data['description'] = $data['full_name'];
	}
	else
	{
		$data['description'] = nl2br($data['description']);
	}
	
	if (empty($data['category_1c_id']) or $data['category_1c_id'] == '' or !isset($data['category_1c_id']))
	{
		$data['category_1c_id'] = "";
	}
	
	if (VM_VERVM == '2')
	{
		$slug_str = str_replace("(", "", $data['name']);
		$slug_str = str_replace(")", "", $slug_str);
		$slug_str = str_replace("№", "_", $slug_str); //Аматор
		$slug_str = str_replace("%", "_", $slug_str); //Аматор
		$slug_str = str_replace("®", "_", $slug_str); //Аматор
		$slug_str = str_replace("«", "_", $slug_str); //Аматор
		$slug_str = str_replace("»", "_", $slug_str); //Аматор
		$slug_str = str_replace("#", "_", $slug_str); //Аматор

		//$slug_str = str_replace("\ ", "_", $slug_str); //Аматор
		$slug_str = str_replace(chr(9),"_",$slug_str); 		
		//$slug_str = str_replace("%20", "_", $slug_str); //Аматор
		$slug_str = str_replace(".", "_", $slug_str);
		$slug_str = str_replace("/", "_", $slug_str);
		$slug_str = str_replace("-", "_", $slug_str);
		$slug_str = str_replace("+", "_", $slug_str);
		$slug_str = str_replace("=", "_", $slug_str);
		$slug_str = str_replace("&plusmn;", "_", $slug_str);
		$slug_str = str_replace(",", "", $slug_str);
		$slug_str = str_replace("&frasl;", "_", $slug_str);
		$slug_str = str_replace("'", "", $slug_str);
		$slug_str = strtr($slug_str,":", "_");
		$slug_str = str_replace(":", "_", $slug_str);
		
		$slug_str = str_replace('"', "", $slug_str);
		$slug_str = str_replace('</br>', "", $slug_str);
		$slug_str = str_replace('<br', "", $slug_str);
		$slug_str = str_replace('/>', "", $slug_str);
		$slug_str = str_replace('•', "", $slug_str);
		$slug_str = str_replace('&#149;', "", $slug_str);
		
		$search = array ("'<script[^>]*?>.*?</script>'si",  // Вырезает javaScript
						 "'<[\/\!]*?[^<>]*?>'si",           // Вырезает HTML-теги
						 "'([\r\n])[\s]+'",                 // Вырезает пробельные символы
						 "'&(quot|#34);'i",                 // Заменяет HTML-сущности
						 "'&(amp|#38);'i",
						 "'&(lt|#60);'i",
						 "'&(gt|#62);'i",
						 "'&(nbsp|#160);'i",
						 "'&(iexcl|#161);'i",
						 "'&(cent|#162);'i",
						 "'&(pound|#163);'i",
						 "'&(copy|#169);'i",
						 "'&#(\d+);'i",
						 "'&(frasl|#8260);'i");             // интерпретировать как php-код

		$replace = array ("",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "_");
		
	// SirPiter 	$slug_str = preg_replace($search, $replace, $slug_str);
		$slug_str = preg_replace($search,
		    $replace,
		    $slug_str);
		
		//$slug_str = preg_replace("~[^-0-9A-Z_]~isU","",$slug_str);
		
		if($slug_str{0} == " ")
		{
			$slug_str = substr($slug_str, 1);
		}
		
		if (substr($slug_str, -1) == " ")
		{
			$slug_str = substr($slug_str, 0, -1);
		}
		
		$slug_name = explode ( " ", $slug_str );
			
		if (count($slug_name) > 1)
		{
			$id_slug=0;
			unset ($s_name);
			$s_name = array ();
			
			foreach ($slug_name as $snm)
			{
				$s_name[$id_slug] =  translitString($snm);
				$id_slug = $id_slug + 1;
			}
				
			$data['slug'] = implode("_", $s_name);
		}
		else
		{
			$data['slug'] =  translitString($data['name']);
		}
		if (empty ($data['slug']) or $data['slug'] == "")
		{
			$data['slug'] = $data['name'];
		}
		
		$search2 = array ("'<script[^>]*?>.*?</script>'si",  // Вырезает javaScript
						 "'<[\/\!]*?[^<>]*?>'si",           // Вырезает HTML-теги
						 "'([\r\n])[\s]+'",                 // Вырезает пробельные символы
						 "'&(quot|#34);'i",                 // Заменяет HTML-сущности
						 "'&(amp|#38);'i",
						 "'&(lt|#60);'i",
						 "'&(gt|#62);'i",
						 "'&(nbsp|#160);'i",
						 "'&(iexcl|#161);'i",
						 "'&(cent|#162);'i",
						 "'&(pound|#163);'i",
						 "'&(copy|#169);'i",
						 "'&#(\d+);'i",
						 "'\"'i",
						 "'•'i",
						 "'&#149;'i",
						 "'<br'i",
						 "'\/>'i");                    // интерпретировать как php-код

		$replace2 = array ("",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "");
		
		$key_meta = preg_replace($search2, $replace2, $data['description']);
		//$key_meta = preg_replace_callback($search2, $replace2, $data['description']);
		
		$data['metakey'] = str_replace(" ", ", ", $key_meta);
		$data['metadesc'] = $data['description'];
		$data['metarobot'] = "";
		$data['metaauthor'] = str_replace("\n", "", $username);
	}
	
//SirPiter 	//$sql = "SELECT category_id FROM #__".$dba['category_to_1c_db']." where `c_category_id` = '" . $db->getEscaped($data['category_1c_id']) . "'";
	$sql = "SELECT category_id FROM #__".$dba['category_to_1c_db']." where `c_category_id` = '" . $db->Escape($data['category_1c_id']) . "'";
	$db->setQuery ( $sql );
	$rows_sub_Count = $db->loadResult ();
	
	if(isset ( $rows_sub_Count )) 
	{
		$category_id = $rows_sub_Count;
	}
	else
	{
	    JLog::add ( 'Этап 4.1.3) Неудача: Товар '.$data['name'].' пропущен, нет категории - ' . $data['category_1c_id'], JLog::ERROR, 'vmshop_1c' );
	    $logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Товар <strong>".$data['name']."</strong> пропущен, нет категории - <strong>" . $data['category_1c_id']."</strong>";
	    JLog::add ( 'Этап 4.1.3) ' . $sql  , JLog::ERROR, 'vmshop_1c' );
	    
		$category_id = 0;
		//echo 'failure\n';
		//echo 'error category_id';
	}
		
	//SirPiter 	$sql = "SELECT product_id FROM #__".$dba['product_to_1c_db']." where `c_id` = '" . $db->getEscaped($data['id']) . "'";
	$sql = "SELECT product_id FROM #__".$dba['product_to_1c_db']." where `c_id` = '" . $db->Escape($data['id']) . "'";
	$db->setQuery ( $sql );
	
	$rows_sub_Count = $db->loadResult ();
							
	if(isset ( $rows_sub_Count ) and $rows_sub_Count != '') 
	{
		//Обновляем товар
		if ($data['status'] == 'Удален')
		{
			//Удаляем помеченный товар
			$table_del = array();
			
			$table_del[1] = $dba['product_db'];
			$table_del[2] = $dba['product_ln_db'];
			$table_del[3] = $dba['product_category_xref_db'];
			$table_del[4] = $dba['product_mf_xref_db'];
			$table_del[5] = DBBASE."_product_medias";
			$table_del[6] = $dba['product_price_db'];
			$table_del[7] = $dba['customfields_db'];
			
			$query = "DELETE FROM `#__".$dba['product_to_1c_db']."` WHERE `product_id` = '".$rows_sub_Count."'";
			$db->setQuery ( $query );
			if ($db->query ())
			{
				$logs_http[] = "<strong>Загрузка товара</strong> - Выполнен запрос № 0: (<strong>".$query."</strong>)";
				JLog::add ( 'Этап 4.1.3(product.php) Выполнен запрос № 0: ('.$query.')' , JLog::INFO, 'vmshop_1c' );
				
			}
			else
			{
				$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка запроса № 0: (<strong>".$query."</strong>)";
				JLog::add ( 'Этап 4.1.3(product.php) Неудача: Ошибка запроса № 0: ('.$query.')' , JLog::ERROR, 'vmshop_1c' );
				
			}
			
			foreach($table_del as $key => $table_del_sql)
			{
				$sql = "DELETE FROM `#__".$table_del_sql."` WHERE `".$dba['pristavka']."product_id` = '".$rows_sub_Count."'";
				$db->setQuery ( $sql );
				if ($db->query ())
				{
					$logs_http[] = "<strong>Загрузка товара</strong> - Выполнен запрос № ".$key.": (<strong>".$sql."</strong>)";
					JLog::add ( 'Этап 4.1.3(product.php) Выполнен запрос № '.$key.': ('.$sql.')' , JLog::INFO, 'vmshop_1c' );
					
				}
				else
				{
					$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка запроса № ".$key.": (<strong>".$sql."</strong>)";
					JLog::add ( 'Этап 4.1.3(product.php) Неудача: Ошибка запроса № '.$key.': ('.$sql.')' , JLog::ERROR, 'vmshop_1c' );
					
				}
			}

			$logs_http[] = "<strong>Загрузка товара</strong> - Товар id - <strong>".$rows_sub_Count."</strong> удален";
//			$log->addEntry ( array ('comment' => 'Этап 4.1.3) Товар id - ' . $rows_sub_Count . ' удален') );
			JLog::add ( 'Этап 4.1.3(product.php) Товар id - ' . $rows_sub_Count . ' удален' , JLog::INFO, 'vmshop_1c' );
			
		}
		elseif($data['status'] == 'Обновить')  // добавил проверку, чтобы убрать обновление товара. SirPiter
		{
			$sql = "SELECT * FROM #__".$dba['product_db']." where `".$dba['pristavka']."product_id` = '" . $rows_sub_Count . "'";
			$db->setQuery ( $sql );
			$rows = $db->loadObject ();
			$update = array();
			$update_ln = array();
			if($rows) 
			{
				$product_sku = $rows->product_sku;
				$product_weight = $rows->product_weight;
				
				if (VM_VERVM == '1')
				{
					$product_tax_id = $rows->product_tax_id;
					$product_thumb_image = $rows->product_thumb_image;
					$product_full_image = $rows->product_full_image;
					$product_s_desc = $rows->product_s_desc;
					$product_desc = $rows->product_desc;
					$product_name = $rows->product_name;
					
					$slug = "";
					$metadesc = "";
					$metakey = "";
					$metarobot = "";
					$metaauthor = "";
				}
				elseif (VM_VERVM == '2' and VM_VERVM_S != 'F')
				{
					$slug = $rows->slug;
					$metadesc = $rows->metadesc;
					$metakey = $rows->metakey;
					$metarobot = $rows->metarobot;
					$metaauthor = $rows->metaauthor;
					
					$product_s_desc = $rows->product_s_desc;
					$product_desc = $rows->product_desc;
					$product_name = $rows->product_name;
					
					$product_tax_id = "";
					$product_thumb_image = "";
					$product_full_image = "";
				}
				elseif (VM_VERVM == '2' and VM_VERVM_S == 'F')
				{
					$sql = "SELECT * FROM #__".$dba['product_ln_db']." where `".$dba['pristavka']."product_id` = '" . $rows_sub_Count . "'";
					$db->setQuery ( $sql );
					$rows_ln = $db->loadObject ();
					
					$slug = $rows_ln->slug;
					$metadesc = $rows_ln->metadesc;
					$metakey = $rows_ln->metakey;
					$product_s_desc = $rows_ln->product_s_desc;
					$product_desc = $rows_ln->product_desc;
					$product_name = $rows_ln->product_name;
					
					$metarobot = $rows->metarobot;
					$metaauthor = $rows->metaauthor;
					
					$product_tax_id = "";
					$product_thumb_image = "";
					$product_full_image = "";
				}
				
				$logs_http[] = "<strong>Загрузка товара</strong> - Обновляем товар id - <strong>".$rows_sub_Count."</strong>, наименование - <strong>".$product_name."</strong>";
				
				JLog::add ( 'Этап 4.1.3(product.php) Обновляем товар id - ' . $rows_sub_Count . ', наименование - '.$product_name , JLog::INFO, 'vmshop_1c' );
				
				if ($product_sku != $data['art'])
				{
					$update['sku'] = "`product_sku`='".(string)$data['art']."'";
				}
				
				if ($product_s_desc != $data['full_name'])
				{
					if (VM_VERVM_S == 'F')
					{
						$update_ln['s_desc'] = "`product_s_desc`='".(string)$data['full_name']."'";
					}
					else
					{
						$update['s_desc'] = "`product_s_desc`='".(string)$data['full_name']."'";
					}
				}
				
				if ($product_name != $data['name'])
				{
					if (VM_VERVM_S == 'F')
					{
						$update_ln['name'] = "`product_name`='".(string)$data['name']."'";
					}
					else
					{
						$update['name'] = "`product_name`='".(string)$data['name']."'";
					}
				}
				
				if ($product_desc != $data['description'])
				{
					if (VM_VERVM_S == 'F')
					{
						$update_ln['desc'] = "`product_desc`='".(string)$data['description']."'";
					}
					else
					{
						$update['desc'] = "`product_desc`='".(string)$data['description']."'";
					}
				}
				
				if ($product_weight != $data['ves'])
				{
					$update['weight'] = "`product_weight`='".(string)$data['ves']."'";
				}
				
				if ($product_tax_id != $data['nds'] and VM_VERVM == '1')
				{
					$update['tax'] = "`product_tax_id`='".(int)$data['nds']."'";
				}
				
				if ($product_full_image != $data['image'] and isset($change) and $change == true and VM_VERVM == '1')
				{
					if (substr ( $data['image'], - 4 ) == 'jpeg')
					{
						$update['full_image'] = "`product_full_image`='".str_replace(".jpeg", "", $data['image']).".".VM_JPG_S."'";
					}
					else
					{
						$update['full_image'] = "`product_full_image`='".$data['image']."'";
					}
				}
				
				if ($product_thumb_image != $small_img and isset($change) and $change == true and VM_VERVM == '1')
				{
					$update['thumb_image'] = "`product_thumb_image`='".$small_img."'";
				}
				
				if ($slug != $data['slug'] and VM_VERVM == '2' and !empty($data['slug']) and $data['slug'] != "")
				{
					if (VM_VERVM_S == 'F')
					{
						$update_ln['slug'] = "`slug`='".(string)$data['slug']."_pid_".$rows->virtuemart_product_id."'";//Аматор
					}
					else
					{
						$update['slug'] = "`slug`='".(string)$data['slug']."'";
					}
				}
				
				if ($metadesc != $data['metadesc'] and VM_VERVM == '2')
				{
					if (VM_VERVM_S == 'F')
					{
						$update_ln['metadesc'] = "`metadesc`='".(string)$data['metadesc']."'";
					}
					else
					{
						$update['metadesc'] = "`metadesc`='".(string)$data['metadesc']."'";
					}
				}
				
				if ($metakey != $data['metakey'] and VM_VERVM == '2')
				{
					if (VM_VERVM_S == 'F')
					{
						$update_ln['metakey'] = "`metakey`='".(string)$data['metakey']."'";
					}
					else
					{
						$update['metakey'] = "`metakey`='".(string)$data['metakey']."'";
					}
				}
				
				if ($metaauthor != $data['metaauthor'] and VM_VERVM == '2')
				{
					$update['metaauthor'] = "`metaauthor`='".(string)$data['metaauthor']."'";
				}
				
				if(!empty($update))
				{
					$sql_upd = "";
					
					foreach($update as $upd )
					{
						$sql_upd .= $upd.", ";
					}
					
					$sql = "UPDATE #__".$dba['product_db']." SET ".$sql_upd."".$dba['modifdate']." where ".$dba['pristavka']."product_id='".$rows_sub_Count."'";
					$db->setQuery ( $sql );
					if (!$db->query ())
					{
					    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно обновить продукт id - ' . $rows_sub_Count , JLog::ERROR, 'vmshop_1c' );
					    JLog::add ( 'Этап 4.1.3) ' . $sql , JLog::ERROR, 'vmshop_1c' );
					    if(!defined( 'VM_SITE' ))
						{
							echo 'failure\n';
							echo 'error mysql update\n';
							echo $sql;
						}
						else
						{
							$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно обновить продукт id - <strong>".$rows_sub_Count."</strong>";
							$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql."</strong>";
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
				
				if(!empty($update_ln) and VM_VERVM_S == 'F')
				{
					$sql_upd = "";
					
					foreach($update_ln as $upd )
					{
						$sql_upd .= $upd.", ";
					}
					
					$sql = "UPDATE #__".$dba['product_ln_db']." SET ".$sql_upd."`virtuemart_product_id`='".$rows_sub_Count."' where ".$dba['pristavka']."product_id='".$rows_sub_Count."'";
					$db->setQuery ( $sql );
					if (!$db->query ())
					{
					    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно обновить продукт id - ' . $rows_sub_Count , JLog::ERROR, 'vmshop_1c' );
					    JLog::add ( 'Этап 4.1.3) ' . $sql , JLog::ERROR, 'vmshop_1c' );
					    if(!defined( 'VM_SITE' ))
						{
							echo 'failure\n';
							echo 'error mysql update\n';
							echo $sql;
						}
						else
						{
							$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно обновить продукт id - <strong>".$rows_sub_Count."</strong>";
							$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql."</strong>";
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
				
				if (VM_VERVM == '2' and $change == true)
				{
					$sql = "SELECT * FROM `#__".DBBASE."_product_medias` where `virtuemart_product_id` = '" . $rows_sub_Count . "'";
					$db->setQuery ( $sql );
					$rows = $db->loadObjectList ();
					foreach ( $rows as $row ) 
					{
						
continue; //Аматор. Попробуем не удалять существующие картинки
						$sql_2 = "DELETE FROM `#__".DBBASE."_medias` WHERE `virtuemart_media_id` = '".$row->virtuemart_media_id."'";
						$db->setQuery ( $sql_2 );
						if (!$db->query ())
						{
						    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно обновить основную картинку id - ' . $rows_sub_Count , JLog::ERROR, 'vmshop_1c' );
						    JLog::add ( 'Этап 4.1.3) ' . $sql_2 , JLog::ERROR, 'vmshop_1c' );
						    if(!defined( 'VM_SITE' ))
							{
								echo 'failure\n';
								echo 'error mysql update\n';
								echo $sql_2;
							}
							else
							{
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно обновить основную картинку id - <strong>".$rows_sub_Count."</strong>";
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql_2."</strong>";
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
						
						$sql_3 = "DELETE FROM `#__".DBBASE."_product_medias` WHERE `id` = '".$row->id."'";
						$db->setQuery ( $sql_3 );
						if (!$db->query ())
						{
						    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно обновить основную картинку id - ' . $rows_sub_Count , JLog::ERROR, 'vmshop_1c' );
						    JLog::add ( 'Этап 4.1.3) ' . $sql_3 , JLog::ERROR, 'vmshop_1c' );
						    if(!defined( 'VM_SITE' ))
							{
								echo 'failure\n';
								echo 'error mysql update\n';
								echo $sql_3;
							}
							else
							{
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно обновить основную картинку id - <strong>".$rows_sub_Count."</strong>";
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql_3."</strong>";
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
					
					if ($del_img == false)
					{
						$ins = new stdClass ();
						$ins->virtuemart_media_id = NULL;
						$ins->virtuemart_vendor_id = '1';
						$ins->file_title = (string)$data['name'];
						$ins->file_description = '';//(string)$data['description'];
						$ins->file_meta = '';
						$ins->file_mimetype = 'image/'.$mimetype;
						$ins->file_type = 'product';
						if (substr ( $data['image'], - 4 ) == 'jpeg')
						{
							$ins->file_url = JPATH_PICTURE.DS.str_replace(".jpeg", "", $data['image']).".".VM_JPG_S;
						
}
						else
						{
							$ins->file_url = JPATH_PICTURE.DS.$data['image'];
	
						}
//+Аматор Проверим, нет ли уже картинки с таким УРЛ в базе

							$sql1 = "SELECT * FROM #__".DBBASE."_medias where `file_url` like '%" .str_replace(JPATH_PICTURE.DS, "", $ins->file_url) . "'";
							JLog::add ( 'Аматор 5) id - ' . $sql1 , JLog::INFO, 'vmshop_1c' );
						
							$db->setQuery ( $sql1 );
							$rows1 = $db->loadObject ();
							if (!$rows1) {

//-Аматор					
					    JLog::add ( 'Аматор 8: Добавляем медиа)' , JLog::INFO, 'vmshop_1c' );
							    
						$ins->file_url_thumb = JPATH_PICTURE.DS.$small_img;
						$ins->file_is_product_image = '1';
						$ins->file_is_downloadable = '0';
						$ins->file_is_forSale = '0';
						$ins->file_params = '';
						$ins->ordering = NULL;
						$ins->shared = '0';
						$ins->published = '1';
						$ins->created_on = date ('Y-m-d H:i:s');
						$ins->created_by = $id_admin;
						$ins->modified_on = date ('Y-m-d H:i:s');
						$ins->modified_by = $id_admin;
					
						if (! $db->insertObject ( '#__'.DBBASE.'_medias', $ins, 'virtuemart_media_id' )) 
						{
						    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - '.DBBASE.'_medias' , JLog::ERROR, 'vmshop_1c' );
						    if(!defined( 'VM_SITE' ))
							{
								echo 'failure\n';
								echo 'error mysql';
							}
							else
							{
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_medias</strong>";
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
					
						$media_id = ( int ) $ins->virtuemart_media_id;
						
						$ins = new stdClass ();
						$ins->id = NULL;
						$ins->virtuemart_product_id = (int)$rows_sub_Count;
						$ins->virtuemart_media_id = (int)$media_id;
						$ins->ordering = '0';
						
						if (! $db->insertObject ( '#__'.DBBASE.'_product_medias', $ins )) 
						{
						    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - '.DBBASE.'_product_medias' , JLog::ERROR, 'vmshop_1c' );
						    if(!defined( 'VM_SITE' ))
							{
								echo 'failure\n';
								echo 'error mysql';
							}
							else
							{
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_product_medias</strong>";
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
else {
    JLog::add ( 'Аматор 9: Картинка уже есть в базе)' , JLog::INFO, 'vmshop_1c' );

}
				}

				}
				
				if(isset($data['product_image']) and $change == true)
				{
					foreach ($data['product_image'] as $img )
					{
						$data['file'] = substr ( $img, 16 );
						if(substr ( $data['file'], -4 ) == 'jpeg')
						{
							$tbn_img = str_replace(".jpeg", "", $data['file']);
							$small_img = "resized".DS.$tbn_img."_".VM_TBN_H."x".VM_TBN_W.".".VM_JPG_S;
							$meta_img = "jpeg";
							$mimetype = $meta_img;
						}
						else
						{
							$meta_img = substr ( $data['file'], - 3 );
							$tbn_img = str_replace(".".$meta_img, "", $data['file']);
							$small_img = "resized".DS.$tbn_img."_".VM_TBN_H."x".VM_TBN_W.".".$meta_img;
							if ($meta_img == 'jpg')
							{
								$mimetype = 'jpeg';
							}
							else
							{
								$mimetype = $meta_img;
							}
						}
						
						if (VM_VERVM == '2')
						{
							$ins = new stdClass ();
							$ins->virtuemart_media_id = NULL;
							$ins->virtuemart_vendor_id = '1';
							$ins->file_title = (string)$data['name'];
							$ins->file_description = '';//(string)$data['description'];
							$ins->file_meta = '';
							$ins->file_mimetype = 'image/'.$mimetype;
							$ins->file_type = 'product';
							if (substr ( $data['file'], - 4 ) == 'jpeg')
							{
								$ins->file_url = JPATH_PICTURE.DS.str_replace(".jpeg", "", $data['file']).".".VM_JPG_S;
							}
							else
							{
								$ins->file_url = JPATH_PICTURE.DS.$data['file'];
							}
							
//+Аматор Проверим, нет ли уже картинки с таким УРЛ в базе
						
							$sql1 = "SELECT * FROM #__".DBBASE."_medias where `file_url` like '%" .str_replace(JPATH_PICTURE.DS, "", $ins->file_url) . "'";
							JLog::add ( 'Аматор 3) id - ' . $sql1 , JLog::INFO, 'vmshop_1c' );
						
							$db->setQuery ( $sql1 );
							$rows1 = $db->loadObject ();
							if ($rows1) {continue;}

//-Аматор

							$ins->file_url_thumb = JPATH_PICTURE.DS.$small_img;
							$ins->file_is_product_image = '1';
							$ins->file_is_downloadable = '0';
							$ins->file_is_forSale = '0';
							$ins->file_params = '';
							$ins->ordering = NULL;
							$ins->shared = '0';
							$ins->published = '1';
							$ins->created_on = date ('Y-m-d H:i:s');
							$ins->created_by = $id_admin;
							$ins->modified_on = date ('Y-m-d H:i:s');
							$ins->modified_by = $id_admin;
							
							if (! $db->insertObject ( '#__'.DBBASE.'_medias', $ins, 'virtuemart_media_id' )) 
							{
							    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - '.DBBASE.'_medias' , JLog::ERROR, 'vmshop_1c' );
							    if(!defined( 'VM_SITE' ))
								{
									echo 'failure\n';
									echo 'error mysql';
								}
								else
								{
									$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_medias</strong>";
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
							
							$media_id = ( int ) $ins->virtuemart_media_id;
							
							$ins = new stdClass ();
							$ins->id = NULL;
							$ins->virtuemart_product_id = (int)$rows_sub_Count;
							$ins->virtuemart_media_id = (int)$media_id;
							$ins->ordering = '0';
							
							if (! $db->insertObject ( '#__'.DBBASE.'_product_medias', $ins )) 
							{
							    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - '.DBBASE.'_product_medias' , JLog::ERROR, 'vmshop_1c' );
							    if(!defined( 'VM_SITE' ))
								{
									echo 'failure\n';
									echo 'error mysql';
								}
								else
								{
									$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_product_medias</strong>";
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
						else
						{
							$sql = "SELECT * FROM #__".DBBASE."_product_files where `file_product_id` = '" . $rows_sub_Count . "'";
							$db->setQuery ( $sql );
							$rows = $db->loadObject ();
							$update = array();
							if($rows) 
							{
								$file_id = $rows->file_id;
								$file_name = $rows->file_name;
								$file_title = $rows->file_title;
								$file_extension = $rows->file_extension;
								$file_mimetype = $rows->file_mimetype;
								$file_url = $rows->file_url;
								$file_image_thumb_height = $rows->file_image_thumb_height;
								$file_image_thumb_width = $rows->file_image_thumb_width;
								
								if ($file_name != $small_img)
								{
									$update['file_name'] = "`file_name`='".(string)$small_img."'";
								}
								if ($file_title != $data['name'])
								{
									$update['file_title'] = "`file_title`='".(string)$data['name']."'";
								}
								if ($file_extension != $meta_img)
								{
									$update['file_extension'] = "`file_extension`='".(string)$meta_img."'";
								}
								if ($file_mimetype != 'image/'.$meta_img)
								{
									$update['file_mimetype'] = "`file_mimetype`='image/".$meta_img."'";
								}
								
								
								if (substr ( $data['file'], - 4 ) == 'jpeg')
								{
									if ($file_url != 'components'.DS.'com_virtuemart'.DS.'shop_image'.DS.'product'.DS.str_replace(".jpeg", "", $data['file']).".".VM_JPG_S)
									{
										$update['file_url'] = "`file_url`='components".DS."com_virtuemart".DS."shop_image".DS."product".DS.str_replace(".jpeg", "", $data['file']).".".VM_JPG_S."'";
									}
								}
								else
								{
									if ($file_url != 'components'.DS.'com_virtuemart'.DS.'shop_image'.DS.'product'.DS.$data['file'])
									{
										$update['file_url'] = "`file_url`='components".DS."com_virtuemart".DS."shop_image".DS."product".DS.$data['file']."'";
									}
								}
								
								if ($file_image_thumb_height != VM_TBN_H)
								{
									$update['file_image_thumb_height'] = "`file_image_thumb_height`='".(int)VM_TBN_H."'";
								}
								if ($file_image_thumb_width != VM_TBN_W)
								{
									$update['file_image_thumb_width'] = "`file_image_thumb_width`='".(int)VM_TBN_W."'";
								}
								
								if(!empty($update))
								{
									$sql_upd = "";
									
									foreach($update as $upd )
									{
										$sql_upd .= $upd.", ";
									}
									
									$sql = "UPDATE #__".DBBASE."_product_files SET ".$sql_upd."file_is_image=1 where file_id='".$file_id."'";
									$db->setQuery ( $sql );
									if (!$db->query ())
									{
									    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно обновить дополнительную картинку id - ' . $file_id  , JLog::ERROR, 'vmshop_1c' );
									    JLog::add ( 'Этап 4.1.3) ' . $sql , JLog::ERROR, 'vmshop_1c' );
									    if(!defined( 'VM_SITE' ))
										{
											echo 'failure\n';
											echo 'error mysql update\n';
											echo $sql;
										}
										else
										{
											$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно обновить дополнительную картинку id - <strong>".$file_id."</strong>";
											$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql."</strong>";
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
							else
							{
								$ins = new stdClass ();
								$ins->file_id = NULL;
								$ins->file_product_id = ( int )$rows_sub_Count;
								$ins->file_name = $small_img;
								$ins->file_title = (string)$data['name'];
								$ins->file_description = '';
								$ins->file_extension = $meta_img;
								$ins->file_mimetype = 'image/'.$meta_img;
								if (substr ( $data['file'], - 4 ) == 'jpeg')
								{
									$ins->file_url = 'components'.DS.'com_virtuemart'.DS.'shop_image'.DS.'product'.DS .str_replace(".jpeg", "", $data['file']).".".VM_JPG_S;
								}
								else
								{
									$ins->file_url = 'components'.DS.'com_virtuemart'.DS.'shop_image'.DS.'product'.DS . $data['file'];
								}
								$ins->file_published = '1';
								$ins->file_is_image = '1';
								$ins->file_image_height = '';
								$ins->file_image_width = '';
								$ins->file_image_thumb_height = VM_TBN_H;
								$ins->file_image_thumb_width = VM_TBN_W;
															
								if (! $db->insertObject ( '#__'.DBBASE.'_product_files', $ins, 'file_id' )) 
								{
								    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - vm_product_files'  , JLog::ERROR, 'vmshop_1c' );
								    
									if(!defined( 'VM_SITE' ))
									{
										echo 'failure\n';
										echo 'error mysql\n';
									}
									else
									{
										$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>vm_product_files</strong>";
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
				}
			}
			else
			{
			    JLog::add ( 'Этап 4.1.3) Неудача: Нет данных по продукту id - ' . $rows_sub_Count , JLog::ERROR, 'vmshop_1c' );
			    
				if(!defined( 'VM_SITE' ))
				{
					echo 'failure\n';
					echo 'error mysql\n';
				}
				else
				{
					$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Нет данных по продукту id - <strong>".$rows_sub_Count."</strong>";
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
	else
	{
		//Добавляем новый товар
		if ($data['status'] != 'Удален' and $category_id <> 0)
		{
		//	$log->addEntry ( array ('comment' => '--------------Добавляем товар: '.$data['name'].'--------------' ) );
		    JLog::add ( '--------------Добавляем товар: '.$data['name'].'--------------' , JLog::INFO, 'vmshop_1c' );
		    $logs_http[] = "<strong>Загрузка товара</strong> - --------------Добавляем товар: <strong>".$data['name']."</strong>--------------";
			
			if (VM_VERVM == '2')
			{
				$product_special = '0';
			}
			else
			{
				$product_special = 'N';
			}
			
			$ins = new stdClass ();
			$ins->product_parent_id = '0';
			$ins->product_sku = (string)$data['art'];							//!!!!!!!!!!!!!!!!!
			if (VM_VERVM_S != 'F')
			{
				$ins->product_s_desc = (string)$data['full_name'];						//!!!!!!!!!!!!!!!!!
				$ins->product_desc = (string)$data['description'];					//!!!!!!!!!!!!!!!!!
				$ins->product_name = (string)$data['name'];							//!!!!!!!!!!!!!!!!!
			}

			$ins->product_weight = (double)$data['ves'];								//!!!!!!!!!!!!!!!!!
			$ins->product_weight_uom = 'KG';
			$ins->product_length = (double)$data['dlina'];
			$ins->product_width = (double)$data['shirina'];
			$ins->product_height = (double)$data['visota'];
			$ins->product_lwh_uom = 'CM';
			$ins->product_url = "";
			$ins->product_in_stock = "0";
			$ins->product_special = $product_special;
			$ins->ship_code_id = NULL;
			$ins->product_sales = "0";
			$ins->product_packaging = "0";
			if (! isset ( $data['baz_ed'] )) {
				$ins->product_unit = 'piece';
			}
			else 
			{
				$ins->product_unit = $data['baz_ed'].".";
			}
			
			if(VM_VERVM == '2')
			{
				$ins->virtuemart_product_id = NULL;
				$ins->virtuemart_vendor_id = '1';
				$ins->product_ordered = '0';
				$ins->low_stock_notification = '5';
				$ins->hits = '0';
				$ins->intnotes = NULL;
				if (VM_VERVM_S != 'F')
				{
					$ins->slug = (string)$data['slug'];
					$ins->metadesc = (string)$data['metadesc'];
					$ins->metakey = (string)$data['metakey'];
				}
				$ins->metarobot = (string)$data['metarobot'];
				$ins->metaauthor = (string)$data['metaauthor'];
				$ins->layout = '0';
				$ins->published = VM_PUBLISH_NEW_PRODUCT;   	// SirPiter : публикация нового товара задается в config.php
				$ins->created_on = date ('Y-m-d H:i:s');
				$ins->created_by = $id_admin;
				$ins->modified_on = date ('Y-m-d H:i:s');
				$ins->modified_by = $id_admin;
				//$ins->product_params =  (string)'min_order_level=s:1:"0";|max_order_level=s:1:"0";|min_order_level=s:1:"0";|max_order_level=s:1:"0";|';
				$ins->product_params = '';
				if (VM_POSTAVKA_E == 'yes')
				{
					$available_date = time() + VM_POSTAVKA_TIME;
					$available_date = date ('Y-m-d H:i:s', $available_date);
					
					$ins->product_available_date = $available_date;								//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
					$ins->product_availability = VM_POSTAVKA;					//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				}
				else
				{
					$ins->product_available_date = date ('Y-m-d H:i:s');								//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
					$ins->product_availability = "on-order.gif";					//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				}
			}
			else
			{
				
				$ins->product_id = NULL;
				$ins->vendor_id = '1';
				$ins->product_thumb_image = $small_img;
				if (substr ( $data['image'], - 4 ) == 'jpeg')
				{
					$ins->product_full_image = str_replace(".jpeg", "", $data['image']).".".VM_JPG_S;
				}
				else
				{
					$ins->product_full_image = $data['image'];
				}
				$ins->product_publish = "Y";
				$ins->product_discount_id = "";
				$ins->cdate = time ();
				$ins->mdate = time ();	
				$ins->attribute = "";
				$ins->custom_attribute = "";
				$ins->product_tax_id = (int)$data['nds'];							//!!!!!!!!!!!!!!!!!
				$ins->child_options = "N,N,N,N,N,N,20%,10%,";
				$ins->quantity_options = "none,0,0,1";
				$ins->child_option_ids = "";
				$ins->product_order_levels = "1,10";	
				
				if (VM_POSTAVKA_E == 'yes')
				{
					$ins->product_available_date = time() + VM_POSTAVKA_TIME;								//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
					$ins->product_availability = VM_POSTAVKA;					//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				}
				else
				{
					$ins->product_available_date = time();								//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
					$ins->product_availability = "on-order.gif";					//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				}
			}
			
			JLog::add ( 'Этап 4.1.3) Пытаемся вставить запись в таблицу - '.$dba['product_db'] , JLog::INFO, 'vmshop_1c' );
	//		JLog::add ( 'Этап 4.1.3) '. print_r($ins) , JLog::INFO, 'vmshop_1c' );
			
			if (! $db->insertObject ( '#__'.$dba['product_db'], $ins, $dba['pristavka'].'product_id' )) 
			{
			    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - '.$dba['product_db'] , JLog::ERROR, 'vmshop_1c' );
			    if(!defined( 'VM_SITE' ))
				{
					echo 'failure\n';
					echo 'error mysql\n';
				}
				else
				{
					$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['product_db']."</strong>";
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
					
			if (VM_VERVM == '2')
			{
				$produkt_id = ( int ) $ins->virtuemart_product_id;
			}
			else
			{
				$produkt_id = ( int ) $ins->product_id;
			}
			
			if (VM_VERVM_S == 'F')
			{
				$slug = $data['slug']."_pid_".$produkt_id;
				
				$ins = new stdClass ();
				$ins->virtuemart_product_id = $produkt_id;
				$ins->product_s_desc = (string)$data['full_name'];						//!!!!!!!!!!!!!!!!!
				$ins->product_desc = (string)$data['description'];					//!!!!!!!!!!!!!!!!!
				$ins->product_name = (string)$data['name'];							//!!!!!!!!!!!!!!!!!
				$ins->slug = (string)$slug;
				$ins->metadesc = (string)$data['metadesc'];
				$ins->metakey = (string)$data['metakey'];
				
				if (! $db->insertObject ( '#__'.$dba['product_ln_db'], $ins )) 
				{
				    JLog::add ( 'Этап 4.1.3) Неудача: Таблица '.$dba['product_ln_db'].' Невозможно вставить запись для продукта - '.$data['name'] , JLog::ERROR, 'vmshop_1c' );
				    if(!defined( 'VM_SITE' ))
					{
						echo 'failure\n';
						echo 'error mysql\n';
					}
					else
					{
						$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись для продукта - <strong>".$data['name']."</strong>";
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
			
			if(VM_VERVM == '2' and VM_VERVM_S != 'F')
			{
				$sql = "UPDATE #__".$dba['product_db']." SET 
											`slug` = '".$data['slug']."_pid_".$produkt_id."'
								where `virtuemart_product_id`='".$produkt_id."'";
				$db->setQuery ( $sql );
				$db->query ();
			}
			
			$ins = new stdClass ();
			if (VM_VERVM == '2')
			{
				$ins->virtuemart_product_id  = ( int )$produkt_id;
				$ins->virtuemart_category_id = ( int )$category_id;
				$ins->ordering   = NULL;
			}
			else
			{
				$ins->category_id = ( int )$category_id;
				$ins->product_id  = ( int )$produkt_id;
				$ins->product_list   = '1';
			}
			
			if (! $db->insertObject ( '#__'.$dba['product_category_xref_db'], $ins )) 
			{
			    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - '.$dba['product_category_xref_db'] , JLog::ERROR, 'vmshop_1c' );
			    if(!defined( 'VM_SITE' ))
				{
					echo 'failure\n';
					echo 'error mysql\n';
				}
				else
				{
					$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['product_category_xref_db']."</strong>";
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
			
			$ins = new stdClass ();
			$ins->product_id = ( int )$produkt_id;
//SirPiter			$ins->c_id = (string)$db->getEscaped($data['id']);
			$ins->c_id = (string)$db->Escape($data['id']);
			$ins->tax_id = (int)$data['nds'];
			
			JLog::add ( 'Этап 4.1.3) Пытаемся вставить запись в таблицу - '.$dba['product_to_1c_db'] , JLog::INFO, 'vmshop_1c' );
			JLog::add ( 'Этап 4.1.3) '.$ins->c_id , JLog::INFO, 'vmshop_1c' );
			
			if (! $db->insertObject ( '#__'.$dba['product_to_1c_db'], $ins )) 
			{
			    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - '.$dba['product_to_1c_db'] , JLog::ERROR, 'vmshop_1c' );
			    if(!defined( 'VM_SITE' ))
				{
					echo 'failure\n';
					echo 'error mysql\n';
				}
				else
				{
					$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['product_to_1c_db']."</strong>";
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
			
			if (isset ($data['manufacturer']) and $data['manufacturer'] != "")
			{
				if (VM_VERVM == '2')
				{
					//$sql = "SELECT virtuemart_manufacturer_id FROM #__".$dba['manufacturer_ln_db']." where `mf_name` = '" . $data['manufacturer'] . "'";
				$sql = "SELECT virtuemart_manufacturer_id FROM #__".$dba['manufacturer_ln_db']." where `slug` = '" . $data['manufacturer'] . "'";
// Аматор - Ищем по слугу, а не по наименованию
				
}
				else
				{
					$sql = "SELECT manufacturer_id FROM #__".$dba['manufacturer_db']." where `mf_name` = '" . $data['manufacturer'] . "'";
				}
				$db->setQuery ( $sql );
				$rows_sub_Count = $db->loadResult ();
				
				$ins = new stdClass ();
				if (VM_VERVM == '2')
				{
					$ins->id = NULL;
					$ins->virtuemart_product_id = ( int )$produkt_id;
					$ins->virtuemart_manufacturer_id = ( int )$rows_sub_Count;
				}
				else
				{
					$ins->product_id = ( int )$produkt_id;
					$ins->manufacturer_id = ( int )$rows_sub_Count;
				}
				
				if (! $db->insertObject ( '#__'.$dba['product_mf_xref_db'], $ins )) 
				{
				    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - '.$dba['product_mf_xref_db'] , JLog::ERROR, 'vmshop_1c' );
				    if(!defined( 'VM_SITE' ))
					{
						echo 'failure\n';
						echo 'error mysql\n';
					}
					else
					{
						$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['product_mf_xref_db']."</strong>";
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
					$logs_http[] = "<strong>Загрузка товара</strong> - Добавляем производителя - <strong>".$data['manufacturer']."</strong> к продукту <strong>".$data['name']."</strong>";
				}
			}
			
			if (VM_VERVM == '2' and !empty($data['image']) and $data['image'] <> '')
			{
				$ins = new stdClass ();
				$ins->virtuemart_media_id = NULL;
				$ins->virtuemart_vendor_id = '1';
				$ins->file_title = (string)$data['name'];
				$ins->file_description = '';//(string)$data['description'];
				$ins->file_meta = '';
				$ins->file_mimetype = 'image/'.$mimetype;
				$ins->file_type = 'product';
				if (substr ( $data['image'], - 4 ) == 'jpeg')
				{
					$ins->file_url = JPATH_PICTURE.DS.str_replace(".jpeg", "", $data['image']).".".VM_JPG_S;
				}
				else
				{
					$ins->file_url = JPATH_PICTURE.DS.$data['image'];
				}
				
//+Аматор Проверим, нет ли уже картинки с таким УРЛ в базе

							$sql1 = "SELECT * FROM #__".DBBASE."_medias where `file_url` = '" . $ins->file_url . "'";
							JLog::add ( 'Аматор 7) id - ' . $sql1 , JLog::INFO, 'vmshop_1c' );
							$db->setQuery ( $sql1 );
							$rows1 = $db->loadObject ();
						//	if ($rows1) {continue;}

//-Аматор

				$ins->file_url_thumb = JPATH_PICTURE.DS.$small_img;
				$ins->file_is_product_image = '1';
				$ins->file_is_downloadable = '0';
				$ins->file_is_forSale = '0';
				$ins->file_params = '';
				$ins->ordering = NULL;
				$ins->shared = '0';
				$ins->published = '1';
				$ins->created_on = date ('Y-m-d H:i:s');
				$ins->created_by = $id_admin;
				$ins->modified_on = date ('Y-m-d H:i:s');
				$ins->modified_by = $id_admin;
					
				if (! $db->insertObject ( '#__'.DBBASE.'_medias', $ins, 'virtuemart_media_id' )) 
				{
				    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - '.DBBASE.'_medias' , JLog::ERROR, 'vmshop_1c' );
				    if(!defined( 'VM_SITE' ))
					{
						echo 'failure\n';
						echo 'error mysql\n';
					}
					else
					{
						$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_medias</strong>";
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
							
				$media_id = ( int ) $ins->virtuemart_media_id;
				
				$ins = new stdClass ();
				$ins->id = NULL;
				$ins->virtuemart_product_id = ( int )$produkt_id;
				$ins->virtuemart_media_id = (int)$media_id;
				$ins->ordering = '0';
				
				if (! $db->insertObject ( '#__'.DBBASE.'_product_medias', $ins )) 
				{
				    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - '.DBBASE.'_product_medias' , JLog::ERROR, 'vmshop_1c' );
				    if(!defined( 'VM_SITE' ))
					{
						echo 'failure\n';
						echo 'error mysql\n';
					}
					else
					{
						$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_product_medias</strong>";
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
			
			
			if (VM_XML_VERS == "203") 
			{
			for ($q=0; $q < count($cvid); $q++)
					{
						makecustoms($data,$produkt_id,$cvid[$q],$cvzn[$q]);
					}
			}


			if(isset($data['product_image']))
			{
				foreach ($data['product_image'] as $img )
				{
					$data['file'] = substr ( $img, 16 );
					if(substr ( $data['file'], -4 ) == 'jpeg')
					{
						$tbn_img = str_replace(".jpeg", "", $data['file']);
						$small_img = "resized".DS.$tbn_img."_".VM_TBN_H."x".VM_TBN_W.".".VM_JPG_S;
						$meta_img = "jpeg";
						$mimetype = $meta_img;
					}
					else
					{
						$meta_img = substr ( $data['file'], - 3 );
						$tbn_img = str_replace(".".$meta_img, "", $data['file']);
						$small_img = "resized".DS.$tbn_img."_".VM_TBN_H."x".VM_TBN_W.".".$meta_img;
						if ($meta_img == 'jpg')
						{
							$mimetype = 'jpeg';
						}
						else
						{
							$mimetype = $meta_img;
						}
					}
					
					if (VM_VERVM == '2')
					{
						$ins = new stdClass ();
						$ins->virtuemart_media_id = NULL;
						$ins->virtuemart_vendor_id = '1';
						$ins->file_title = (string)$data['name'];
						$ins->file_description = '';//(string)$data['description'];
						$ins->file_meta = '';
						$ins->file_mimetype = 'image/'.$mimetype;
						$ins->file_type = 'product';
						if (substr ( $data['file'], - 4 ) == 'jpeg')
						{
							$ins->file_url = JPATH_PICTURE.DS.str_replace(".jpeg", "", $data['file']).".".VM_JPG_S;
						}
						else
						{
							$ins->file_url = JPATH_PICTURE.DS.$data['file'];
						}
						$ins->file_url_thumb = JPATH_PICTURE.DS.$small_img;
						$ins->file_is_product_image = '1';
						$ins->file_is_downloadable = '0';
						$ins->file_is_forSale = '0';
						$ins->file_params = '';
						$ins->ordering = NULL;
						$ins->shared = '0';
						$ins->published = '1';
						$ins->created_on = date ('Y-m-d H:i:s');
						$ins->created_by = $id_admin;
						$ins->modified_on = date ('Y-m-d H:i:s');
						$ins->modified_by = $id_admin;
							
						if (! $db->insertObject ( '#__'.DBBASE.'_medias', $ins, 'virtuemart_media_id' )) 
						{
						    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - '.DBBASE.'_medias' , JLog::ERROR, 'vmshop_1c' );
						    if(!defined( 'VM_SITE' ))
							{
								echo 'failure\n';
								echo 'error mysql\n';
							}
							else
							{
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_medias</strong>";
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
									
						$media_id = ( int ) $ins->virtuemart_media_id;
						
						$ins = new stdClass ();
						$ins->id = NULL;
						$ins->virtuemart_product_id = ( int )$produkt_id;
						$ins->virtuemart_media_id = (int)$media_id;
						$ins->ordering = '0';
						
						if (! $db->insertObject ( '#__'.DBBASE.'_product_medias', $ins )) 
						{
						    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - '.DBBASE.'_product_medias' , JLog::ERROR, 'vmshop_1c' );
						    if(!defined( 'VM_SITE' ))
							{
								echo 'failure\n';
								echo 'error mysql\n';
							}
							else
							{
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_product_medias</strong>";
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
					else
					{
						$ins = new stdClass ();
						$ins->file_id = NULL;
						$ins->file_product_id = ( int )$produkt_id;
						$ins->file_name = $small_img;
						$ins->file_title = (string)$data['name'];
						$ins->file_description = '';
						$ins->file_extension = $meta_img;
						$ins->file_mimetype = 'image/'.$meta_img;
						if (substr ( $data['file'], - 4 ) == 'jpeg')
						{
							$ins->file_url = 'components'.DS.'com_virtuemart'.DS.'shop_image'.DS.'product'.DS .str_replace(".jpeg", "", $data['file']).".".VM_JPG_S;
						}
						else
						{
							$ins->file_url = 'components'.DS.'com_virtuemart'.DS.'shop_image'.DS.'product'.DS . $data['file'];
						}
						$ins->file_published = '1';
						$ins->file_is_image = '1';
						$ins->file_image_height = '';
						$ins->file_image_width = '';
						$ins->file_image_thumb_height = VM_TBN_H;
						$ins->file_image_thumb_width = VM_TBN_W;
													
						if (! $db->insertObject ( '#__'.$dba['product_files_db'], $ins, 'file_id' )) 
						{
						    JLog::add ( 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - '.$dba['product_files_db'] , JLog::ERROR, 'vmshop_1c' );
						    if(!defined( 'VM_SITE' ))
							{
								echo 'failure\n';
								echo 'error mysql\n';
							}
							else
							{
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['product_files_db']."</strong>";
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
			
			$logs_http[] = "<strong>Загрузка товара</strong> - Товар - <strong>".$data['name']."</strong> добавлен";
			JLog::add ( 'Этап 4.1.3) Товар - ' . $data['name'] . ' добавлен' , JLog::INFO, 'vmshop_1c' );
		}
		
	}


}

?>