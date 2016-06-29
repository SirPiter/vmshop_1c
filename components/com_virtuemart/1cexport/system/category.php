<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: system/category.php - Класс создания категорий
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

function inserCategory($xml, $parent = 0) 
{
	global $log, $db, $dba, $id_admin;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
	
	$xml = simplexml_load_string($xml);
	
	foreach($xml as $category)
	{		
		unset ($slug_name);
		$slug_name = array ();
		
		if( isset($category->Ид) AND isset($category->Наименование) AND $category->Ид != '' AND $category->Наименование != '')
		{ 
			$id =  strval($category->Ид);
			$name = ( string )$category->Наименование;
			$desc = ( string )$category->Комментарий; //Аматор
			
			$log->addEntry ( array ('comment' => '--------------------'.$name.'--------------------') );
			$logs_http[] = "<strong>Загрузка товара</strong> - --------------------<strong>".$name."</strong>--------------------";
			
			if(VM_VERVM == '2')
			{
				$slug_str = str_replace("(", "", $name);
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
					$slug =  translitString($name);
				}
				
				if (empty ($slug) or $slug == "")
				{
					$slug = $name;
				}
				
				$slug = str_replace("/", "_", $slug);
				$slug = str_replace("-", "_", $slug);
				
			}
			
			$log->addEntry ( array ('comment' => 'Этап 4.1.2) Проверяем категорию  '.$name) );
			$logs_http[] = "<strong>Загрузка товара</strong> - Проверяем существование категории: <strong>".$name."</strong>";
					
			$sql = "SELECT category_id FROM #__".$dba['category_to_1c_db']." where `c_category_id` = '" . $db->getEscaped($id) . "'";
			$db->setQuery ( $sql );
			//$log->addEntry ( array ('comment' => $id) );
			//$log->addEntry ( array ('comment' => $sql) );
			$rows_sub_Count = $db->loadResult ();
							
			if(isset ( $rows_sub_Count )) 
			{	
				$log->addEntry ( array ('comment' => 'Этап 4.1.2) Категория '.$name.' существует, обновляем информацию в базе') );
				$logs_http[] = "<strong>Загрузка товара</strong> - Категория <strong>".$name."</strong> существует, обновляем информацию в базе";
				
				$category_id = ( int ) $rows_sub_Count;
				if(VM_VERVM_S == 'F' and VM_VERVM == '2')
				{
					$category_db = $dba['category_ln_db'];
					$modifdate = "";
				}
				else
				{
					$category_db = $dba['category_db'];
					$modifdate = $dba['modifdate'];
				}
				
				$sql = "SELECT category_name FROM #__".$category_db." where `".$dba['pristavka']."category_id` = '" . $category_id . "'";
				$db->setQuery ( $sql );
				$category_name = $db->loadResult ();
				
				$sql = "SELECT category_description FROM #__".$category_db." where `".$dba['pristavka']."category_id` = '" . $category_id . "'";
				$db->setQuery ( $sql );
				$category_description = $db->loadResult ();//Аматор


				if(VM_VERVM == '2')
				{
					$sql = "SELECT slug FROM #__".$category_db." where `".$dba['pristavka']."category_id` = '" . $category_id . "'";
					$db->setQuery ( $sql );
					$slug_base = $db->loadResult ();
					$slug_if = $slug."_cid_".$category_id;
					if ($slug_base != $slug_if )
					{
						$slug_db = "slug = '".$slug."_cid_".$category_id."',";
					}
					else
					{
						$slug_db = "";
					}
				}
				else
				{
					$slug_db = "";
				}
				
				if($category_name != $name AND $category_description != $desc)
				{
					$sql = "UPDATE #__".$category_db." SET 
											`category_name` = '".$name."',
											`category_description` = '".$desc."',
											".$slug_db."
											".$modifdate."
							 	where `".$dba['pristavka']."category_id`='".$category_id."'";
					$db->setQuery ( $sql );
					$db->query ();
					
					$log->addEntry ( array ('comment' => 'Этап 4.1.2) Категория '.$name.' обновлена') );
					$logs_http[] = "<strong>Загрузка товара</strong> - Категория <strong>".$name."</strong> обновлена";
				}
				else
				{
					$log->addEntry ( array ('comment' => 'Этап 4.1.2) Категория '.$name.' не менялась, обновление не требуется') );
					$logs_http[] = "<strong>Загрузка товара</strong> - Категория <strong>".$name."</strong> не менялась, обновление не требуется";
				}
				
			} 
			else 
			{
				$ins = new stdClass ();
				if(VM_VERVM == '1')
				{
					$ins->category_id = NULL;
					$ins->category_name = $name;
					$ins->category_description = $desc;
					$ins->products_per_row = VM_LIST_CAT;
					$ins->vendor_id = '1';
					$ins->category_publish = 'Y';
					$ins->category_browsepage = 'managed';
					$ins->cdate = time ();
					$ins->mdate = time ();
					$ins->category_flypage = 'flypage.tpl';
					$ins->category_thumb_image = '';
					$ins->category_full_image = '';
					$ins->list_order = 1;
				}
				elseif(VM_VERVM == '2')
				{
					$ins->virtuemart_category_id = NULL;
					$ins->virtuemart_vendor_id = '1';
					if (VM_VERVM_S != 'F')
					{
						$ins->category_name = $name;
						$ins->slug = $slug;
						$ins->category_description = $desc;
						$ins->metadesc = '';
						$ins->metakey = '';
					}
					$ins->category_template = '0';
					$ins->category_layout = '0';
					$ins->category_product_layout = '0';
					$ins->products_per_row = VM_LIST_CAT;
					$ins->limit_list_start = NULL;
					$ins->limit_list_step = NULL;
					$ins->limit_list_max = NULL;
					$ins->limit_list_initial = NULL;
					$ins->hits = '0';
					$ins->metarobot = '';
					$ins->metaauthor = '';
					$ins->ordering = '0';
					$ins->shared  = '0';
					$ins->published  = '1';
					$ins->created_on = date ('Y-m-d H:i:s');
					$ins->created_by = $id_admin;
					$ins->modified_on = date ('Y-m-d H:i:s');
					$ins->modified_by = $id_admin;
				}
				
				if (! $db->insertObject ( '#__'.$dba['category_db'], $ins, $dba['pristavka'].'category_id' )) 
				{
					$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно создать категорию - <strong>".$name."</strong>";
					$log->addEntry ( array ('comment' => 'Этап 4.1.2) Неудача: Невозможно создать категорию - '.$name) );
					print 'error mysql';
					die();
				}
				else
				{
					$log->addEntry ( array ('comment' => 'Этап 4.1.2) Создаем категорию  '.$name) );
					$logs_http[] = "<strong>Загрузка товара</strong> - Создаем категорию: <strong>".$name."</strong>";
				}
				if(VM_VERVM == '1')
				{	
					$category_id = ( int ) $ins->category_id;
				}
				elseif(VM_VERVM == '2')
				{
					$category_id = ( int ) $ins->virtuemart_category_id;
				}
				
				if(VM_VERVM == '2')
				{
					if(VM_VERVM_S == 'F')
					{
						$slug = $slug."_cid_".$category_id;
						
						$ins = new stdClass ();
						$ins->virtuemart_category_id = $category_id;
						$ins->category_name = $name;
						$ins->slug = $slug;
						$ins->category_description = $desc;
						$ins->metadesc = '';
						$ins->metakey = '';
						if (! $db->insertObject ( '#__'.$dba['category_ln_db'], $ins )) 
						{
							$log->addEntry ( array ('comment' => 'Этап 4.1.2) Неудача: Таблица '.$dba['category_ln_db'].' Невозможно вставить запись для категории - '.$name) );
							$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись для категории - <strong>".$name."</strong>";
							print 'error mysql';
							die();
						}
					}
					else
					{
						$sql = "UPDATE #__".$dba['category_db']." SET 
													`slug` = '".$slug."_cid_".$category_id."'
										where `virtuemart_category_id`='".$category_id."'";
						$db->setQuery ( $sql );
						$db->query ();
					}
				}
				
				$ins = new stdClass ();
				$ins->category_parent_id = ( int ) $parent;
				$ins->category_child_id = ( int ) $category_id;
				if(VM_VERVM == '1')
				{
					$ins->category_list = null;
				}
				elseif(VM_VERVM == '2')
				{
					$ins->ordering = null;
				}			
				if (! $db->insertObject ( '#__'.$dba['category_xref_db'], $ins )) 
				{
					$log->addEntry ( array ('comment' => 'Этап 4.1.2) Неудача: Невозможно создать дерево категорий') );
					$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно создать дерево категорий";
					print 'error mysql';
					die();
				}
				
				$ins = new stdClass ();
				$ins->category_id = ( int ) $category_id;
				$ins->c_category_id = $db->getEscaped($id);
				
				if (! $db->insertObject ( '#__'.$dba['category_to_1c_db'], $ins )) 
				{
					$log->addEntry ( array ('comment' => 'Этап 4.1.2) Неудача: Невозможно применить категории 1С и VMSHOP') );
					$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно применить категории 1С и VMSHOP";
					print 'error mysql';
					die();
				}
				
			}
			
			$CAT[$id] = $category_id;
				
			$log->addEntry ( array ('comment' => ' 4.1.2) Категория и все ее подкатегории созданы') );
			$logs_http[] = "<strong>Загрузка товара</strong> - Категория и все ее подкатегории созданы";
		}
			
		if( $category->Группы ) inserCategory($category->Группы->asXML(), $category_id);
		
	}
	
	unset($xml);
}
?>