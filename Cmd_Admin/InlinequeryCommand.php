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
use Longman\TelegramBot\Entities\InlineQuery\InlineQueryResultArticle;
use Longman\TelegramBot\Entities\InputMessageContent\InputTextMessageContent;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Funciones;
use PDO;
use Longman\TelegramBot\DB;
/**
 * Inline query command
 *
 * Command that handles inline queries.
 */
class InlinequeryCommand extends SystemCommand
{
    protected $name = 'inlinequery';
    protected $description = 'Reply to inline query';

    /**
     * @var string
     */
    protected $version = '1.1.1';

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
	public function findcity($city,$country='')	
    {   
		$pdo = DB::getPdo(); if (! DB::isDbConnected()) {return false;}
		$sql = "SELECT
				`city.list`.id,
				`city.list`.`name` as title ,
				`city.list`.country as description,
				`city.list`.coord
				FROM
				`city.list`
				WHERE
				name like '".$city."%'";
		if ( $country <> '' ) $sql.= " and country like '".$country."%' ";
		$sql.="  limit 10  ";		
		$sth =   $pdo->prepare( $sql );
		$sth->execute();
		$a =$sth->fetchAll();
		return ( $a );
	}
    public function execute()
    {
        $inline_query = $this->getInlineQuery();
        $query        = $inline_query->getQuery();
        $data    = ['inline_query_id' => $inline_query->getId()];
		$user    = $inline_query->getFrom();
		$user_id = $user->getId();
		$f=new Funciones();
		$lng =  $f->get_lenguaje_actual($user_id );
		 
		$data['cache_time'] = 36000;
        $results = [];
		if (($query  == 'RECOMENDAR') 
			|| 	( substr( $query,0,3 ) == 'REC' )
			|| 	( substr( $query ) == '' )
			)
		 {
			 //$frase = Funciones::traduccion('share', $lng);
			 $article =  
					[
					'type'					=> 'article',
                    'id'                    => 'share',
                    'title'                 => 'Forecast weather',
                    'description'           => 'Open Weather Forecast',
					'url'					=> 't.me/fcstbot',
					'input_message_content' => new InputTextMessageContent
												(
												['message_text' => $user->getFirstName().' '. $f->traduccion('te.recomendo', $lng).' Forecast bot'.PHP_EOL.
													'<b>'.$f->traduccion('share', $lng).'</b> @fcstbot !',
												'parse_mode'=> 'HTML',
												'disable_web_page_preview' => true
												]
												
												
												)
					
					
                   // 'input_message_content' => new InputTextMessageContent(['message_text' => ' ' . $query]),
					];
			$results[] = new InlineQueryResultArticle($article);
			$data['results'] = '[' . implode(',', $results) . ']';	
            $data['switch_pm_text'] = 'Open Werather Bot';			
			$data['switch_pm_parameter'] = 'start';
			$data['is_personal'] = false;
			  $f->debug_a_admins_php( 'yo', $data );
			return Request::answerInlineQuery($data);
			 
			 
		 }
		 $results = [];
        if ($query !== '') 
		{
			if ( $find <> '' )
			{
				$ciudad ='';$pais='';
				$busco = explode(',',$find);
				if (isset($busco[0])) $ciudad = trim($busco[0]);
				if (isset($busco[1])) $pais = trim($busco[1]);
				
				$data['text'] = 'Buscamos '.$ciudad.' '.$pais;
			
				$citys = $this->findcity($ciudad,$pais)	;
				//Funciones::dump(   $citys, 662767623 ); 
				$articles=array();
				foreach ( $citys as $article )
				{
					
					$articles[] =  
					[
                    'id'                    => $article['id'],
                    'title'                 => $article['title'],
                    //'description'           => 'you enter: ' . $query,
					'description'           => 'Country: ' . $article['description'],
                    'input_message_content' => new InputTextMessageContent(['message_text' => ' ' . $query]),
					];
				}
					
			 
				$texto = "coming soon: ".PHP_EOL. "Inline forecast for selected city";
				//foreach ( $find as $article ) {
				foreach ($articles as $article) {			
				  
					$article ['input_message_content'] =  new InputTextMessageContent(
							['message_text' => $texto.' ('.$article['title'].','.$article['description'].')']
							);                
					$results[] = new InlineQueryResultArticle($article);
				}
			}
        }
        $data['results'] = '[' . implode(',', $results) . ']';		
		
		$data['switch_pm_text']='Ir al bot';
		//Funciones::debug_a_admins_php( 'yo', $data );
        return Request::answerInlineQuery($data);
    }
}

//@fcstbot ringuelet


