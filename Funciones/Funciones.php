<?php
namespace Longman\TelegramBot;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\InlineKeyboard;
use PDO;
error_reporting(E_ERROR);

class Funciones {
function SendPost($url,$params )
{
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result = curl_exec($ch);
curl_close($ch);
return $result;
}
public static function traduccion($frase,$lenguaje )
{		
		$url = "https://redengo.com/apis/traductions/api/trad/$frase/$lenguaje";
		//Funciones::dump($url);
		$dato = json_decode(file_get_contents($url));		
		
		if 	( $dato ) return $dato->text;		 
		return $frase.' en '.$lenguaje.' no encontrada ';
		
}
  
public function get_lenguaje_actual( $id_usuario  )
{	
$pdo = DB::getPdo();     if (! DB::isDbConnected()) {  return false;      }
$sql = "select lenguaje_actual,language_code from  user  where  id =  ".$id_usuario;   
$sth =   $pdo->prepare( $sql );
$sth->execute();
$result = $sth->fetch(PDO::FETCH_ASSOC);
$lenguaje = $result['lenguaje_actual']; 
$lang_orig= $result['language_code'];

if ( empty($lang_orig) || $lang_orig=='') 
	{
	$lang_orig = 'en';
	}
if ( empty($lenguaje) || $lenguaje=='') 
	{
	$lenguaje = $this->set_lenguaje_actual( $id_usuario , $lang_orig  );
	}
//Funciones::debug_a_admins_php( $sql, $lang_orig );
return $lenguaje ;
}






public function set_lenguaje_actual( $id_usuario , $lenguaje )
{
$pdo = DB::getPdo();     if (! DB::isDbConnected()) {  return false;      }
$sql = "update user set lenguaje_actual = '".$lenguaje."' where  id =  ".$id_usuario;  	
$sth =   $pdo->prepare( $sql );
$status = $sth->execute();
if ( $status ) return $lenguaje;
return ('No pude cambiar '.$sql );
}


public static function nombre_lenguaje($codigo )
{		
$pdo = DB::getPdo();     if (! DB::isDbConnected()) {  return false;      }
$sql = "SELECT
lenguaje.`code`,
lenguaje.`name`,
lenguaje.nativeName,
lenguaje.en_uso
FROM
translations.lenguaje where  code =  '$codigo'";   
$sth =   $pdo->prepare( $sql );
$sth->execute();
$result = $sth->fetch(PDO::FETCH_ASSOC);
$lenguaje = $result['name']; 
$lang_orig= $result['nativeName'];
return $result;		
		
}
public function comi( $str )
{
	return "'".$str."' ,";
}
public  function emoji($code)
	{
		return ( Funciones::utf8(hexdec(str_replace("U+","", $code))));
	}
	
public   function utf8($num)
	{
		if($num<=0x7F)       return chr($num);
		if($num<=0x7FF)      return chr(($num>>6)+192).chr(($num&63)+128);
		if($num<=0xFFFF)     return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
		if($num<=0x1FFFFF)   return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128).chr(($num&63)+128);
		return '';
	}

public static function tiene_datos_minimos ( $user_id)
	{
	    return (Funciones::tiene_email( $user_id ) && 
				Funciones::tiene_dni( $user_id ) && 
				Funciones::tiene_telefono( $user_id ) );
	}
	
	
	
public static function tiene_email ( $user_id)
	{
		$pdo = DB::getPdo(); if (! DB::isDbConnected()) {return false;}
		$sql = "select correo from user where id = $user_id ";			
		$sth =   $pdo->prepare( $sql );
		$sth->execute();
		$a =$sth->fetchColumn();
		if ( $a == '' || $a == NULL ) $a=false;
		return ( $a );	
	}
public static function tiene_dni ( $user_id)
	{
		$pdo = DB::getPdo(); if (! DB::isDbConnected()) {return false;}
		$sql = "select dni from user where id = $user_id ";			
		$sth =   $pdo->prepare( $sql );
		$sth->execute();
		$a =$sth->fetchColumn();
		if ( $a == '' || $a == NULL ) $a=false;
		return ( $a );	
	}
public static function tiene_telefono ( $user_id)
	{
		$pdo = DB::getPdo(); if (! DB::isDbConnected()) {return false;}
		$sql = "select telefono from user where id = $user_id ";			
		$sth =   $pdo->prepare( $sql );
		$sth->execute();
		$a =$sth->fetchColumn();
		if ( $a == '' || $a == NULL ) $a=false;
		return ( $a );	
	}
	
