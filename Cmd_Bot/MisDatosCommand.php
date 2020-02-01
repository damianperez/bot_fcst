<?php
namespace Longman\TelegramBot\Commands\UserCommands;
use Longman\TelegramBot\Commands\SystemCommands\BMnuPzaCommand;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Funciones;
use PDO;
use Longman\TelegramBot\DB;
//use Translation\Translation;
//use BMenu\BMnu;

/**
 * MisDatos command
 */
class MisDatosCommand extends UserCommand
{
    protected $name = 'MisDatos';
    protected $description = 'Muetra la carta de la Pizzeria';
    protected $usage = '/MisDatos';
    protected $version = '1.1.0';
    protected $private_only = true;
	protected $conversation;
	public	 function GuardarUsuario( $valores , $id )
	{
	/* Valores se corresponde a notes */
	$retorno = '';
	$set = "";
	/*
	first_name
	last_name
	dni
	correo
	telefono 
	domicilio
	*/	
	
	foreach ( $valores  as $que =>$valor )
		{   
		    $que = strtolower($que);
			if ( $que == 'nombre' ) $que='first_name';
			if ( $que == 'apellido' ) $que='last_name';
			
			if (isset(  $valores[$que] ))
				$set.="$que= '".$valor."'  ,".PHP_EOL; 

		}
	
    $set = substr($set,0,-3);

	$pdo = DB::getPdo();     if (! DB::isDbConnected()) {  return false;      }
	$sql = " update user set ".$set . " where id = $id ";
	
	//Funciones::debug_a_admins_php(   'yo', $sql ); 
	$sth =   $pdo->prepare( $sql );
	$status  = $sth->execute();
	if ( !$status ) 	return false;

	return true;

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
			 
		}
		else
		{
			$message = $this->getMessage() ?: $this->getEditedMessage();
			$chat    = $message->getChat();
			$user    = $message->getFrom();
			$chat_id = $chat->getId();
			$user_id = $user->getId();			
        }
		
		$text    = trim($message->getText(true));
		$data = [
					'chat_id'    => $chat_id,
					'user_id' => $user_id,					
				];
		
        if ($chat->isGroupChat() || $chat->isSuperGroup()) {
            //reply to message id is applied by default
            //Force reply is applied by default so it can work with privacy on
            $data['reply_markup'] = Keyboard::forceReply(['selective' => true]);
        }
		$dni=Funciones::tiene_dni($user_id);
		$correo=Funciones::tiene_email($user_id);
		$telefono=Funciones::tiene_telefono($user_id);
		
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
		
		$data['parse_mode'] = 'HTML';	

		
		
