<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: system/imgresize.php - Класс изменения картинок
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

function img_resize($src, $dest, $width, $height, $red=255, $green=255, $blue=255, $quality=100)
{
  if (!file_exists($src)) return false;

  $size = getimagesize($src);

  if ($size === false) return false;

  // Определяем исходный формат по MIME-информации, предоставленной
  // функцией getimagesize, и выбираем соответствующую формату
  // imagecreatefrom-функцию.
  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
  $icfunc = "imagecreatefrom" . $format;
  if (!function_exists($icfunc)) return false;

  $x_ratio = $width / $size[0];
  $y_ratio = $height / $size[1];

  $ratio       = min($x_ratio, $y_ratio);
  $use_x_ratio = ($x_ratio == $ratio);

  $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
  $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
  $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
  $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
  
  ini_set("gd.jpeg_ignore_warning", 1);
  
  $isrc = $icfunc($src);
  $idest = imagecreatetruecolor($width, $height);
  
  $rgb = imagecolorallocate($idest, $red, $green, $blue);

  imagefill($idest, 0, 0, $rgb);
  imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0, 
    $new_width, $new_height, $size[0], $size[1]);

  if($format=="jpeg")
  {
  	imagejpeg($idest, $dest, $quality);
  }
  elseif($format=="png")
  {
  	if($quality == 100)
	{
		$quality = 90;
	}
	imagepng($idest, $dest, 9-$quality/10);
  }
  else
  {
	  $func="image".$format;
	  $img_cr = $func($idest, $dest, $quality);
  }

  imagedestroy($isrc);
  imagedestroy($idest);

  return true;

}

function image_resize($source_path,$destination_path,$newwidth,$newheight = FALSE,$quality = FALSE ) 
{

    ini_set("gd.jpeg_ignore_warning", 1); // иначе на некотоых jpeg-файлах не работает
   
    list($oldwidth, $oldheight, $type) = getimagesize($source_path);
   
    switch ($type) {
        case 1: $typestr = 'gif' ;break;
        case 2: $typestr = 'jpeg'; break;
        case 3: $typestr = 'png'; break;
    }
    $function = "imagecreatefrom$typestr";
    $src_resource = $function($source_path);
   
    if (!$newheight) { $newheight = round($newwidth * $oldheight/$oldwidth); }
    elseif (!$newwidth) { $newwidth = round($newheight * $oldwidth/$oldheight); }
    $destination_resource = imagecreatetruecolor($newwidth,$newheight);
   
    imagecopyresampled($destination_resource, $src_resource, 0, 0, 0, 0, $newwidth, $newheight, $oldwidth, $oldheight);
   
    if ($type = 2) { # jpeg
        imageinterlace($destination_resource, 1); // чересстрочное формирование изображение
        if ($quality) imagejpeg($destination_resource, $destination_path, $quality);
        else imagejpeg($destination_resource, $destination_path);
    }
    else { # gif, png
        $function = "image$typestr";
        $function($destination_resource, $destination_path);
    }
   
    imagedestroy($destination_resource);
    imagedestroy($src_resource);
	
	return true;
	
}


?>