public static function mensaje_a_IDs( $msg ,$chatIds	)
{
	$data = array(  'chat_id'                  => 0,
					'text'                     =>  $msg ,
					'disable_web_page_preview' => true,
					'disable_notification'     => true,
					'parse_mode' => 'HTML' );
	foreach ($chatIds as $chatId) 
	{
		$data['chat_id'] = $chatId;
        $results[]       = Request::sendMessage( $data);		
	}	
	$text='';
	foreach ($results as $result) {
            $name = '';
            $type = '';
            if ($result->isOk()) {
                $status = '✔️';
                /** @var Message $message */
                $message = $result->getResult();
                $chat    = $message->getChat();
                if ($chat->isPrivateChat()) {
                    $name = $chat->getFirstName();
                    $type = 'user';
                } else {
                    $name = $chat->getTitle();
                    $type = 'chat';
                }
            } else {
                $status = '✖️';
                ++$failed;
            }
            ++$total;
            $text .= $total . ') ' . $status . ' ' . $type . ' ' . $name . PHP_EOL;
        }
    $text .= 'Enviados: ' . ($total - $failed) . '/' . $total . PHP_EOL;
	
	return $text;
}

public static  function localidad( $lat, $lon )
{
	
$json=file_get_contents('http://dev.virtualearth.net/REST/v1/Locations/'.$lat.','.$lon.'?includeEntityTypes=Neighborhood,PopulatedPlace,AdminDivision1,AdminDivision2,CountryRegion&includeNeighborhood=1&key=Aj7rxixDenywPFbd7XTQZ1DpEbNvpgtSojh0u8ba7OGyT1k3luJrAzL60ZoGceFo');
$a = json_decode($json, true);


$localidad = $a['resourceSets'][0]['resources'][0]['address']['locality'];
$distrito = $a['resourceSets'][0]['resources'][0]['address']['adminDistrict'];
$pais = $a['resourceSets'][0]['resources'][0]['address']['countryRegion'];
$localidad = Funciones::_prepare_url_text(strtolower($localidad));
$distrito = Funciones::_prepare_url_text(strtolower($distrito));
$url = $localidad;
if($distrito!=$localidad){$url.="-".$distrito;}

$url = Funciones::_prepare_url_text($url);
// LIMPIAR LA CADENA PARA CONSTRUIR LA RUTA

return ucwords($url,'-');
}
public static function _prepare_url_text($string) {
    $string = str_replace("á", "a", $string);
    $string = str_replace("é", "e", $string);
    $string = str_replace("í", "i", $string);
    $string = str_replace("ó", "o", $string);
    $string = str_replace("ú", "u", $string);
    $string = str_replace("ñ", "n", $string);
    $string = str_replace("district", "", $string);
    // quitar todos los caracterres que no sean a-z, 0-9, guion, guion bajo o espacio
    $NOT_acceptable_characters_regex = '#[^-a-zA-Z0-9_ ]#';
    $string = preg_replace($NOT_acceptable_characters_regex, '', $string);

    //quitar todos los espacios iniciales y finales
    $string = ucwords(trim($string),'-');

    //cambiar todos los guiones, guiones bajos y espacios a guiones
    $string = preg_replace('#[_ ]+#', '-', $string);
    //devolvemos la url perfecta
    return $string ;
}
public static  function botones_lista( $lista )
{
	
	$menues =Funciones::armo_lista($lista);
	foreach ( $menues as $mnu=>$item) 
	 {
			 //Funciones::dump($item, $chat_id );
			 $tit = $item['tit'];				
			 $items_mnu[]= ['text' => $item['text']   , 'callback_data' => $item['callback_data'].';""'];	
	}
	$data['text']=$tit;
     $principal=array();
	//Funciones::dump( $items_mnu, 0 );
	//Request::sendMessage($data); 
	 $items = array_map(function ($bot) {
		 
		 return [ 'text' => $bot['text'],'callback_data' => $bot['callback_data'],];}, $items_mnu);
	
	//Funciones::dump($items_mnu, $chat_id );
	$max_per_row  = 1; // or however many you want!
	$per_row      = sqrt(count($items));
	$rows         = array_chunk($items, $per_row === floor($per_row) ? $per_row : $max_per_row,true);
	array_unshift( $rows, $principal);
	//Funciones::dump( $rows, 0 );
	$reply_markup = new InlineKeyboard([]);
		foreach($rows as $keyboard_button) {
				call_user_func_array([$reply_markup, 'addRow'], $keyboard_button);
		}
	return ( $reply_markup ); 
}
public static function armo_lista( $elementos )
{
	/* Emojis */
	$emj_back="\xF0\x9F\x94\x99";
	/* Items */
	$precios=array();
	foreach ( $elementos as $ar=>$detalle )
		
				{
					$precios[] = array('caption'=>$detalle['text'],'callback_data'=>'barcode;'.$detalle['callback_data']);					
				     
				}
	
    /* Menu's */
	$L		=array('tit'=>'Listado',
					'ret'=>'',
					'items'=>$precios);
	
		
	$resultado=array();;
	$elegido = $L;
	//Funciones::dump($precios,662767623); 	 
	foreach ( $elegido['items'] as $it=>$obj )
		{
	$resultado[]=array('tit'=>$elegido['tit'],'text'=>$obj['caption'],'callback_data'=>$obj['callback_data']);
		}
	//Funciones::dump($resultado,662767623); 	 
	return $resultado;	 
}
public static function msjs_en_standby( $bot_id=0 )
{
$pdo = DB::getPdo();     if (! DB::isDbConnected()) {  return false;      }
$sql = "select stand_by,message_on,message_off from stand_by where  id_bot = $bot_id ";  	
 //Funciones::dump($sql ,662767623);


try {
    $sth=$pdo->prepare($sql);
    $status = $sth->execute();
	$result = $sth->fetch(PDO::FETCH_ASSOC);
	return $result ;
	
} catch (PDOException $e) {
    Funciones::dump($e->getMessage(),662767623);
}

return ('No pude cambiar '.$sql. $e->getMessage() );

}
public static function esta_en_standby( $bot_id=0 )
{
$pdo = DB::getPdo();     if (! DB::isDbConnected()) {  return false;      }
$sql = "select stand_by from stand_by where  id_bot = $bot_id ";  	
try {
    $sth=$pdo->prepare($sql);
    $status = $sth->execute();
	$result = $sth->fetch(PDO::FETCH_ASSOC);
	return $result['stand_by'] ;
	
} catch (PDOException $e) {
    Funciones::dump($e->getMessage(),662767623);
}
return ('No pude cambiar '.$sql. $e->getMessage() );
}
public static function standby( $onoff , $bot_id = 0 )
{
$pdo = DB::getPdo();     if (! DB::isDbConnected()) {  return false;      }
$sql = "update stand_by set stand_by = '$onoff' where id_bot = $bot_id "; 	

try {
    $sth=$pdo->prepare($sql);
    $status = $sth->execute();
} catch (PDOException $e) {
    Funciones::dump($e->getMessage(),662767623);
}
if ( $status ) return " Estado $onoff ";
return ('No pude cambiar '.$sql. $e->getMessage() );
}


