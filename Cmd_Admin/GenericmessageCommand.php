<?php
namespace Longman\TelegramBot\Commands\SystemCommands;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Funciones;
use PDO;
use Longman\TelegramBot\DB;

class GenericmessageCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'genericmessage';
    protected $description = 'Handle generic message';
    protected $version = '1.1.0';
    protected $need_mysql = true;
    public function executeNoDb()
    {
        // Do nothing
        return Request::emptyResponse();
    }
	public function findcity($city,$country='')	
    {   
		$pdo = DB::getPdo(); if (! DB::isDbConnected()) {return false;}
		$sql = "SELECT
				`city.list`.id,
				`city.list`.`name`,
				`city.list`.country,
				`city.list`.coord
				FROM
				`city.list`
				WHERE
				name = '$city' ";
		if ( $country <> '' ) $sql.= " and country = '$country' ";
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
            'chat_id' => $chat_id,
            'text'    => 'GM' . $text . ' not found.. :(',
        ];
		$f=new Funciones();
		$lng =  $f->get_lenguaje_actual($user_id);
		$lang_name = $f->nombre_lenguaje($lng);
		//.' '.$lang_name['nativeName'].' ('.$lang_name['name'].')';
			
		$texto_ciudad = $f->traduccion('ciudad',$lng );
		 
		$find='';$ciudad='';$pais='';
		if ( ( strtolower(substr($text, 0, 4)) == 'city' )		) 
			$find = substr($text,5);
		//Funciones::dump(  strtolower(substr($text,strlen( $texto_ciudad) )), 662767623 ); 
		//Funciones::dump(   strtolower($texto_ciudad), 662767623 ); 
		if  (  strtolower(substr($text,0, strlen( $texto_ciudad) )) ==  strtolower($texto_ciudad) )
			$find = substr($text,strlen( $texto_ciudad)+1 );
		
		if ( $find <> '' )
		{
			$busco = explode(',',$find);
			if (isset($busco[0])) $ciudad = trim($busco[0]);
			if (isset($busco[1])) $pais = trim($busco[1]);
		
			$data['text'] = 'Buscamos '.$ciudad.' '.$pais;
		
			$find = $this->findcity($ciudad,$pais)	;
			if ( count( $find ) > 0 ) 
			{
				$coords = json_decode($find[0]['coord'], true);
				Funciones::set_gps_actual( $user_id  , $coords['lat'],$coords['lon']);
				
				$data['text'] = $f->traduccion('ciudad.cambiando',$lng ).' '.$ciudad.' '.$pais;
				Request::sendMessage($data); 	
				return $this->telegram->executeCommand('bmnu');
				
				//Funciones::dump(   $coords, 662767623 ); 
			}
				
			//Funciones::dump(   $find, 662767623 ); 
			//Request::sendMessage($data); 	
		}
		
        //If a conversation is busy, execute the conversation command after handling the message
        $conversation = new Conversation(
            $user_id,
            $chat_id
        );
		
        //You can use $command as param        
        $command = $message->getCommand();
	    // Try to continue any active conversation.
		
		if (stripos($text, 'cancel') === 0 || stripos($text, 'Cancel') === 0) {
            $this->telegram->executeCommand('cancel');
			return $this->telegram->executeCommand('bmnu');
        }
		
        if ($active_conversation_response = $this->executeActiveConversation()) {
            return $active_conversation_response;
        }
        // Try to execute any deprecated system commands.
        if (self::$execute_deprecated && $deprecated_system_command_response = $this->executeDeprecatedSystemCommand()) {
            return $deprecated_system_command_response;
        }
		
        return Request::emptyResponse();
	}
}
