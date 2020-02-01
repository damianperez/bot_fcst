<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Conversation;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Funciones;
use Longman\TelegramBot\Entities\InputMedia\InputMediaPhoto;
require_once __DIR__ . '/../Funciones/BMnu.php';


/**
 * Generic command
 *
 * Gets executed for generic commands, when no other appropriate one is found.
 */
class GenericCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'generic';

    /**
     * @var string
     */
    protected $description = 'Handles generic commands or is executed by default when a command is not found';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
		error_reporting(E_ERROR);
        $message = $this->getMessage();

        //You can use $command as param
		
		
		 if ($this->getCallbackQuery() !== null) {
			 //$message =  $update->getMessage();
             $message  = $this->getCallbackQuery()->getMessage();
			 // $message_id = $message->getMessageId();
			 $chat    =$this->getCallbackQuery()->getMessage()->getChat();
			 $user    = $chat;
			 $chat_id =  $this->getCallbackQuery()->getMessage()->getChat()->getId();
			 $user_id = $chat_id;
			 $text = '';		 
			 //Funciones::dump(  $chat   ,  $user_id );
		}
		else
		{
				$message = $this->getMessage();
				
			//	$message_id = $message->getMessageId();
				
				$chat    = $message->getChat();
				$user    = $message->getFrom();
				
				$chat_id = $chat->getId();
				$user_id = $user->getId();
				$text    = trim($message->getText(true));
		   	 
        }
		 $command = $message->getCommand();
		
		
		
        //If the user is an admin and the command is in the format "/whoisXYZ", call the /whois command
        if (stripos($command, 'whois') === 0 && $this->telegram->isAdmin($user_id)) {
            return $this->telegram->executeCommand('whois');
        }
		if (stripos($command, 'cancel') === 0 || stripos($command, 'Cancel') === 0 	) 
		{
            $this->telegram->executeCommand('cancel');
			return $this->telegram->executeCommand('bmnu');
        }
		/*
        $data = [
            'chat_id' => $chat_id,
			'message_id' => $message_id,
            'text'    => 'Command /' . $command . ' not found.. :(',
        ];
		*/
		//$data['text'] = 'Commando ' . $command . ' fue ingresado';
        //Request::sendMessage($data);
		//If the user is an admin and the command is in the format "/whoisXYZ", call the /whois command
        if (stripos($command, 'whois') === 0 && $this->telegram->isAdmin($user_id)) {
            return $this->telegram->executeCommand('whois');
        }
        $data = [
            'chat_id' => $chat_id,
            'text'    => 'Command /' . $command . ' not found.. :(',
        ];
		return $this->telegram->executeCommand('bmnu');
        return Request::sendMessage($data);
    }
}