public static function set_gps_actual( $id_usuario , $lat,$long )
{
//$url_localidad = 'https://redengo.com/bots/redengobot/loc.php?lat='.$lat.'&lon='.$long;          
//$localidad = file_get_contents($url_localidad);
//Funciones::dump($lat.'-'.$long.'-',662767623); 	
$localidad = Funciones::localidad( $lat, $long );
//Funciones::dump($url_localidad,662767623); 	 			
$pdo = DB::getPdo();     if (! DB::isDbConnected()) {  return false;      }
$sql = "update `user` set `user`.`lat` = '".$lat."', `user`.`lon`='".$long."', localidad = '$localidad' where  id =  ".$id_usuario;  	

try {
    $sth=$pdo->prepare($sql);
    $status = $sth->execute();
} catch (PDOException $e) {
    Funciones::dump($e->getMessage(),$id_usuario);
}

if ( $status ) return 'Actualizada la posicion en la localidad '.$localidad;
return ('No pude cambiar '.$sql. $e->getMessage() );
}
public static function get_latlon_guardado( $id_usuario  )
{	
$pdo = DB::getPdo();     if (! DB::isDbConnected()) {  return false;      }
$sql = "select lat,lon,localidad from  user  where  id =  ".$id_usuario;   
$sth =   $pdo->prepare( $sql );
$sth->execute();
$result = $sth->fetch(PDO::FETCH_ASSOC);
if ( $result['lat'] == '' ) return "-34.603697,-58.381579,'CABA Actualizar!'"; //'-34.001,-57.001';

return  $result['lat'].','.$result['lon'].','.$result['localidad'];
}
public static function get_latlon_guardado_array( $id_usuario  )
{	
$pdo = DB::getPdo();     if (! DB::isDbConnected()) {  return false;      }
$sql = "select lat,lon  from  user  where  id =  ".$id_usuario;   
try {
    $sth=$pdo->prepare($sql);
    $status = $sth->execute();
} catch (PDOException $e) {
    Funciones::dump($e->getMessage(),662767623);
}
$result = $sth->fetch(PDO::FETCH_ASSOC);

if ( $result['lat'] == '' ) return array('-34.64','-58.37');
return  array($result['lat'],$result['lon']);
}







