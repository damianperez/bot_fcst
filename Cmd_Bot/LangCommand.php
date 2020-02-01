<?php
namespace Longman\TelegramBot\Commands\AdminCommands;
use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Funciones;
use Longman\TelegramBot\Entities\InlineKeyboard;

class LangCommand extends AdminCommand
{
    protected $name = 'Lang';
    protected $description = 'Cambiar el lenguaje';
	protected $usage = '/Lang <text>';
	protected $version = '1.1.0';
	protected $button_label = 'Idioma';
	protected $show_in_menu = true;  
    public function execute()
    {
        if ($this->getCallbackQuery() !== null) {
			 //$message =  $update->getMessage();
             $message  = $this->getCallbackQuery()->getMessage();
			 $chat    =$this->getCallbackQuery()->getMessage()->getChat();
			 $user    = $chat;
			 $chat_id =  $this->getCallbackQuery()->getMessage()->getChat()->getId();
			 $user_id = $chat_id;
			 $text = '';
		}
		else
		{
				$message = $this->getMessage();
				$chat    = $message->getChat();
				$user    = $message->getFrom();
				
				$chat_id = $chat->getId();
				$user_id = $user->getId();
				$text    = trim($message->getText(true));
		   	 
        }
        
		$data['chat_id'] =  $chat_id;
		/* Lang Command no hace nada, solo muestra botonoes
		Se resuelve todo en callbackquery */
        $text = 'Seleccionar lenguaje: ';// . $this->getUsage();
		$inline_keyboard = new InlineKeyboard( [
            ['text' => "🇵🇹" .' Portugues', 'callback_data' => 'Lng;'.$user_id.';pt-BR'],
			['text' => "🇷🇺 ".'Russia ', 'callback_data' => 'Lng;'.$user_id.';ru-RU'],
			['text' =>  "🇪🇸 󠁧󠁢󠁥󠁮󠁧󠁿". 'Español', 'callback_data' => 'Lng;'.$user_id.';es-ES'],
			['text' =>  "🏴󠁧󠁢󠁥󠁮󠁧󠁿" .'Inglés', 'callback_data' => 'Lng;'.$user_id.';en-US'],
			
        	]);
		$data['reply_markup'] =  $inline_keyboard;
		$data['text'] =  $text;
        return Request::sendMessage($data);
    }
}
