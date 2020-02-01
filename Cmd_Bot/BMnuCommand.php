<?php 
namespace Longman\TelegramBot\Commands\SystemCommands;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Funciones;
use Longman\TelegramBot\Entities\InputMedia\InputMediaPhoto;
require_once __DIR__ . '/../Funciones/BMnu.php';
use BMenu\BMnu;
error_reporting(E_ALL);

class BmnuCommand extends UserCommand
{
    protected $name = 'Bmnu';
    protected $description = 'Menu';
    protected $usage = '/Bmnu';
    protected $version = '0.1.0';
	
	
	public function armar_menu($CAT='PRIN',$actual=0,$item_id=0,$lang='en')
    {
		//Funciones::debug_a_admins_php(   'yo', $this->getCallbackQuery()->getMessage()->getChat() ); 
		$actual = intval($actual) ; //Este es el numero de item que hay que incrementar/decrementar
		$prox = $actual + 1;
		$ant = $actual - 1;
		$proxd = $actual + 2;
		$antd = $actual - 2;
		$f=new Funciones();
		switch($CAT)
		{
			
			case 'PRIN';						
				/* Menu Principal de Pizzeria */
				$MENU=new BMnu($CAT);
				//Funciones::debug_a_admins_php(   'yo', $lang ); 
				$MENU->setTit('Principal');
				$MENU->setCols(array(1,1,2,3,2,3));
				$MENU->setRet('');				
				$MENU->add_btn($f->traduccion('weather', $lang) ,"pag;WEATHER;0");
				$MENU->add_btn( $f->traduccion('share', $lang),'switch_inline_query;""');						
				$MENU->add_btn( $MENU->emoji('U+2699'). ' '.$f->traduccion('settings', $lang),'bmnu;CFG');
				
				break;	
			case 'WEATHER';	
				/* Menu de Pizzas */
				$MENU=new BMnu($CAT);
				$MENU->setTit('Weather Bot');
				$MENU->setCols(array(3,3,2,1));
				$MENU->setRet('PRIN');
				$MENU->actual_item = $actual;
				$MENU->item_id = $item_id;
				$MENU->add_btn( '< ',"pag;$CAT;$ant");
				$MENU->add_btn( $f->traduccion('now', $lang),"pag;$CAT;0");				
				$MENU->add_btn( '> ',"pag;$CAT;$prox");
				$MENU->add_btn( '<< ',"pag;$CAT;$antd");				
				$MENU->add_btn( $f->traduccion('ciudad', $lang),'cmd;gps');
				$MENU->add_btn( '>> ',"pag;$CAT;$proxd");
				break;	
			case 'CFG';			
			
				/* Menu Principal de Pizzeria */
				$MENU=new BMnu($CAT);
				$MENU->setTit('Actualizá tus datos');
				$MENU->setCols(array(3,2,1,3,2,3));
				$MENU->setRet('PRIN');
				//$MENU->add_btn( 'Recomendar','switch_inline_query;""');				
				//$MENU->add_btn( 'Web','login_url;https://redengo.com/pizza/check_authorization.php');				
				//$MENU->add_btn( 'DashBoard','login_url;https://redengo.com/pizza/dash/check_authorization.php');				
				$MENU->add_btn( $MENU->emj_satellite.' Position','cmd;gps');
				$MENU->add_btn( ' Language','bmnu;LANG');
				
				
				//$MENU->add_btn( 'Ayuda','cmd;ayudapza');
				break;	
			case 'LANG';	
				/* Menu de Pizzas */
				$MENU=new BMnu($CAT);
				$MENU->setTit('Language');
				$MENU->setCols(array(2,2,2,2,2,2,2,1));
				$MENU->setRet('PRIN');
				$MENU->actual_item = $actual;
				$MENU->item_id = $item_id;
				$MENU->add_btn( "🇵🇹" .' Portugues',"Lng;pt");
				$MENU->add_btn( "🇷🇺 ".'Russia ',"Lng;ru");
				$MENU->add_btn( "🇪🇸 󠁧󠁢󠁥󠁮󠁧󠁿". 'Español',"Lng;es");
				$MENU->add_btn( "🏴󠁧󠁢󠁥󠁮󠁧󠁿" .'Inglés',"Lng;en");
				$MENU->add_btn('🇮🇹'.'Italiano',"Lng;it");
				$MENU->add_btn('🇫🇷'.'Frances',"Lng;fr");
				$MENU->add_btn('🇩🇪'.'German',"Lng;de");
				$MENU->add_btn('🇳🇱'.'Dutch',"Lng;nl");
				$MENU->add_btn('🇦🇪'.'Arabic',"Lng;ar");
				
				
				
				
				break;	
			default;
				/* Menu Principal de Pizzeria */
				$MENU=new BMnu('PRIN');
				$MENU->setTit('Principal');
				$MENU->setCols(array(1,1,2,3,2,3));
				$MENU->setRet('');				
				$MENU->add_btn($f->traduccion('weather', $lang) ,"pag;WEATHER;0");
				$MENU->add_btn( $f->traduccion('share', $lang),'switch_inline_query;""');						
				$MENU->add_btn( $MENU->emoji('U+2699'). ' '.$f->traduccion('settings', $lang),'bmnu;CFG');
			 
				break;	
				/*$MENU=new BMnu($CAT);
				$MENU->setTit('Errror llamando al menu con '.$CAT);
				$MENU->setCols(array(1,1,2,2,3));
				$MENU->setRet('PRIN');				
				$MENU->add_btn( 'ir al menu principal','bmnu;PRIN');
				$MENU->add_btn( 'ERROR'.$CAT,'cmd:help');
				*/

			break;
			
		}
		return $MENU;
		
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
		
		
		$M = ($text == '') ? 'PRIN' : $text; //Trae el text del ultimo getMessage, no sirve
		//$M = 'WEATHER';
        $update         = $this->getUpdate();
		
		$data = [
            'chat_id'      => $chat_id,
			'parse_mode' => 'HTML',                     
        ];		
		$data['disable_web_page_preview'] = false;
		$f=new Funciones();			 		
		$lng =  $f->get_lenguaje_actual($user_id);
		$MENU = $this->armar_menu($M,1,0,$lng);
		$MENU->actual_item = 1;
		
		//Funciones::debug_a_admins_php(   'yo', $MENU->show()); 
		$data['text'] = '*'.$MENU->getTit().'*' . $this->getTelegram()->getBotId() ;	
		
		$imgurl=__DIR__.'/../imagenes/weather/owm.png';
		$data['parse_mode']='HTML';
		
		$lengua=$f->nombre_lenguaje($lng );
		$data['caption']= 'Open Weather Bot     <i>'.$lengua['nativeName'].' '.$lengua['name'].'</i> ';
		$data['photo']   = Request::encodeFile($imgurl);		
		$data['reply_markup']= $MENU->show(); 						
		
		$data['media'] = new InputMediaPhoto([
							  'type' => 'photo',
					            'caption' => 'Principal mnu',
							   'parse_mode' => 'HTML',
					          'media'   => Request::encodeFile($imgurl),
					      ]);
		//return Request::editMessageMedia($data);		
	   //return Request::sendMessage($data);        
		return Request::sendPhoto($data);
    }	
}
?>
