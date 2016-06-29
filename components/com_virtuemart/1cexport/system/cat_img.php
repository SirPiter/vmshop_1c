<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: system/cat_img.php - Класс создания картинки категорий
// Автор оригинала: Дуденков М.В. (email: mihail@termservis.ru)
// Помогали разрабатывать:	Alexandr Datsiuk
//							Павел Михнев 
//                          CALEORT
// Авторские права: Использовать, а также распространять данный скрипт
// 					разрешается только с разрешением автора скрипта
//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

function Ins_cat_img() 
{
	global $log, $db, $dba, $id_admin;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
	
	$sql = "SELECT * FROM #__".$dba['category_db']." ORDER BY `".$dba['pristavka']."category_id` DESC";
	$db->setQuery ( $sql );
	$rows = $db->loadObjectList ();
	foreach ( $rows as $cat_rows ) 
	{
		if (VM_VERVM == '2')
		{
			$cat_id = $cat_rows->virtuemart_category_id;
		}
		elseif (VM_VERVM == '1')
		{
			$cat_id = $cat_rows->category_id;
			$cat_img = $cat_rows->category_full_image;
			$cat_tn_img = $cat_rows->category_thumb_image;
		}
		$cheack = false;
		$change_cimg = false;
		
		if(VM_VERVM_S == 'F' and VM_VERVM == '2')
		{
			$sql = "SELECT `category_name` FROM #__".$dba['category_ln_db']." where `".$dba['pristavka']."category_id` = '" . $cat_id . "'";
			$db->setQuery ( $sql );
			$cat_name = $db->loadResult ();
		}
		else
		{
			$cat_name = $cat_rows->category_name;
		}
		
				
		$log->addEntry ( array ('comment' => '-------------------'.$cat_name.'-------------------' ) );
		$logs_http[] = "<strong>Загрузка товара</strong> ------------------- <strong>".$cat_name."</strong> -------------------";
		
		if(VM_VERVM == '1')
		{
			if(!isset($cat_img) or $cat_img == "")
			{
				$log->addEntry ( array ('comment' => 'Этап 4.1.4) Добавляем картинку в категорию '.$cat_name ) );
				$logs_http[] = "<strong>Загрузка товара</strong> - Добавляем картинку в категорию - <strong>".$cat_name."</strong>";
				$make_cimg = true;
			}
			else
			{
				$log->addEntry ( array ('comment' => 'Этап 4.1.4) Категория '.$cat_name.' уже имеет картинку' ) );
				$logs_http[] = "<strong>Загрузка товара</strong> - Категория <strong>".$cat_name."</strong> уже имеет картинку";
				$make_cimg = false;
			}
		}
		elseif(VM_VERVM == '2')
		{
			$sql = "SELECT id FROM #__".DBBASE."_category_medias where `".$dba['pristavka']."category_id` = '" . $cat_id . "'";
			$db->setQuery ( $sql );
			$c_media = $db->loadResult ();
			if (isset($c_media) )
			{
				$log->addEntry ( array ('comment' => 'Этап 4.1.4) Категория '.$cat_name.' уже имеет картинку' ) );
				$logs_http[] = "<strong>Загрузка товара</strong> - Категория <strong>".$cat_name."</strong> уже имеет картинку";
				$make_cimg = false;
			}
			else
			{
				$log->addEntry ( array ('comment' => 'Этап 4.1.4) Добавляем картинку в категорию '.$cat_name ) );
				$logs_http[] = "<strong>Загрузка товара</strong> - Добавляем картинку в категорию - <strong>".$cat_name."</strong>";
				$make_cimg = true;
			}
		}
		
		if ($make_cimg == true)
		{
			$cat_id_new = $cat_id;
					
			$mediap_id = '';
			$cat_ch_id = array();
			$count_id = 0;
			$cat_subimg = false;
				
			do
			{
				$sql = "SELECT * FROM #__".$dba['product_category_xref_db']." where `".$dba['pristavka']."category_id` = '" . $cat_id_new . "'";
				$db->setQuery ( $sql );
				$pid = $db->loadObjectList ();
				$prod_id = array();
				
				if (isset($pid) and $pid != '')
				{
					foreach ( $pid as $prodid ) 
					{
						if (VM_VERVM == '2')
						{
							$sql = "SELECT virtuemart_media_id FROM #__".DBBASE."_product_medias where `".$dba['pristavka']."product_id` = '" . (int)$prodid->virtuemart_product_id . "'";
							$db->setQuery ( $sql );
							//$log->addEntry ( array ('comment' => $sql ) );
							$med_id = $db->loadResult ();
							if (isset($med_id))
							{
								$prod_id[] = $med_id;
							}
						}
						elseif (VM_VERVM == '1')
						{
							$sql = "SELECT product_id, product_full_image FROM #__".DBBASE."_product where `".$dba['pristavka']."product_id` = '" . (int)$prodid->product_id . "'";
							$db->setQuery ( $sql );
							//$log->addEntry ( array ('comment' => $sql ) );
							$med_id = $db->loadObject ();
							if (isset($med_id->product_full_image) and $med_id->product_full_image != "")
							{
								$prod_id[] = (int)$med_id->product_id;
							}
						}
						
					}
					
					if(count($prod_id) > 1)
					{
						if (VM_CAT_RAND == "r")
						{
							$ch = count($prod_id) - 1;
							$c_rnd = rand(0, $ch);
							$p_id = $prod_id[$c_rnd];
						}
						else
						{
							$p_id = $prod_id[0];
						}
					}
					elseif (count($prod_id) == 1)
					{
						$p_id = $prod_id[0];
					}
					else
					{
						$sql = "SELECT * FROM #__".$dba['category_xref_db']." where `category_parent_id` = '" . $cat_id_new . "'";
						$db->setQuery ( $sql );
						$cat_chid = $db->loadObjectList ();
						foreach ($cat_chid as $ch_cid)
						{
							if(VM_VERVM == '2')
							{
								$sql = "SELECT `virtuemart_media_id` FROM #__".DBBASE."_category_medias where `virtuemart_category_id` = '" . $ch_cid->category_child_id . "'";
								$db->setQuery ( $sql );
								$media_p_id = $db->loadObject ();
								if (isset($media_p_id) and $media_p_id != "")
								{
									$mediap_id = $media_p_id->virtuemart_media_id;
									$change_cimg = true;
									$cat_subimg = true;
									$cheack = true;
									break;
								}
							}
							elseif(VM_VERVM == '1')
							{
								$sql = "SELECT category_thumb_image, category_full_image FROM #__".DBBASE."_category where `category_id` = '" . $ch_cid->category_child_id . "'";
								$db->setQuery ( $sql );
								$media_cat = $db->loadObject ();
								
								if (isset($media_cat->category_full_image) and $media_cat->category_full_image != "" and isset($media_cat->category_thumb_image) and $media_cat->category_thumb_image != "")
								{
									$sql = "UPDATE #__".$dba['category_db']." SET 
												`category_full_image` = '".$media_cat->category_full_image."',
												`category_thumb_image` = '".$media_cat->category_thumb_image."'
									where `category_id`='".( int )$cat_id_new."'";
									$db->setQuery ( $sql );
									$db->query ();
									
									$change_cimg = true;
									$cheack = true;
									break;
								}
							}
							
							
						}
						$change_cimg = false;
						break;
					}
					
					if ($p_id != '' or !empty($p_id))
					{
						$mediap_id = $p_id;
						$cheack = true;
						break;
					}
				}
				else
				{
					$sql = "SELECT * FROM #__".$dba['category_xref_db']." where `category_parent_id` = '" . $cat_id_new . "'";
					$db->setQuery ( $sql );
					$cat_chid = $db->loadObjectList ();
					foreach ($cat_chid as $ch_cid)
					{
						if(VM_VERVM == '2')
						{
							$sql = "SELECT `virtuemart_media_id` FROM #__".DBBASE."_category_medias where `virtuemart_category_id` = '" . $ch_cid->category_child_id . "'";
							$db->setQuery ( $sql );
							$media_p_id = $db->loadObject ();
							if (isset($media_p_id) and $media_p_id != "")
							{
								$mediap_id = $media_p_id->virtuemart_media_id;
								$change_cimg = true;
								$cat_subimg = true;
								$cheack = true;
								break;
							}
						}
						elseif(VM_VERVM == '1')
						{
							$sql = "SELECT category_thumb_image, category_full_image FROM #__".DBBASE."_category where `category_id` = '" . $ch_cid->category_child_id . "'";
							$db->setQuery ( $sql );
							$media_cat = $db->loadObject ();
							
							if (isset($media_cat->category_full_image) and $media_cat->category_full_image != "" and isset($media_cat->category_thumb_image) and $media_cat->category_thumb_image != "")
							{
								$sql = "UPDATE #__".$dba['category_db']." SET 
											`category_full_image` = '".$media_cat->category_full_image."',
											`category_thumb_image` = '".$media_cat->category_thumb_image."'
								where `category_id`='".( int )$cat_id_new."'";
								$db->setQuery ( $sql );
								$db->query ();
								
								$change_cimg = true;
								$cheack = true;
								break;
							}
						}
						
						
					}
					$change_cimg = false;
					break;
				}
				
				if ($cat_id_new == false)
				{
					$change_cimg = false;
					break;
				}
				
				$count_id = $count_id + 1;
			}
			while($cheack = true);
			
			if (isset($mediap_id) and $mediap_id != '' and VM_VERVM == '2')
			{
				$sql = "SELECT * FROM #__".$dba['product_files_db']." where `".$dba['pristavka']."media_id` = '" . $mediap_id. "'";
				$db->setQuery ( $sql );
				$media = $db->loadObject ();
					
				if($media) 
				{
					if (!$cat_subimg)
					{
						$file = $media->file_url;
						$newfile = str_replace(DS."product".DS, DS."category".DS, $media->file_url);
							
						if (file_exists($newfile))
						{
							unlink ($newfile);
						}
						
						if (!file_exists($file)) {
							$log->addEntry ( array ('comment' => 'не удалось скопировать '.$file.'...\n' ) );
							$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Не удалось скопировать <strong>".$file."</strong>";
						}
						else
						{
							copy($file, $newfile);
						}
							
						$file_tb = $media->file_url_thumb;
						$newfile_tb = str_replace(DS."product".DS, DS."category".DS, $media->file_url_thumb);
						
						if (file_exists($newfile_tb))
						{
							unlink ($file_tb);
						}
						
						if (!file_exists($newfile_tb)) {
							$log->addEntry ( array ('comment' => 'не удалось скопировать '.$file_tb.'...\n' ) );
							$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Не удалось скопировать <strong>".$file_tb."</strong>";
						}
						else
						{
							copy($file_tb, $newfile_tb);
						}
					}
					else
					{
						$newfile = $media->file_url;
						$newfile_tb = $media->file_url_thumb;
					}
						
						
					$ins = new stdClass ();
					$ins->virtuemart_media_id = NULL;
					$ins->virtuemart_vendor_id = '1';
					$ins->file_title = (string)$cat_name;
					$ins->file_description = (string)$cat_name;
					$ins->file_meta = '';
					$ins->file_mimetype = $media->file_mimetype;
					$ins->file_type = 'category';
					$ins->file_url = $newfile;
					$ins->file_url_thumb = $newfile_tb;
					$ins->file_is_product_image = '0';
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
						
					//$log->addEntry ( array ('comment' => $cat_name .' / '. $media->file_mimetype .' / '. $media->file_url .' / '. $media->file_url_thumb ) );
								
					if (! $db->insertObject ( '#__'.$dba['product_files_db'], $ins, 'virtuemart_media_id' )) 
					{
						$log->addEntry ( array ('comment' => 'Этап 4.1.4) Неудача: Невозможно вставить запись в таблицу - '.DBBASE.'_medias' ) );
						if(!defined( 'VM_SITE' ))
						{
							echo 'failure\n';
							echo 'error mysql\n';
						}
						else
						{
							$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_medias</strong>";
						}
					}
							
					$media_id = ( int ) $ins->virtuemart_media_id;
									
					$ins = new stdClass ();
					$ins->id = NULL;
					$ins->virtuemart_category_id = ( int )$cat_id;
					$ins->virtuemart_media_id = (int)$media_id;
					$ins->ordering = '0';
						
					if (! $db->insertObject ( '#__'.DBBASE.'_category_medias', $ins )) 
					{
						$log->addEntry ( array ('comment' => 'Этап 4.1.4) Неудача: Невозможно вставить запись в таблицу - '.DBBASE.'_product_medias' ) );
						if(!defined( 'VM_SITE' ))
						{
							echo 'failure\n';
							echo 'error mysql\n';
						}
						else
						{
							$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_product_medias</strong>";
						}
					}
					
					$change_cimg = true;
				}
				else
				{
					$change_cimg = false;
				}
			}
			elseif (isset($mediap_id) and $mediap_id != '' and VM_VERVM == '1')
			{
				$sql = "SELECT * FROM #__".$dba['product_db']." where `".$dba['pristavka']."product_id` = '" . $mediap_id. "'";
				$db->setQuery ( $sql );
				$media = $db->loadObject ();
					
				if($media) 
				{
					$file = JPATH_BASE_PICTURE.DS.$media->product_full_image;
					$newfile = JPATH_CAT_PICTURE.DS.$media->product_full_image;
						
					if (file_exists($newfile))
					{
						unlink ($newfile);
					}
					if (!copy($file, $newfile)) {
						$log->addEntry ( array ('comment' => 'не удалось скопировать '.$file.'...\n' ) );
					}
						
					$file_tb = JPATH_BASE_PICTURE.DS.$media->product_thumb_image;
					$newfile_tb = JPATH_CAT_PICTURE.DS.$media->product_thumb_image;
					
					if (file_exists($newfile_tb))
					{
						unlink ($newfile_tb);
					}
					if (!copy($file_tb, $newfile_tb)) {
						$log->addEntry ( array ('comment' => 'не удалось скопировать '.$file_tb.'...\n' ) );
					}
					
					$sql = "UPDATE #__".$dba['category_db']." SET 
											`category_full_image` = '".$media->product_full_image."',
											`category_thumb_image` = '".$media->product_thumb_image."'
								where `category_id`='".( int )$cat_id."'";
					$db->setQuery ( $sql );
					$db->query ();
					
					$change_cimg = true;
				}
				else
				{
					$change_cimg = false;
				}
			}
				
			if ($change_cimg == true)
			{
				$log->addEntry ( array ('comment' => 'Этап 4.1.4) Для категории '.$cat_name.' добавлена картинка' ) );
				$logs_http[] = "<strong>Загрузка товара</strong> - Для категории - <strong>".$cat_name."</strong> добавлена картинка";
			}
			elseif ($change_cimg == false)
			{
				$log->addEntry ( array ('comment' => 'Этап 4.1.4) Для категории '.$cat_name.' нет картинки' ) );
				$logs_http[] = "<strong>Загрузка товара</strong> - Для категории - <strong>".$cat_name."</strong> нет картинки";
			}
		}
	}
}
?>