<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: system/customfields.php - Класс создание свободных полей
// Автор оригинала: Дуденков М.В. (email: mihail@termservis.ru)
// Помогали разрабатывать:	Alexandr Datsiuk
//							Павел Михнев 
//                          CALEORT
//			    Amator  (email: amatoravg@gmail.com)
// Авторские права: Использовать, а также распространять данный скрипт
// 					разрешается только с разрешением автора скрипта
//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

function customfields($UIN)
{
global $log, $db, $dba, $id_admin, $username, $lang_1c;	
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
	
	$sql = "SELECT virtuemart_custom_id FROM #__".$dba['customs_db']." where `custom_title` = '" . $lang_1c[0] . "'";
	$db->setQuery ( $sql );
	$rows_sub_Count = $db->loadResult ();

	if (!isset($rows_sub_Count) or empty($rows_sub_Count))
	{
		$ins = new stdClass ();
		$ins->virtuemart_custom_id = NULL;
		$ins->custom_parent_id = "0";
		$ins->virtuemart_vendor_id = "1";
		$ins->custom_jplugin_id = "0";
		$ins->custom_element = "";
		$ins->admin_only = "0";
		$ins->custom_title = $lang_1c[0];
		$ins->custom_tip = $lang_1c[0];
		$ins->custom_value = "";
		$ins->custom_field_desc = $lang_1c[0];
		$ins->field_type = "S";
		$ins->is_list = "0";
		$ins->is_hidden = "0";
		$ins->is_cart_attribute = "1";
		$ins->layout_pos = NULL;
		$ins->custom_params = $UIN;//Аматор. Тут будем хранить идентификатор поля из 1С
		$ins->ordering				=	'0'; 	
		$ins->shared				=	'0'; 	
		$ins->published				=	'1';	
		$ins->created_on			=	date ('Y-m-d H:i:s');
		$ins->created_by			=	$id_admin;
		$ins->modified_on			=	date ('Y-m-d H:i:s');
		$ins->modified_by			=	$id_admin;
					
		if (! $db->insertObject ( '#__'.$dba['customs_db'], $ins, 'virtuemart_custom_id' )) 
		{
			$log->addEntry ( array ('comment' => 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - '.$dba['customs_db'] ) );
			if(!defined( 'VM_SITE' ))
			{
				echo 'failure\n';
				echo 'error mysql\n';
			}
			else
			{
				$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['customs_db']."</strong>";
			}
			die;
		}
		
		$custom_id = ( int ) $ins->virtuemart_custom_id;
		
	}
	else
	{
		$custom_id = $rows_sub_Count;
	}

	return $custom_id;

}

function makecustoms($data='',$produkt_id='0',$cvid,$cvzn)
{
	global $log, $db, $dba, $id_admin, $username, $lang_1c;	
	
	if (VM_VERVM == '2' )
	{
		
		$ins = new stdClass ();
		$ins->virtuemart_customfield_id = NULL;
		$ins->virtuemart_product_id = ( int )$produkt_id;
		
		$sql = "SELECT * FROM #__".$dba['customs_db']." where `custom_params` = '" .$cvid. "'";
		$log->addEntry ( array ('comment' => 'Запрос: '.$sql));
					$db->setQuery ( $sql );
							$rows = $db->loadObject ();
					
							if($rows) 
							{
								
		$ins->virtuemart_custom_id = $rows->virtuemart_custom_id;
		$ins->custom_value = $cvzn;
		$ins->custom_price = "";
		$ins->custom_param = NULL;
		$ins->ordering = "0";
		$ins->published = '1';
		$ins->created_on = date ('Y-m-d H:i:s');
		$ins->created_by = $id_admin;
		$ins->modified_on = date ('Y-m-d H:i:s');
		$ins->modified_by = $id_admin;

		
		if (! $db->insertObject ( '#__'.$dba['customfields_db'], $ins )) 
		{
			$log->addEntry ( array ('comment' => 'Неудача: Невозможно вставить запись в таблицу - '.$dba['customfields_db'] ) );
			if(!defined( 'VM_SITE' ))
			{
				echo 'failure\n';
				echo 'error mysql\n';
			}
			else
			{
				$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['customfields_db']."</strong>";
			}
			die;
		}
		else
		{
			$logs_http[] = "<strong>Загрузка товара</strong> - Для продукта - <strong>".$data['name']."</strong> создано дополнительное поле <strong>".$rows->virtuemart_custom_id."</strong> со значением <strong>".$cvzn."</strong>";
			$log->addEntry ( array ('comment' => 'Для продукта '.$data['name'].' создано дополнительное поле '.$rows->custom_title.' со значением '.$cvzn ) );
		}
	}
	
	
	else 
	{
$log->addEntry ( array ('comment' => 'Аматор Неудача: Не найдена запись в таблице доп. полей с идентификатором '.$cvid ) );


	}
	return true;
}
}


?>