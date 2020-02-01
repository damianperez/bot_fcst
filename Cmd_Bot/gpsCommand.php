<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\UserCommands;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\PhotoSize;
use Longman\TelegramBot\Funciones;
use PDO;
use Longman\TelegramBot\DB;
 

/**
 * User "/survey" command
 *
 * Command that demonstrated the Conversation funtionality in form of a simple survey.
 */
class GpsCommand extends UserCommand
{
    
    protected $name = 'gps';
    protected $description = 'Guardar posicion GPS';
    protected $version = '0.3.0';
    protected $need_mysql = true;
    protected $private_only = true;
    protected $conversation;
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
			 $text = '';		 
			 //Funciones::dump(  $chat   ,  $user_id );
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
       $data = ['chat_id' => $chat_id,   ];

        if ($chat->isGroupChat() || $chat->isSuperGroup()) {
            //reply to message id is applied by default
            //Force reply is applied by default so it can work with privacy on
            $data['reply_markup'] = Keyboard::forceReply(['selective' => true]);
        }

        //Conversation start
        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];

        //cache data from the tracking session if any
        $state = 0;
        if (isset($notes['state'])) {
            $state = $notes['state'];
        }

        $result = Request::emptyResponse();

        switch ($state) {
            case 0:
				$f=new Funciones();						    
				$lng =  $f->get_lenguaje_actual($user_id  );
				$frase_boton = $f->traduccion('share.or.choose',$lng );
					
                if ( ($message->getLocation() === null) &&
				   ( substr($text, 0, 4) <> 'city' ) )
				{
                    $notes['state'] = 0;
                    $this->conversation->update();
					$reply_markup=(new Keyboard(
                       [ (new KeyboardButton($frase_boton))->setRequestLocation(true),
						 (new KeyboardButton('Cancel'))->setRequestLocation(false)
						  ]
                    ))
                        ->setOneTimeKeyboard(true)
                        ->setResizeKeyboard(true)
                        ->setSelective(true);
                              
				    $emj_clip = "\xF0\x9F\x93\x8E";
					
                    //Funciones::dump( $reply_markup,$chat_id);
					$frase = $f->traduccion('share.location_1',$lng );
                   // $data['text'] = "In order to show the forecast in your city,".PHP_EOL." you must:".PHP_EOL.
				    $data['text'] = $frase.PHP_EOL.
					"   - share your location  or".PHP_EOL.
					"   - choose another place on the map ( $emj_clip  )".PHP_EOL.
					"   - write like this: 'city city_name,country' ".PHP_EOL.
					"           city roma, it ";
					Request::sendMessage($data);
					
					
					
					$data['text'] = 'Actual location ';					
					$data['text'] .= Funciones::get_gps_guardado($user_id );
                    
					$data['reply_markup'] = $reply_markup;     
 					$data['reply_markup']= $reply_markup; 
                    $result = Request::sendMessage($data);
                    break;
				}
				else
				{
					if  ($message->getLocation() <> null) 
					{
						$notes['longitude'] = $message->getLocation()->getLongitude();
						$notes['latitude']  = $message->getLocation()->getLatitude();
					}
					elseif ( strtolower(substr($text, 0, 4)) == 'city' ) 
					{
						$find='';$ciudad='';$pais='';
						$find = substr($text,5);
						if ( $find <> '' )
							{
								$busco = explode(',',$find);
								if (isset($busco[0])) $ciudad = trim($busco[0]);
								if (isset($busco[1])) $pais = trim($busco[1]);							
								$data['text'] = 'Buscamos '.$ciudad.' '.$pais;							
								$find = $this->findcity($ciudad,$pais)	;
								if ( count( $find ) == 0 )
								{
									
									$data['text'] = 'Buscamos '.$ciudad.' '.$pais. ' Nada encontrado';	
									$notes['state'] = 0;
									$this->conversation->update();									
									$result = Request::sendMessage($data);
									break;
								}
								elseif (count( $find ) == 1 )
								{
									$coords = json_decode($find[0]['coord'], true);
									$notes['latitude'] =$coords['lat'];
									$notes['longitude'] =$coords['lon'];								
									
								}
								else
								{
									/* Hay varias ciudades que coinciden, ponerlas en una lista */
									$data['text'] = 'Hay varias  ('.count( $find ).') '.$ciudad.' '.$pais. ' Seleccionar una ';	
									$coords = json_decode($find[0]['coord'], true);
									$notes['latitude'] =$coords['lat'];
									$notes['longitude'] =$coords['lon'];	
									
									//Funciones::dump(   $find, 662767623 ); 
								}
								//Request::sendMessage($data); 	
							}
					
					}
					
					
                }

                

             
			
			    
            case 7:
                $this->conversation->update();
				//Funciones::dump(  $notes   ,  $user_id );
                $out_text = 'saving location ..' . PHP_EOL;
				$data['reply_markup'] = Keyboard::remove(['selective' => false]);
                 $out_text.= Funciones::set_gps_actual( $user_id  ,  $notes['latitude'],$notes['longitude']);
                foreach ($notes as $k => $v) {
                 //   $out_text .= PHP_EOL . ucfirst($k) . ': ' . $v;
                }
				$data['text'] = $out_text;
                $data['caption']      = $out_text;
				unset($notes['state']);
                $this->conversation->stop();
				$result = Request::sendMessage($data);
				return $this->telegram->executeCommand('bmnu');
                break;
        }

        return $result;
    }
}
