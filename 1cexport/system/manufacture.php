<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: system/manufacture.php - Класс создания производителей

//			    Amator  (email: amatoravg@gmail.com)

//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

$manuf = new XMLReader();

function inserManufacture($xml) 
{
	global $log, $db, $dba, $id_admin, $manuf, $lang_1c;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
	
	$manuf->XML($xml);
					
	$data = array();

	$data['id'] = "";
	$data['name'] = "";
	$data['manuf'] = "";
	$data['slug'] = ""; //Аматор


	while($manuf->read()) 
	{
		if($manuf->nodeType == XMLReader::ELEMENT ) 
		{
			switch($manuf->name) 
			{
							
				case 'Ид': 
					$data['id'] = $manuf->readString();
									
					break;
									
				case 'Наименование':
					$data['name'] = trim($manuf->readString());
					
					
					
					if ($data['name'] != VM_MANUFACTURE) 
	{
	//Аматор Попытаемся создать доп. поля - это свойство, но не производитель!
	
// SirPiter дополнительные поля не нужны 
	$lang_1c[0] = $data['name'];	//Аматор - Создадим доп. поле с названием свойства
	require_once(JPATH_BASE_1C .DS.'system'.DS.'customfields.php');
	
	$custom_id = customfields($data['id']);		
							//$znachhar = ( string )$harakteristiki->Значение;
							
							//for ($q=0; $q < count($lang_1c); $q++)
							//{
							//	if($lang_1c[$q] == $namehar)
							//	{
							//		$harakt[$q] = $znachhar;
							//	}
							//}
	
	}
					
					
					
					
					
					
					break;
									
				case 'ВариантыЗначений': 
					//$xml_man = $manuf->readOuterXML();
					//$xml_man = $manuf->readInnerXML();					
					//$DataId = $data['id'];
					//$DataName = $data['name'];
					//Сохраним
					$xml_man = simplexml_load_string($manuf->readOuterXML());
					foreach($xml_man as $harakteristiki)
						{
							

					


					$data['manuf'] = $harakteristiki->Значение;	
					$data['slug'] = $harakteristiki->ИдЗначения;

					makeManufacture($data);
						}


					unset($xml_man);
					
					$manuf->next();
					
					break;
			}
		}
	}
	
	
}