		if ( !isset($notes['dni'])) $notes['dni']=$dni;
		if ( !isset($notes['correo'])) $notes['correo']= $correo;
		if ( !isset($notes['telefono'])) $notes['telefono']=$telefono;
		$this->conversation->update();
        switch ($state) {
            case 0:
			    
                $notes['first_name'] =  $user->getFirstName();
				$data['text'] = "Hola ".$user->getFirstName().PHP_EOL.
								"<pre>Necesitamos conocer tu correo, teléfono y dni para poder realizar compras por este medio </pre>".PHP_EOL.
								"<i>Sólo lo ingresarás una vez, quedará almacenado para futuras compras </i>";
				
								
                if ( !empty($notes['dni'])) 
					{ $data['text'] .= "Tu dni es ".$notes['dni'].",";}
				else
					{ $data['text'] .= "Nos falta tu dni, ";}
				if ( !empty($notes['correo']) )
					{ $data['text'] .= "tu correo es ".$notes['correo'];}
				else
					{ $data['text'] .= "nos falta tu correo ";}
				if ( !empty($notes['telefono']) )
					{ $data['text'] .= "y tu telefono es ".$notes['telefono'];}
				else
					{ $data['text'] .= "y nos falta tu telefono ";}
				
				//Request::sendMessage($data);
				$text='';
				
            // no break
            case 1:
				$text = str_replace('usar Dni ',"", $text);
                if (empty($text) || $text == '' || !is_numeric($text)) 
				{
                    $notes['state'] = 1;
                    $this->conversation->update();                    
                    $data['text'] .= PHP_EOL.'Ingresá tu dni'.PHP_EOL.
									"<pre>Va a ser necesario para cualquier inconveniente en el proceso</pre>";
									
					$data['reply_markup'] = (new Keyboard(
                        (new KeyboardButton( 'usar Dni '.$notes['dni'] ))
                    ))->setOneTimeKeyboard(true)
                        ->setResizeKeyboard(true)
                        ->setSelective(true);
                    $result = Request::sendMessage($data);
                    break;
                }
				
				$notes['dni']=$text;
				$text = '';
				
            // no break
			case 2:
			     $text = str_replace('usar email ',"", $text );
				 if ( $text == '' || !filter_var($text, FILTER_VALIDATE_EMAIL))
				 {
					 $notes['state'] = 2;	 
					 $this->conversation->update();
					 $data['text'] ="Ingrespa tu correo electrónico:";
					 if ( filter_var($notes['correo'], FILTER_VALIDATE_EMAIL))
					 {
						 $data['reply_markup'] = (new Keyboard(
							(new KeyboardButton( 'usar email '.$notes['correo'] ))
						))
						->setOneTimeKeyboard(true)
                        ->setResizeKeyboard(true)
                        ->setSelective(true);
					 }
					 $result = Request::sendMessage($data);
					break;
				 }
				 $notes['correo']= $text;				
				 $text ='';
			case 3:
				  $text = str_replace('usar ',"", $text);
				  Funciones::debug_a_admins_php(   'yo', $text ); 
				  if ($message->getContact() <> null ) 	  $text = $message->getContact()->getPhoneNumber();
				  if ( !preg_match('/^\+?\d+$/', $text)  ) 
				  {
					 $notes['state'] = 3;	 
					 $this->conversation->update();
					 $data['text'] ="Ya casi estamos. Ahora sólo necesitamos tu número de teléfono".PHP_EOL.
									"<pre>Presioná el botón 'Compartir mi número'</pre>";
						

					$data['reply_markup'] = (new Keyboard(
                        (new KeyboardButton('Compartir mi número'))->setRequestContact(true),
						(new KeyboardButton('usar '.$notes['telefono'])))
						)
                        ->setOneTimeKeyboard(true)
                        ->setResizeKeyboard(true)
                        ->setSelective(true);

                    
                    $result = Request::sendMessage($data);
                    break;
                 }
				if ($message->getContact() <> null ) 
				{
					$notes['first_name'] = $message->getContact()->getFirstName();
					$notes['last_name'] = $message->getContact()->getLastName();
					$notes['telefono'] = $message->getContact()->getPhoneNumber();
				}
				else
				{
					$notes['telefono'] = $text;
				}
				
                
				 $text ='';
            case 7:
                $this->conversation->update();
                $out_text = '*Tus datos:*' . PHP_EOL;
				
                unset($notes['state']);
                foreach ($notes as $k => $v) {
                    $out_text .= PHP_EOL . ucfirst($k) . ': ' . $v;
                }
                //$data['photo']        = $notes['photo_id'];
				$data['text'] = $out_text;
                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                $data['caption']      = $out_text;
				
				if ( !$this->GuardarUsuario( $notes , $user_id ))							
					{ $data['text']='Error guardando datos del usuario';
						$result = Request::sendMessage($data);
					}
				
                $this->conversation->stop();
                /*** Si existe conversa con finalizar pagos, continuarla *////
				
				$conversation = new Conversation(
						$user_id,
						$chat_id,
						'FinalizarPedido'						
					);
				if ($conversation->exists() && ('FinalizarPedido' == $conversation->getCommand())) {
						return $this->telegram->executeCommand('FinalizarPedido');
					}
				
				$menu = BMnuPzaCommand::armar_menu( 'PRIN' );
		$data['reply_markup']= $menu->show(); 
		return Request::sendMessage($data);
				
				return $this->telegram->executeCommand('BMnuPza');
                break;
        }
        return $result;		
		/*
		$menu = BMnuPzaCommand::armar_menu( 'PEDIDOS' );
		$data['reply_markup']= $menu->show(); 
		Request::sendMessage($data);
		*/
		//return $this->telegram->executeCommand('BMnuPza');
    }
}
