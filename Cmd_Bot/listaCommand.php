<?php
namespace Longman\TelegramBot\Commands\AdminCommands;
use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Funciones;
use PDO;
use Longman\TelegramBot\DB;


/**
 * lista command
 */
class listaCommand extends AdminCommand
{
    protected $name = 'lista';
    protected $description = 'Bienvenida a la Pizzeria';
    protected $usage = '/lista';
    protected $version = '1.1.0';
    protected $private_only = true;
	public function usuarios_hoy()	
    {   
		$pdo = DB::getPdo(); if (! DB::isDbConnected()) {return false;}
		$sql = "select * from user WHERE ( updated_at >=  CURRENT_DATE()) and is_bot=0 and is_admin=0 order by updated_at ";
		$sth =   $pdo->prepare( $sql );
		$sth->execute();
		$a =$sth->fetchAll();
		return ( $a );
	}
	
    public function execute()
    {

        if ($this->getCallbackQuery() !== null) {
			 //$message =  $update->getMessage();
             $message  = $this->getCallbackQuery()->getMessage();
			 $chat    =$this->getCallbackQuery()->getMessage()->getChat();
			 $user    = $chat;
			 $chat_id =  $this->getCallbackQuery()->getMessage()->getChat()->getId();
			 $user_id = $chat_id;
			 $text    = trim($message->getText(true));
		}
		else
		{
			$message = $this->getMessage() ?: $this->getEditedMessage();
			$chat    = $message->getChat();
			$user    = $message->getFrom();
			$chat_id = $chat->getId();
			$user_id = $user->getId();
			$text    = trim($message->getText(true));
        }
		$data = [
					'chat_id'    => $chat_id,
					'user_id' => $user_id,					
				];
		$data['parse_mode'] = 'HTML';
		$data['disable_web_page_preview']=false;
		
		
		$resultado = $this->usuarios_hoy()	;				
		$data['text'] .= 'Usaron el bot '.PHP_EOL;
		$i=0;
		foreach ( $resultado as $dato )
		{	
			$i+=1;
			$data['text'] .=$dato['first_name'].' :'.$dato['localidad'].' ('.$dato['usos'] .')'.PHP_EOL;
		}	
		$data['text'] .="Hoy: $i";
		
		Request::sendMessage($data);
		return $this->telegram->executeCommand('BMnu');
    }
}