function makeManufacture($data) 
{
	global $log, $db, $dba, $id_admin;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
	

	if (isset($data['name']) and $data['name'] != "" and $data['name'] == VM_MANUFACTURE and isset($data['manuf']) and $data['manuf'] != "")
	{
		/*if (VM_VERVM == '1')
		{
			$sql = "SELECT ".$dba['pristavka']."manufacturer_id FROM #__".$dba['manufacturer_db']." where `c_id` = '" . $data['id'] . "'";
		}
		else
		{
			$sql = "SELECT ".$dba['pristavka']."manufacturer_id 
				FROM #__".$dba['manufacturer_db']." as man 
				LEFT JOIN #__".$dba['manufacturer_ln_db']." as man_ln 
				ON (man.".$dba['pristavka']."manufacturer_id=man_ln.".$dba['pristavka']."manufacturer_id) 
				WHERE man_ln.mf_name = '".$data['manuf']."'";
		}*/
	
		$sql = "SELECT manufacturer_id FROM #__".$dba['manufacturer_to_1c_db']." where `c_manufacturer_id` = '" . $data['id'] . "'";
		$db->setQuery ( $sql );
		$rows_sub_Count = $db->loadResult ();
		JLog::add ( 'Аматор 2: )'.$sql , JLog::INFO, 'vmshop_1c' );
		if (isset($rows_sub_Count) and $rows_sub_Count != '')
		{
	
		}
		else
		{
			$ins = new stdClass ();
			$ins->manufacturer_id = '1';//$man_id;
			$ins->c_manufacturer_id = $data['id'];
			
			if (! $db->insertObject ( '#__'.$dba['manufacturer_to_1c_db'], $ins )) 
			{
			    JLog::add ( 'Этап 4.1.2) Неудача: Невозможно вставить запись в таблицу - '.$dba['manufacturer_to_1c_db']  , JLog::ERROR, 'vmshop_1c' );
			    
				if(!defined( 'VM_SITE' ))
				{
					echo 'failure\n';
					echo 'error mysql';
				}
				else
				{
					$logs_http[] = "<strong>Производители</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['manufacturer_to_1c_db']."</strong>";
				}
				die;
			}
		}
		
		//Аматор Поищем, нет ли в базе уже такого производителя
		$sql = "SELECT virtuemart_manufacturer_id FROM #__".$dba['manufacturer_ln_db']." where `slug` = '" . (string)$data['slug'] . "'";
		$db->setQuery ( $sql );
		$rows_sub_Count = $db->loadResult ();
		JLog::add ( 'Аматор 4: )'.$sql  , JLog::DEBUG, 'vmshop_1c' );
		if (!(isset($rows_sub_Count) and $rows_sub_Count != ''))
		{
	
			//Добавляем
			$ins = new stdClass ();
			
				$ins->virtuemart_manufacturer_id = NULL;
				$ins->virtuemart_manufacturercategories_id = '0';
				$ins->hits = '0';
				$ins->published = '1';
				$ins->created_on = date ('Y-m-d H:i:s');
				$ins->created_by = $id_admin;
				$ins->modified_on = date ('Y-m-d H:i:s');
				$ins->modified_by = $id_admin;
			
			
			
			if (! $db->insertObject ( '#__'.$dba['manufacturer_db'], $ins, $dba['pristavka']."manufacturer_id" )) 
			{
			    JLog::add ( 'Этап 4.1.2) Неудача: Невозможно вставить запись в таблицу - '.$dba['manufacturer_db']  , JLog::ERROR, 'vmshop_1c' );
			    
				if(!defined( 'VM_SITE' ))
				{
					echo 'failure\n';
					echo 'error mysql';
				}
				else
				{
					$logs_http[] = "<strong>Производители</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['manufacturer_db']."</strong>";
				}
				die;
			}
				$man_id = ( int ) $ins->virtuemart_manufacturer_id;
				
				$slug_str = str_replace("(", "", $data['manuf']);
				$slug_str = str_replace(")", "", $slug_str);
				$slug_str = str_replace(".", "_", $slug_str);
				$slug_str = str_replace("/", "_", $slug_str);
				$slug_str = str_replace("-", "_", $slug_str);
				$slug_str = str_replace("+", "_", $slug_str);
				$slug_str = str_replace("=", "_", $slug_str);
				$slug_str = str_replace("&plusmn;", "_", $slug_str);
				$slug_str = str_replace(",", "", $slug_str);
				$slug_str = str_replace("&frasl;", "_", $slug_str);
				$slug_str = str_replace("&#8260;", "_", $slug_str);
				$slug_str = str_replace(":", "_", $slug_str);
				$slug_str = strtr($slug_str,"&frasl;", "_");
				$slug_str = strtr($slug_str,"&#8260;", "_");
				$slug_str = strtr($slug_str,":", "_");
				
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
					
					$slug = implode("_", $s_name);
				}
				else
				{
					$slug =  translitString($data['manuf']);
				}
				
				if (empty ($slug) or $slug == "")
				{
					$slug = $name;
				}
				
				$slug = str_replace("/", "_", $slug);
				$slug = str_replace("-", "_", $slug);
				
				$ins = new stdClass ();
				$ins->virtuemart_manufacturer_id = (int)$man_id;
				$ins->mf_name = (string)$data['manuf'];
				$ins->mf_email = '';
				$ins->mf_desc = (string)$data['manuf'];
				$ins->mf_url = '';
				//$ins->slug = (string)$slug;
				$ins->slug = (string)$data['slug']; //Аматор - Будем хранить тут не трансилтерированное значение, а УИН, чтобы потом по нему подставить производителя, когда будем создавать товар.


				if (! $db->insertObject ( '#__'.$dba['manufacturer_ln_db'], $ins )) 
				{
				    JLog::add ( 'Аматор 3: )'.$ins->mf_name , JLog::INFO, 'vmshop_1c' );
				    
				    JLog::add ( 'Этап 4.1.2) Неудача: Невозможно вставить запись в таблицу - '.$dba['manufacturer_ln_db'] , JLog::ERROR, 'vmshop_1c' );
				    
					if(!defined( 'VM_SITE' ))
					{
						echo 'failure\n';
						echo 'error mysql';
					}
					else
					{
						$logs_http[] = "<strong>Производители</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['manufacturer_ln_db']."</strong>";
					}
					die;
				}
				
				JLog::add ( 'Этап 4.1.2) Производитель '.$data['manuf'].' создан' , JLog::INFO, 'vmshop_1c' );
				$logs_http[] = "<strong>Производители</strong> - Производитель <strong>".$data['manuf']."</strong> создан";
		}
		
	}
	

}
?>