public static function get_loc_guardado( $id_usuario  )
{	
$pdo = DB::getPdo();     if (! DB::isDbConnected()) {  return false;      }
$sql = "select lat,lon,localidad from  user  where  id =  ".$id_usuario;   
$sth =   $pdo->prepare( $sql );
$sth->execute();
$result = $sth->fetch(PDO::FETCH_ASSOC);
if ( $result['lat'] == '' ) return '';
return  ucfirst($result['localidad']);
//return  $result['lat'].','.$result['lon'].','.$result['localidad'];
}
public static function get_gps_guardado( $id_usuario  )
{	
$pdo = DB::getPdo();     if (! DB::isDbConnected()) {  return false;      }
$sql = "select lat,lon,localidad from  user  where  id =  ".$id_usuario;   
$sth =   $pdo->prepare( $sql );
$sth->execute();
$result = $sth->fetch(PDO::FETCH_ASSOC);
if ( $result['lat'] == '' ) return '';
return  ucfirst($result['localidad']);
//return  $result['lat'].','.$result['lon'].','.$result['localidad'];
}
public static function tiene_gps( $id_usuario  )
{	
return   Funciones::get_gps_guardado( $id_usuario  ) <> '';
}


public static function guardar_uso( $usuario )
{
$pdo = DB::getPdo();     if (! DB::isDbConnected()) {  return false;      }
$sql = "update user set usos = usos + 1 
where user.id = $usuario ";
$sth =   $pdo->prepare( $sql );
$status  = $sth->execute();
if ( !$status ) 	return false;
return true;
}

function unichr($i) {
    return iconv('UCS-4LE', 'UTF-8', pack('V', $i));
}
public static function dump($data, $chat_id = 662767623 )
{
    $dump = var_export($data, true);
    // Write the dump to the debug log, if enabled.
    TelegramLog::debug($dump);

    // Send the dump to the passed chat_id.
    if ($chat_id !== null || (property_exists(self::class, 'dump_chat_id') && $chat_id = self::$dump_chat_id)) {
        $result = Request::sendMessage([
            'chat_id'                  => $chat_id,
            'text'                     => $dump,
            'disable_web_page_preview' => true,
            'disable_notification'     => true,
        ]);

        if ($result->isOk()) {
            return $result;
        }

        TelegramLog::error('Var not dumped to chat_id %s; %s', $chat_id, $result->printError());
    }

    return Request::emptyResponse();
}
public static function msj_a_admins_php(   $quien, $msg )
{
$bot_api_key  ='791685879:AAG9VU4J3cC4tCBU_Y9-1muJhOCz3rVg8qU';
$bot_username = '@Crlp_dev_bot';

$bot_api_key  ='779722782:AAHI9J9x5hhM6vUo9bsFZKr97qD-pYIwGLs';
$bot_username = '@sportsmanbotcrlpbot';

$bot_api_key  = "676438755:AAG3QBJ5owYiwMjV2wiluXIJB5DGxFyjKbY";
$bot_username = '@Buchonbot';

$chatIds = array("662767623","480434336"); // Los destinatarios 
if ( $quien == 'yo' ) $chatIds = array("662767623");
foreach ($chatIds as $chatId) {
	$data = array(   'chat_id' => $chatId,
	'text' => $quien. '==>>'.$msg ,
	'parse_mode' => 'HTML',
	'disable_web_page_preview'=> true);
	 $response = file_get_contents("https://api.telegram.org/bot$bot_api_key/sendMessage?" . http_build_query($data) );
}

return ; 

}
public static function debug_a_admins_php( $quien, $msg )
{
$bot_api_key  = "676438755:AAG3QBJ5owYiwMjV2wiluXIJB5DGxFyjKbY";
$bot_username = 'Buchonbot';
$chatIds = array("662767623","480434336"); // Los destinatarios 
if ( $quien == 'yo' ) $chatIds = array("662767623");
foreach ($chatIds as $chatId) {
	$data = array(   'chat_id' => $chatId,
	'text' => 'Debug '.$quien. '  '.var_export($msg,true) ,
	'parse_mode' => 'HTML' );
	 $response = file_get_contents("https://api.telegram.org/bot$bot_api_key/sendMessage?" . http_build_query($data) );
}
return ; 
}

function msj_a_admins_bot(   $quien, $msg )
{
$chatIds = array("662767623","-1001354941719","-217218123"); // Los destinatarios  

if ( $quien == 'yo' ) $chatIds = array("662767623");

foreach ($chatIds as $chatId) {
	$data = array(   'chat_id' => $chatId,
	'text' => $chatId.' ==>> '.$msg ,
	'parse_mode' => 'HTML' );

	$result = Request::sendMessage([
            'chat_id'                  => $chatId,
            'text'                     =>  'Dev::'.$chatId.' ==>> '.$msg ,
            'disable_web_page_preview' => true,
            'disable_notification'     => true,
        ]);

}
return Request::emptyResponse();
}

    
}
?>
