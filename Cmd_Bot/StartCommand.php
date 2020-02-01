<?php
namespace Longman\TelegramBot\Commands\UserCommands;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Funciones;
use Translation\Translation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use TelegramBot\InlineKeyboardPagination\InlineKeyboardPagination;

/**
 * Start command
 */
class StartCommand extends UserCommand
{
    protected $name = 'Start';
    protected $description = 'Bienvenida a la Pizzeria';
    protected $usage = '/Start';
    protected $version = '1.1.0';
    protected $private_only = true;
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
		
		
		$f=new Funciones();
			 		
					$lng =  $f->get_lenguaje_actual($chat_id  );
					$lang_name = $f->nombre_lenguaje($lng);
					//.' '.$lang_name['nativeName'].' ('.$lang_name['name'].')';
					
					$frase = $f->traduccion('welcome.to',$lng ).' <b>Weather Forecast bot </b>'.PHP_EOL.PHP_EOL;
		
		
		
		/*
		$data['caption']= 'Hola '. $user->getFirstName(). '! Te damos la bienvenida a Open Weather bot'.PHP_EOL.
		                    $this->getTelegram()->getBotId().' Seleccioná la opción del menú ';
		*/
		if ( $lng <> 'ru' ) $frase .= $f->traduccion('welcome.to','ru' ).' <b>Weather Forecast bot </b>'.PHP_EOL;
		if ( $lng <> 'en' ) $frase .= $f->traduccion('welcome.to','en' ).' <b>Weather Forecast bot </b>'.PHP_EOL;
		
		$data['caption']= $frase .PHP_EOL;
		                    
		$data['text'] = $data['caption'];
		//$data['photo']   = Request::encodeFile($imgurl);
		Request::sendMessage($data);
		return $this->telegram->executeCommand('BMnu');
    }
}
