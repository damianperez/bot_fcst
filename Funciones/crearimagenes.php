<?php
error_reporting( E_ALL );

$originales = nombres_originales();

foreach ( $originales as $i )
{
	$img = "https://openweathermap.org/themes/openweathermap/assets/vendor/owm/img/widgets/".$i.'.png';	
	$path = __DIR__ . '/delaweb/';
	$name = $i.'.png';
	//save_image($img,$path.$name);
	//resize_image($name,$path,60);	
}



$v = comb(3, $originales );
//var_dump($v);

foreach ( $v as $i )
{
	$outputImage= crear( $i); 
}

header('Content-Type: image/png');

$path_origen =  __DIR__ . '/../imagenes/orig/100X100/';
//$path_origen =  __DIR__ . '/origen/';
$path_destino =  __DIR__ .'/salida/';

$outputImage= crear( "01d02d10d");
foreach ( $originales as $i )
{	
	$name = $i.'.png';
	//$outputImage= crear_imgsola( $path_origen, $path_destino, $name );
}


$outputImage= crear( "10d10d10d");
$outputImage= crear( "10d10n10n");
$outputImage= crear( "10d10d10n");
//$outputImage= crear( "03d","02d","10d");
imagepng($outputImage);
//imagedestroy($outputImage);


Function crear( $combinacion )
{
$path_origen =  __DIR__ . '/../imagenes/orig/60X60/';
//$path_origen =  __DIR__ . '/origen/';
$path_destino =  __DIR__ .'/salida/';

$first = substr($combinacion,0,3);
$second = substr($combinacion,3,3);
$third = substr($combinacion,6,3);

$numberOfImages = 3;
$x = 60;
$y = 100;
//$background = imagecreatetruecolor($x*5, $y);
$background = @imagecreate($x*5, $y);
    
$color_fondo = imagecolorallocate($background, 255,255,255 );
$base_origen = $path_origen;

$firstUrl  = $base_origen.$first.".png";
$secondUrl = $base_origen.$second.".png";
$thirdUrl  = $base_origen.$third.".png";

$outputImage = $background;

$outputUrl =  $path_destino.$first.$second.$third.".png";
$outputUrl = __DIR__ .'/../imagenes/weather/'.$first.$second.$third.".png";
	
$first = imagecreatefrompng($firstUrl);
$second = imagecreatefrompng($secondUrl);
$third = imagecreatefrompng($thirdUrl);

imagecopymerge($outputImage,$first , $x  , 40 , 0, 0, $x, $y,100);
imagecopymerge($outputImage,$second,$x*2 , 40 , 0, 0, $x, $y,100);
imagecopymerge($outputImage,$third ,$x*3 , 40 , 0, 0, $x, $y,100);

imagepng($outputImage, $outputUrl);
return $outputImage;
}



Function crear_imgsola( $base_origen, $base_destino, $first )
{
//300*100
$x = 100 ; 
$y = 100;
//$background = imagecreatetruecolor($x*5, $y);
$background = @imagecreate(300, $y);    
$color_fondo = imagecolorallocate($background, 255,255,255 );

$firstUrl  = $base_origen.$first;
$outputImage = $background;
$outputUrl =  $base_destino.$first;

$first = imagecreatefrompng($firstUrl);
imagecopymerge($outputImage,$first,100, 0 , 0, 0, $x, $y,100);
imagepng($outputImage, $outputUrl);
return $outputImage;
}


function comb ($n, $elems) {
    if ($n > 0) {
      $tmp_set = array();
      $res = comb($n-1, $elems);
      foreach ($res as $ce) {
          foreach ($elems as $e) {
             array_push($tmp_set, $ce . $e);
          }
       }
       return $tmp_set;
    }
    else {
        return array('');
    }
}
function save_image($img,$fullpath){
 $ch = curl_init ($img);
 curl_setopt($ch, CURLOPT_HEADER, 0);
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
 $rawdata=curl_exec($ch);
 curl_close ($ch);
 if(file_exists($fullpath)){
  unlink($fullpath);
 }
 $fp = fopen($fullpath,'x');
 fwrite($fp, $rawdata);
 fclose($fp);
}
function nombres_originales()
{
$imgs=array();
$imgs[]="01";
$imgs[]="02";
$imgs[]="03";
$imgs[]="04";
$imgs[]="09";
$imgs[]="10";
$imgs[]="11";
$imgs[]="13";
$imgs[]="50";

$todas = array();
foreach ($imgs as $i )
	{
    $todas[] = $i.'d';
    $todas[] = $i.'n';
	}
return $todas;
}
function resize_image($name,$path,$width)
{
$fullpath = $path.$name;
$image = imagecreatefrompng($fullpath);
$ratio = $width / imagesx($image); // 700 for the width you want...
                                // imagesx() to determine the current width
//Get the scaled height:
$height = imagesy($image) * $ratio; // imagesy() to determine the current height
//Do the actual resize:

$new_image = imagecreatetruecolor($width, $height);
imagecopyresampled($new_image, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
$image = $new_image; // $image has now been replaced with the resized one.
$base_origen = __DIR__ . '/orig_60x60/';
$base_destino = __DIR__ . '/salida/';
$base_destino = __DIR__ . '/../imagenes/weather/';
imagepng($image, $base_origen.$name);
crear_imgsola( $base_origen, $base_destino, $name );

}

?>