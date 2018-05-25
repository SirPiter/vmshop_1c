<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: system/clearbase.php - Класс отчистки баз данных

//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

function clearBase($clear,$id = '1') 
{
	global $log, $db, $dba;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
	
	$table_trun = array();

	// Если в настройках модуля стоит флаг обнулить БД 
	if(VM_DB == 'yes' AND $clear == 'false' AND $id == '1') 
	{
		//$log->addEntry ( array ('comment' => 'Этап 4.1.1) Начинаем отчистку таблиц') );
	    JLog::add ( 'Этап 4.1.1) Начинаем отчистку таблиц', JLog::INFO, 'vmshop_1c' );
		$logs_http[] = "<strong>Загрузка товара</strong> - Начинаем отчистку таблиц";
		// Очищает таблицы от всех товаров
		
		$table_trun[] = $dba['category_to_1c_db'];
		$table_trun[] = $dba['product_to_1c_db'];
		$table_trun[] = $dba['manufacturer_to_1c_db'];
		$table_trun[] = $dba['category_db'];
		$table_trun[] = $dba['category_xref_db'];
		$table_trun[] = $dba['manufacturer_db'];
		$table_trun[] = $dba['manufacturer_category_db'];
		$table_trun[] = $dba['product_db'];
		$table_trun[] = $dba['product_category_xref_db'];
		$table_trun[] = $dba['product_mf_xref_db'];

		
		if (VM_VERVM == '1')
		{
			$table_trun[] = 'vm_product_attribute';
			$table_trun[] = 'vm_product_attribute_sku';
			$table_trun[] = 'vm_product_files';
		}
		elseif (VM_VERVM == '2')
		{
			$table_trun[] = 'virtuemart_medias';
			$table_trun[] = 'virtuemart_product_medias';
			$table_trun[] = 'virtuemart_category_medias';
			$table_trun[] = 'virtuemart_product_customfields';
			$table_trun[] = 'virtuemart_customs';

			if (VM_VERVM_S == 'F')
			{
				$table_trun[] = $dba['category_ln_db'];
				$table_trun[] = $dba['manufacturer_ln_db'];
				$table_trun[] = $dba['manufacturer_category_ln_db'];
				$table_trun[] = $dba['product_ln_db'];
			}
		}
		
		foreach($table_trun as $key => $table_trun_sql)
		{
			$sql = "TRUNCATE TABLE `#__".$table_trun_sql."`";
			$db->setQuery ( $sql );
			if ($db->query ())
			{
				$logs_http[] = "<strong>Загрузка товара</strong> - Выполнен запрос № ".$key.": (<strong>".$sql."</strong>)";
				//$log->addEntry ( array ('comment' => 'Этап 4.1.1) Выполнен запрос № '.$key.': ('.$sql.')') );
				JLog::add ( 'Этап 4.1.1) Выполнен запрос № '.$key.': ('.$sql.')', JLog::INFO, 'vmshop_1c' );
				
			}
			else
			{
				$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка запроса № ".$key.": (<strong>".$sql."</strong>)";
				//$log->addEntry ( array ('comment' => 'Этап 4.1.1) Неудача: Ошибка запроса № '.$key.': ('.$sql.')') );
				JLog::add ( 'Этап 4.1.1) Неудача: Ошибка запроса № '.$key.': ('.$sql.')', JLog::ERROR, 'vmshop_1c' );
				
			}
		}
		
		//$log->addEntry ( array ('comment' => 'Этап 4.1.1) Закончили отчистку таблиц') );
		JLog::add ( 'Этап 4.1.1) Закончили отчистку таблиц', JLog::INFO, 'vmshop_1c' );
		$logs_http[] = "<strong>Загрузка товара</strong> - Закончили отчистку таблиц";
	}
	elseif(VM_DB == 'yes' AND $clear == 'false' AND $id == '2') 
	{
		//$log->addEntry ( array ('comment' => 'Этап 4.2.1) Начинаем отчистку таблиц') );
	    JLog::add ( 'Этап 4.2.1) Начинаем отчистку таблиц', JLog::INFO, 'vmshop_1c' );
	    
	    $logs_http[] = "<strong>Загрузка цен</strong> - Начинаем отчистку таблиц";
		// Очищает таблицы от всех товаров
		
		$table_trun[] = $dba['product_price_db'];
		$table_trun[] = $dba['cashgroup_to_1c_db'];
		$table_trun[] = $dba['product_product_type_xref_db'];
		$table_trun[] = $dba['shopper_group_db'];
		
		if (VM_VERVM == '2')
		{
			$table_trun[] = 'virtuemart_calc_categories';
			$table_trun[] = 'virtuemart_calc_shoppergroups';
			$table_trun[] = 'virtuemart_product_shoppergroups';
		}
		
		foreach($table_trun as $key => $table_trun_sql)
		{
			$sql = "TRUNCATE TABLE `#__".$table_trun_sql."`";
			$db->setQuery ( $sql );
			if ($db->query ())
			{
				$logs_http[] = "<strong>Загрузка цен</strong> - Выполнен запрос № ".$key.": (<strong>".$sql."</strong>)";
				//$log->addEntry ( array ('comment' => 'Этап 4.2.1) Выполнен запрос № '.$key.': ('.$sql.')') );
				JLog::add ( 'Этап 4.2.1) Выполнен запрос № '.$key.': ('.$sql.')', JLog::INFO, 'vmshop_1c' );
				
			}
			else
			{
				$logs_http[] = "<strong>Загрузка цен</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка запроса № ".$key.": (<strong>".$sql."</strong>)";
				//$log->addEntry ( array ('comment' => 'Этап 4.2.1) Неудача: Ошибка запроса № '.$key.': ('.$sql.')') );
				JLog::add ( 'Этап 4.2.1) Неудача: Ошибка запроса № '.$key.': ('.$sql.')', JLog::ERROR, 'vmshop_1c' );
				
			}
		}
		
		//$log->addEntry ( array ('comment' => 'Этап 4.2.1) Закончили отчистку таблиц') );
		JLog::add ( 'Этап 4.2.1) Закончили отчистку таблиц', JLog::INFO, 'vmshop_1c' );
		$logs_http[] = "<strong>Загрузка цен</strong> - Закончили отчистку таблиц";
	}

}
?>