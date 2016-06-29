<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: system/cashgroup.php - Класс создания групп покупателей

//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

function inserCashgroup($xml) 
{
	global $log, $db, $cash_group;
	
	$cash_group->XML($xml);
					
	$data = array();

	$data['id'] = "";
	$data['name'] = "";
	$data['nds'] = "";

	while($cash_group->read()) 
	{
		if($cash_group->nodeType == XMLReader::ELEMENT ) 
		{
			switch($cash_group->name) 
			{
							
				case 'Ид': 
					$data['id'] = $cash_group->readString();
					break;
									
				case 'Наименование':
					$data['name'] = trim($cash_group->readString());
					break;
									
				case 'Налог': 
					$xml_nds = $cash_group->readOuterXML();
					$xml_nds = simplexml_load_string($xml_nds);
					$data['nds'] = $xml_nds->УчтеноВСумме;	
					$data['nds_name'] = $xml_nds->Наименование;
					
					unset($xml_nds);
					
					$cash_group->next();
					break;
			}
		}
	}
	
	makeCashgroup($data);
}

function makeCashgroup($data) 
{
	global $log, $db, $dba, $id_admin;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
	
	$sql = "SELECT cashgroup_id FROM #__".$dba['cashgroup_to_1c_db']." where `c_id` = '" . $data['id'] . "'";
	$db->setQuery ( $sql );
	$rows_sub_Count = $db->loadResult ();
							
	if(!isset ( $rows_sub_Count ) or $rows_sub_Count == '' or $rows_sub_Count < 0) 
	{
		//$log->addEntry ( array ('comment' => 'Этап 4.2.2) Обновляем группу покупателей id - '.$rows_sub_Count ) );
		//$logs_http[] = "<strong>Загрузка цен</strong> - Обновляем группу покупателей id - <strong>".$rows_sub_Count."</strong>";
		
		if ($data['nds'] == true)
		{
			$nds = "1";
		}
		else
		{
			$nds = "0";
		}
		
		if($data['name'] == VM_DEF_CASHGR)
		{
			if (VM_VERVM == '2')
			{
				$def = "2";
			}
			else
			{
				$def = "1";
			}
		}
		else
		{
			$def = "0";
		}
		
		if (VM_VERVM == '2')
		{
			$param ="show_prices=1\n";
		
			if(!class_exists('JParameter')) require(JPATH_BASE.DS.'libraries'.DS.'joomla'.DS.'html'.DS.'parameter.php' );
				$jparam = new JParameter($param);
			$data['price_display'] = serialize(new JParameter($param));
		}
		$ins = new stdClass ();
		$ins->shopper_group_name = (string)$data['name'];
		$ins->shopper_group_desc = (string)$data['name'];
		$ins->default = (int)$def;
		if (VM_VERVM == '2')
		{
			$ins->virtuemart_shoppergroup_id = NULL;
			$ins->virtuemart_vendor_id = '1';
			$ins->custom_price_display = '0';
			$ins->price_display = $data['price_display'];
			$ins->ordering = '0';
			$ins->shared = '0';
			$ins->published = '1';
			$ins->created_on = date ('Y-m-d H:i:s');
			$ins->created_by = $id_admin;
			$ins->modified_on = date ('Y-m-d H:i:s');
			$ins->modified_by = $id_admin;
		}
		else
		{
			$ins->shopper_group_id = NULL;
			$ins->vendor_id = '1';
			$ins->shopper_group_discount = '0.00';	
			$ins->show_price_including_tax = (int)$nds;
		}
		if (! $db->insertObject ( '#__'.$dba['shopper_group_db'], $ins, $dba['shopper_group_id_t'] )) 
		{
			$log->addEntry ( array ('comment' => 'Этап 4.2.2) Неудача: Невозможно вставить запись в таблицу - '.$dba['shopper_group_db'] ) );
			
			if(!defined( 'VM_SITE' ))
			{
				echo 'failure\n';
				echo 'error mysql';
			}
			else
			{
				$logs_http[] = "<strong>Загрузка цен</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['shopper_group_db']."</strong>";
			}
			die;
		}
					
		if (VM_VERVM == '2')
		{
			$shopper_group_id = ( int ) $ins->virtuemart_shoppergroup_id;
		}
		else
		{
			$shopper_group_id = ( int ) $ins->shopper_group_id;
		}
		
		if (VM_VERVM == '2' and $nds == "1")
		{
			$sql = "SELECT virtuemart_calc_id FROM #__".$dba['tax_rate_db']." where `calc_name` = '" . $data['nds_name'] . "'";
			$db->setQuery ( $sql );
			$log->addEntry ( array ('comment' => $sql ) ); //SirPiter раскомментировал
			$calc_id = $db->loadResult ();
			if (isset($calc_id))
			{
				$ins = new stdClass ();
				$ins->id = NULL;
				$ins->virtuemart_calc_id = (string)$calc_id;
				$ins->virtuemart_shoppergroup_id = (int)$shopper_group_id;
				if (! $db->insertObject ( '#__'.$dba['tax_shopgr_db'], $ins )) 
				{
					$log->addEntry ( array ('comment' => 'Этап 4.2.2) Неудача: Невозможно вставить запись в таблицу - '.$dba['tax_shopgr_db'] ) );
					if(!defined( 'VM_SITE' ))
					{
						echo 'failure\n';
						echo 'error mysql';
					}
					else
					{
						$logs_http[] = "<strong>Загрузка цен</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['tax_shopgr_db']."</strong>";
					}
					die;
				}
				
			}
		}
			
		$ins = new stdClass ();
		$ins->cashgroup_id = ( int )$shopper_group_id;
		$ins->c_id  = ( string )$db->getEscaped($data['id']);
			
		if (! $db->insertObject ( '#__'.$dba['cashgroup_to_1c_db'], $ins )) 
		{
			$log->addEntry ( array ('comment' => 'Этап 4.2.2) Неудача: Невозможно вставить запись в таблицу - '.$dba['cashgroup_to_1c_db'] ) );
			if(!defined( 'VM_SITE' ))
			{
				echo 'failure\n';
				echo 'error mysql';
			}
			else
			{
				$logs_http[] = "<strong>Загрузка цен</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['cashgroup_to_1c_db']."</strong>";
			}
			die;
		}
		
		$log->addEntry ( array ('comment' => 'Этап 4.2.2) Ценовая группа '.$data['name'].' создана' ) );
		$logs_http[] = "<strong>Загрузка цен</strong> - Ценовая группа <strong>".$data['name']."</strong> создана";
	}
	
	$logs_http[] = "<strong>Загрузка цен</strong> - ---------------- Все ценовые группы созданы ----------------";
}
?>