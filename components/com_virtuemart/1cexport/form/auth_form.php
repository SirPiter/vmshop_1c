<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: auth_form.php - Форма вывода информации после успешного логина
// Автор оригинала: Дуденков М.В. (email: mihail@termservis.ru)
// Помогали разрабатывать:	Alexandr Datsiuk
//							Павел Михнев
//                          CALEORT
// Авторские права: использовать, а также распространять данный скрипт
//                  разрешается только с разрешением автора скрипта
//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

// Выводим форму логина

if (isset($_SERVER['PHP_AUTH_USER'])) 
{
	$http_content .= '
		<center><strong>Процесс выгрузки:</strong><br></center>
		<ul type="circle">
';
	foreach($logs_http as $logs_pr)
	{
		$http_content .= '
			<li>'.$logs_pr.'</li>
		';
	}
	$http_content .= '
		</ul>
	';

	$coding_s = array (0 => 'UTF-8',1 => 'windows-1251',2 => 'KOI-8R',3 => 'KOI-8U');
	$selected_coding = array('','','','');
	foreach ($coding_s as $key=>$value)
	{
		if ($value == VM_CODING)
		{
			$selected_coding[$key] = ' selected="true"';
		}
	}
	
	$db_s = array (0 => 'yes',1 => 'no');
	$selected_db = array('','');
	foreach ($db_s as $key=>$value)
	{
		if ($value == VM_DB)
		{
			$selected_db[$key] = ' selected="true"';
		}
	}
	
	$zip_s = array (0 => 'yes',1 => 'no');
	$selected_zip = array('','');
	foreach ($zip_s as $key=>$value)
	{
		if ($value == VM_ZIP)
		{
			$selected_zip[$key] = ' selected="true"';
		}
	}
	
	$log_s = array (0 => 'time',1 => 'date',2 => 'one');
	$selected_log = array('','','');
	foreach ($log_s as $key=>$value)
	{
		if ($value == VM_LOG)
		{
			$selected_log[$key] = ' selected="true"';
		}
	}
	
	$lang_s = array (0 => 'RU',1 => 'EN',2 => 'UA');
	$selected_lang = array('','','');
	foreach ($lang_s as $key=>$value)
	{
		if ($value == VM_LANG)
		{
			$selected_lang[$key] = ' selected="true"';
		}
	}
	
	$catimg_s = array (0 => 'yes',1 => 'no');
	$selected_catimg = array('','');
	foreach ($catimg_s as $key=>$value)
	{
		if ($value == VM_CAT_IMG)
		{
			$selected_catimg[$key] = ' selected="true"';
		}
	}
	
	$catrand_s = array (0 => 'p',1 => 'r');
	$selected_catrand = array('','');
	foreach ($catrand_s as $key=>$value)
	{
		if ($value == VM_CAT_RAND)
		{
			$selected_catrand[$key] = ' selected="true"';
		}
	}
	
	$nds_s = array (0 => 'yes',1 => 'no');
	$selected_nds = array('','');
	foreach ($nds_s as $key=>$value)
	{
		if ($value == VM_NDS)
		{
			$selected_nds[$key] = ' selected="true"';
		}
	}
	
	$postavka_s = array (0 => 'yes',1 => 'no');
	$selected_postavka = array('','');
	foreach ($postavka_s as $key=>$value)
	{
		if ($value == VM_POSTAVKA_E)
		{
			$selected_postavka[$key] = ' selected="true"';
		}
	}
	
	$jpg_s = array (0 => 'yes',1 => 'no');
	$selected_jpg = array('','');
	foreach ($jpg_s as $key=>$value)
	{
		if ($value == VM_JPG)
		{
			$selected_jpg[$key] = ' selected="true"';
		}
	}
	
	$list_s = array (0 => '1',1 => '2',2 => '3',3 => '4',4 => '5',5 => '6',6 => '7',7 => '8');
	$selected_list = array('','','','','','','','');
	foreach ($list_s as $key=>$value)
	{
		if ($value == VM_LIST_CAT)
		{
			$selected_list[$key] = ' selected="true"';
		}
	}
	
	$usershop_s = array (0 => 'yes',1 => 'no');
	$selected_usershop = array('','');
	foreach ($usershop_s as $key=>$value)
	{
		if ($value == VM_USER_SHOP)
		{
			$selected_usershop[$key] = ' selected="true"';
		}
	}
	
	$client_s = array (0 => '0',1 => '1');
	$selected_client = array('','');
	foreach ($client_s as $key=>$value)
	{
		if ($value == VM_CLIENT)
		{
			$selected_client[$key] = ' selected="true"';
		}
	}
	
	$http_content .= '
		<script language="javascript">
		<!--
			function set_color(a) {
			  window.open("'.DS.'components'.DS.'com_virtuemart'.DS.'1cexport'.DS.'form'.DS.'color_form.html", a, "width=240,height=170,status=0,location=0,menubar=0,scrollbars=0,resizable=0");
			}
		//-->
		</script>
		<form action="vmshop_1c.php?mode=settings&act=save" method="post">
			<input type="hidden" name="VM_FORM" value="True">
			<br><br><hr size="2"><center><strong>Настройки системы выгрузки:</strong> (пропустите этот шаг если не требуется изменение)<br>
			(для пояснения параметра наведите курсор мыши на его название!)<br><br></center>
			<table width="100%" border="1" bordercolor="black" cellspacing="0" cellpadding="2">
				<tr>
					<td width="100%" colspan="8" align="center"><strong>Системные параметры</strong></td>
				</tr>
				<tr>
					<td width="10%"><div align="left" title="Кодировка выгрузки заказов">VM_CODING</div></td>
					<td width="15%" align="center">
						<select size="1" name="VM_CODING">
							<option value="UTF-8"'.$selected_coding[0].'>UTF-8</option>
							<option value="windows-1251"'.$selected_coding[1].'>windows-1251</option>
							<option value="KOI-8R"'.$selected_coding[2].'>KOI-8R</option>
							<option value="KOI-8U"'.$selected_coding[3].'>KOI-8U</option>
						</select>
					</td>
					<td width="10%"><div align="left" title="Обнулять таблицы перед выгрузкой?">VM_DB</div></td>
					<td width="15%" align="center">
						<select size="1" name="VM_DB">
							<option value="yes"'.$selected_db[0].'>Да</option>
							<option value="no"'.$selected_db[1].'>Нет</option>
						</select>
					</td>
					<td width="10%"><div align="left" title="Использование zip архивов">VM_ZIP</div></td>
					<td width="15%" align="center">
						<select size="1" name="VM_ZIP">
							<option value="yes"'.$selected_zip[0].'>Да</option>
							<option value="no"'.$selected_zip[1].'>Нет</option>
						</select>
					</td>
					<td width="10%"><div align="left" title="Максимальный размер архива в байтах">VM_ZIPSIZE</div></td>
					<td width="15%" align="center"><input type="text" name="VM_ZIPSIZE" value="'.VM_ZIPSIZE.'"></td>
				</tr>
				<tr>
					<td width="10%"><div align="left" title="вести логи по времени (time), по дате (date), один лог (one)">VM_LOG</div></td>
					<td width="15%" align="center">
						<select size="1" name="VM_LOG">
							<option value="time"'.$selected_log[0].'>По времени</option>
							<option value="date"'.$selected_log[1].'>По дате</option>
							<option value="one"'.$selected_log[2].'>Один</option>
						</select>
					</td>
					<td width="10%"><div align="left" title="Язык 1С - требуется для характеристик (см adapt.php)">VM_LANG</div></td>
					<td width="15%" align="center">
						<select size="1" name="VM_LANG">
							<option value="RU"'.$selected_lang[0].'>Русский</option>
							<option value="EN"'.$selected_lang[1].'>Английский</option>
							<option value="UA"'.$selected_lang[2].'>Украинский</option>
						</select>
					</td>
					<td width="10%">&nbsp;</td>
					<td width="15%" align="center">&nbsp;</td>
					<td width="10%">&nbsp;</td>
					<td width="15%" align="center">&nbsp;</td>
				</tr>
				<tr>
					<td width="100%" colspan="8" align="center"><strong>Параметры изображения категории</strong></td>
				</tr>
				<tr>
					<td width="10%"><div align="left" title="Применять картинку к категории">VM_CAT_IMG</div></td>
					<td width="15%" align="center">
						<select size="1" name="VM_CAT_IMG">
							<option value="yes"'.$selected_catimg[0].'>Да</option>
							<option value="no"'.$selected_catimg[1].'>Нет</option>
						</select>
					</td>
					<td width="10%"><div align="left" title="Выбор картинки">VM_CAT_RAND</div></td>
					<td width="15%" align="center">
						<select size="1" name="VM_CAT_RAND">
							<option value="p"'.$selected_catrand[0].'>Первая</option>
							<option value="r"'.$selected_catrand[1].'>Рандомом</option>
						</select>
					</td>
					<td width="10%">&nbsp;</td>
					<td width="15%" align="center">&nbsp;</td>
					<td width="10%">&nbsp;</td>
					<td width="15%" align="center">&nbsp;</td>
				</tr>
				<tr>
					<td width="100%" colspan="8" align="center"><strong>Параметры отвечающие за налог</strong></td>
				</tr>
				<tr>
					<td width="10%"><div align="left" title="Учитывать в цене из 1С налог НДС?">VM_NDS</div></td>
					<td width="15%" align="center">
						<select size="1" name="VM_NDS">
							<option value="yes"'.$selected_nds[0].'>Да</option>
							<option value="no"'.$selected_nds[1].'>Нет</option>
						</select>
					</td>
					<td width="10%"><div align="left" title="Страна учета НДС?">VM_NDS_COUNTRY</div></td>
					<td width="15%" align="center"><input type="text" name="VM_NDS_COUNTRY" value="'.VM_NDS_COUNTRY.'"></td>
					<td width="10%">&nbsp;</td>
					<td width="15%" align="center">&nbsp;</td>
					<td width="10%">&nbsp;</td>
					<td width="15%" align="center">&nbsp;</td>
				</tr>
				<tr>
					<td width="100%" colspan="8" align="center"><strong>Параметры отвечающие за поставки товара</strong></td>
				</tr>
				<tr>
					<td width="10%"><div align="left" title="Использовать модуль поставки (заменяет картинку в случае отсутствия товара)?">VM_POSTAVKA_E</div></td>
					<td width="15%" align="center">
						<select size="1" name="VM_POSTAVKA_E">
							<option value="yes"'.$selected_postavka[0].'>Да</option>
							<option value="no"'.$selected_postavka[1].'>Нет</option>
						</select>
					</td>
					<td width="10%"><div align="left" title="Вставляет текст или картинку (например: on-order.gif или в наличии)">VM_POSTAVKA</div></td>
					<td width="15%" align="center"><input type="text" name="VM_POSTAVKA" value="'.VM_POSTAVKA.'"></td>
					<td width="10%"><div align="left" title="Ориентировачное время поставки?">VM_POSTAVKA_TIME</div></td>
					<td width="15%" align="center"><input type="text" name="VM_POSTAVKA_TIME" value="'.VM_POSTAVKA_TIME.'"></td>
					<td width="10%">&nbsp;</td>
					<td width="15%" align="center">&nbsp;</td>
				</tr>
				<tr>
					<td width="100%" colspan="8" align="center"><strong>Параметры thumbnails изображения</strong></td>
				</tr>
				<tr>
					<td width="10%"><div align="left" title="Высота thumbnails изображения?">VM_TBN_H</div></td>
					<td width="15%" align="center"><input type="text" name="VM_TBN_H" value="'.VM_TBN_H.'"></td>
					<td width="10%"><div align="left" title="Ширина thumbnails изображения?">VM_TBN_W</div></td>
					<td width="15%" align="center"><input type="text" name="VM_TBN_W" value="'.VM_TBN_W.'"></td>
					<td width="10%"><div align="left" title="Заменять ли JPEG на JPG?">VM_JPG</div></td>
					<td width="15%" align="center">
						<select size="1" name="VM_JPG">
							<option value="yes"'.$selected_jpg[0].'>Да</option>
							<option value="no"'.$selected_jpg[1].'>Нет</option>
						</select>
					</td>
					<td width="10%">&nbsp;</td>
					<td width="15%" align="center">&nbsp;</td>
				</tr>
				<tr>
					<td width="10%"><div align="left" title="Подложка thumbnails изображения (Красный - 255)?">VM_TBN_RED</div></td>
					<td width="15%" align="center"><input type="text" name="VM_TBN_RED" value="'.VM_TBN_RED.'"></td>
					<td width="10%"><div align="left" title="Подложка thumbnails изображения (Зеленый - 255)?">VM_TBN_GREEN</div></td>
					<td width="15%" align="center"><input type="text" name="VM_TBN_GREEN" value="'.VM_TBN_GREEN.'"></td>
					<td width="10%"><div align="left" title="Подложка thumbnails изображения (Синий - 255)?">VM_TBN_BLUE</div></td>
					<td width="15%" align="center"><input type="text" name="VM_TBN_BLUE" value="'.VM_TBN_BLUE.'"></td>
					<td width="10%"><div align="left" title="Качество thumbnails изображения (максимум 100)?">VM_TBN_QTY</div></td>
					<td width="15%" align="center"><input type="text" name="VM_TBN_QTY" value="'.VM_TBN_QTY.'"></td>
				</tr>
				<tr>
					<td width="100%" colspan="8" align="center"><strong>Параметры отвечающие за каталог</strong></td>
				</tr>
				<tr>
					<td width="10%"><div align="left" title="Сколько отображать товаров в каталоге?">VM_LIST_CAT</div></td>
					<td width="15%" align="center">
						<select size="1" name="VM_LIST_CAT">
							<option value="1"'.$selected_list[0].'>1</option>
							<option value="2"'.$selected_list[1].'>2</option>
							<option value="3"'.$selected_list[2].'>3</option>
							<option value="4"'.$selected_list[3].'>4</option>
							<option value="5"'.$selected_list[4].'>5</option>
							<option value="6"'.$selected_list[5].'>6</option>
							<option value="7"'.$selected_list[6].'>7</option>
							<option value="8"'.$selected_list[7].'>8</option>
						</select>
					</td>
					<td width="10%"><div align="left" title="Название основной категории цен (у меня это Розничная)?">VM_DEF_CASHGR</div></td>
					<td width="15%" align="center"><input type="text" name="VM_DEF_CASHGR" value="'.VM_DEF_CASHGR.'"></td>
					<td width="10%"><div align="left" title="Обозначение свойства производителя?">VM_MANUFACTURE</div></td>
					<td width="15%" align="center"><input type="text" name="VM_MANUFACTURE" value="'.VM_MANUFACTURE.'"></td>
					<td width="10%">&nbsp;</td>
					<td width="15%" align="center">&nbsp;</td>
				</tr>
				<tr>
					<td width="100%" colspan="8" align="center"><strong>Параметры отвечающие за выгрузку заказов</strong></td>
				</tr>
				<tr>
					<td width="10%"><div align="left" title="Создать поля для регистрации новых клиентов (Банковские реквизиты: ИНН, КПП и т.д.)?">VM_USER_SHOP</div></td>
					<td width="15%" align="center">
						<select size="1" name="VM_USER_SHOP">
							<option value="yes"'.$selected_usershop[0].'>Да</option>
							<option value="no"'.$selected_usershop[1].'>Нет</option>
						</select>
					</td>
					<td width="10%"><div align="left" title="0 - Выгружать всех клиентов в 1С на контрагента "Физ лицо"  1- Выгружать всех клиентов в 1С как есть?">VM_CLIENT</div></td>
					<td width="15%" align="center">
						<select size="1" name="VM_CLIENT">
							<option value="0"'.$selected_jpg[0].'>Физ лицо</option>
							<option value="1"'.$selected_jpg[1].'>Как есть</option>
						</select>
					</td>
					<td width="10%"><div align="left" title="Ставка НДС для услуги доставки?">VM_NDS_SHIP</div></td>
					<td width="15%" align="center"><input type="text" name="VM_NDS_SHIP" value="'.VM_NDS_SHIP.'"></td>
					<td width="10%">&nbsp;</td>
					<td width="15%" align="center">&nbsp;</td>
				</tr>
			</table><br>
			<center><input type="submit" value="Сохранить">&nbsp;<input type="reset" value="Очистить"></center>
		</form>
		<hr size="2"><br>
		<form action="vmshop_1c.php?mode=file" enctype="multipart/form-data" method="post">
			<input type="hidden" name="mode" value="file">
			<input type="hidden" name="MAX_FILE_SIZE" value="'.VM_ZIPSIZE.'">
	';
	
	if(VM_ZIP == 'no')
	{
		$http_content .= '
			<strong>import.xml - </strong><input type="file" name="import">&nbsp;<strong>offers.xml - </strong><input type="file" name="offers">
		';
	}
	else
	{
		$http_content .= '<strong>ZIP архив - </strong><input type="file" name="filename">';
	}
	
	$http_content .= '		
			<center><input type="submit" value="Отправить">&nbsp;<input type="reset" value="Очистить"></center>
		</form>
	';

}

?>