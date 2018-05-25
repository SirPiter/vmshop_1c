<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: adaptvm.php - Адаптация VMSHOP к 1С

//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	print "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}
$name_db_sql = array (
	"bank_account_holder",
	"bank_account_nr",
	"bank_sort_code",
	"bank_name",
	"bank_iban",
	"delimiter_bankaccount",
	"vm_fullname",
); 

foreach ($name_db_sql as $name)
{
	$sql = "SELECT registration FROM #__vm_userfield WHERE name ='" . $name . "'";
	$db->setQuery($sql);
	$adapt = $db->loadResult ();
	
	if($adapt == '0')
	{
		$sql = "UPDATE #__vm_userfield SET registration=1 where name='".$name."'";
		$db->setQuery ( $sql );
		if (!$db->query ())
		{
			//$log->addEntry ( array ('comment' => 'Неудача: Невозможно обновить vm_userfield поле '.$name ) );
		    JLog::add ( 'Неудача: Невозможно обновить vm_userfield поле '.$name, JLog::ERROR, 'vmshop_1c' );
			
			//$log->addEntry ( array ('comment' => $sql ) );
		    JLog::add ( $sql, JLog::DEBUG, 'vmshop_1c' );
		    
			echo 'failure\n';
			echo 'error mysql update\n';
			echo $sql;
		}
		else
		{
			//$log->addEntry ( array ('comment' => 'Поле '.$name.' обновлено' ) );
		    JLog::add ( 'Поле '.$name.' обновлено', JLog::DEBUG, 'vmshop_1c' );
		    
		}
	}
	elseif(!isset($adapt))
	{
		$ins = new stdClass ();
		$ins->fieldid = NULL;
		$ins->name = $name;
		$ins->title = "Полное наименование";
		$ins->description = "";
		$ins->type = "text";
		$ins->maxlength = "0";
		$ins->size = "0";
		$ins->required = "0";
		$ins->ordering = "9";
		$ins->cols = "0";
		$ins->rows = "0";
		$ins->value = "";
		$ins->default = NULL;
		$ins->published = "1";
		$ins->registration = "1";
		$ins->shipping = "0";
		$ins->account = "1";
		$ins->readonly = "0";
		$ins->calculated = "0";
		$ins->sys = "0";
		$ins->vendor_id = "1";
		$ins->params = "";
			
		if (! $db->insertObject ( '#__vm_userfield', $ins )) 
		{
			//$log->addEntry ( array ('comment' => 'Неудача: Невозможно вставить поле '.$name ) );
		    JLog::add ( 'Неудача: Невозможно вставить поле '.$name, JLog::ERROR, 'vmshop_1c' );
		    
			//$log->addEntry ( array ('comment' => $sql ) );
		    JLog::add ( $sql, JLog::ERROR, 'vmshop_1c' );
		    
			echo 'failure\n';
			echo 'error mysql';
			echo $sql;
		}
		
		$sql = "ALTER TABLE `#__vm_order_user_info` ADD `".$name."` varchar(255) NULL DEFAULT NULL";
		$db->setQuery ( $sql );
		if (!$db->query ())
		{
			//$log->addEntry ( array ('comment' => 'Неудача: Невозможно обновить vm_order_user_info поле '.$name ) );
		    JLog::add ( 'Неудача: Невозможно обновить vm_order_user_info поле '.$name, JLog::ERROR, 'vmshop_1c' );
		    
			//$log->addEntry ( array ('comment' => $sql ) );
			JLog::add ( $sql, JLog::ERROR, 'vmshop_1c' );
			
			echo 'failure\n';
			echo 'error mysql update\n';
			echo $sql;
		}
		else
		{
			//$log->addEntry ( array ('comment' => 'Поле '.$name.' обновлено' ) );
		    JLog::add ( 'Поле '.$name.' обновлено', JLog::INFO, 'vmshop_1c' );
		    
		}
	}
}

?>