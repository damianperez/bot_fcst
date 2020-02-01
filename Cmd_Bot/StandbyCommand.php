<?php 
namespace Longman\TelegramBot\Commands\AdminCommands;
use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Request;
use PDO;
use Longman\TelegramBot\DB;
error_reporting(E_ALL);
class standbyCommand extends AdminCommand
{
    protected $name = 'standby';
    protected $description = 'Maintenance mode on off';
    protected $usage = '/standby';
    protected $version = '0.1.0';
    
	function esta_en_standby( $bot_id=0 )
	{
	$pdo = DB::getPdo();     if (! DB::isDbConnected()) {  return false;      }
	$sql = "select stand_by from stand_by where  id_bot = $bot_id ";  	
	$ret = false;
	try {
		$sth=$pdo->prepare($sql);
		$status = $sth->execute();
		$result = $sth->fetch(PDO::FETCH_ASSOC);
		$ret =  $result['stand_by'] ;
		
	} catch (PDOException $e) {
		Funciones::dump($e->getMessage());
	}
	return $ret;
	}
	function standby( $onoff , $bot_id = 0 )
	{
	$pdo = DB::getPdo();     if (! DB::isDbConnected()) {  return false;      }
	$sql = "update stand_by set stand_by = '$onoff' where id_bot = $bot_id "; 		
	try {
		$sth=$pdo->prepare($sql);
		$status = $sth->execute();
	} catch (PDOException $e) {
		Funciones::dump($e->getMessage());
	}	
	return $status;
	}
	public static function msjs_en_standby( $bot_id=0 )
	{
	$pdo = DB::getPdo();     if (! DB::isDbConnected()) {  return false;      }
	$sql = "select stand_by,message_on from stand_by where  id_bot = $bot_id ";  	
	$result='';
	try {
		$sth=$pdo->prepare($sql);
		$status = $sth->execute();
		$result = $sth->fetch(PDO::FETCH_ASSOC);				
	} catch (PDOException $e) {
		Funciones::dump($e->getMessage());
	}
	return $result['message_on'];
	}
    public function execute()
    {
		$message = $this->getMessage() ?: $this->getEditedMessage();
		$chat    = $message->getChat();
		$user    = $message->getFrom();
		$chat_id = $chat->getId();
		$user_id = $user->getId();
		$text    = trim($message->getText(true));
		$data = [
			    'chat_id'      => $chat_id,			
			    'text'         => "*Stand by* ".PHP_EOL,           
			];
		$mantenimiento = $this->esta_en_standby( $this->getTelegram()->getBotId()	);
		if ( $text <> 'on' && $text <> 'off')
		{
			$data['text'].="Uso: on/off ".$text.' '.date('d / m G:i:s').PHP_EOL;
			if ( $mantenimiento )  $data['text'] .="En mantenimiento";
			if (!$mantenimiento )  $data['text'] .="OPERATIVO";
			return Request::sendMessage($data);
		}
		if ( $text == 'on' )  
		{
			$this->standby( true , $this->getTelegram()->getBotId() );
			$mensaje = $this->msjs_en_standby( $this->getTelegram()->getBotId() );
			$data['text'].="Nuevo estado:  En mantenimiento ".$mensaje.PHP_EOL.date('d / m G:i:s');
		}
	    	else
		{
			$this->standby(  0   , $this->getTelegram()->getBotId() );
			$data['text'].="Nuevo estado: OPERATIVO".PHP_EOL.date('d / m G:i:s');
		}		
		return Request::sendMessage($data);
		
    }
}