<?php
// Load composer
error_reporting(E_ALL);
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Funciones/Funciones.php';
error_reporting(E_ERROR);

use Longman\TelegramBot\TelegramLog;


$bot = isset($_REQUEST['bot']) ? $_REQUEST['bot'] : 'Gardi_bot';
$bot_username = $bot;
$hook_url     = 'https://redengo.com/bots/bots6/imgbot/hook.php?bot='.$bot;		
 switch ($bot) {
            case 'pizzero_bot':
					$bot_api_key  = '909881128:AAErjYS57qW-HI-3m2zsrt1_-fHdN6Ht_EQ';
               break;
           case 'Gardi_bot':
   					$bot_api_key  = '817206183:AAH_CXTGEe8J_ucRYt5DxOlKLUODciPfY5o';
					break;
			case 'fcstbot':
   					$bot_api_key  = '1026050025:AAFSFQlxIJjD5NPefxBFKlAuGFLTtJwHEmM';
						    
					break;
            case 'chepiBot':
					$bot_api_key  = '920563592:AAFINtvJPH6ovDVVY24Nfav73LXp_a0oBAE';
					break;
			default:
			     
				$bot_api_key  = '909881128:AAErjYS57qW-HI-3m2zsrt1_-fHdN6Ht_EQ';
					$bot_username = 'pizzero_bot';
					$hook_url     = 'https://redengo.com/bots/pizzero/hook.php?bot=pizzero_bot';	
 }


$admin_users = [
  662767623,
  480434336
//    123,
];
$commands_paths = [
     __DIR__ . '/Cmd_Admin/'  ,	
	 __DIR__ . '/Cmd_Bot/'  ,	


];

$mysql_credentials = [
     'host'     => 'localhost',
    'user'     => 'damdng',
     'password' => 'Damian200',
     'database' => 'pizzabot',
 ];

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Add commands paths containing your custom commands
    $telegram->addCommandsPaths($commands_paths);

    // Enable admin users
    $telegram->enableAdmins($admin_users);

    // Enable MySQL
    $telegram->enableMySql($mysql_credentials);
	

    // Set custom Upload and Download paths
     $telegram->setDownloadPath(__DIR__ . '/Download');
     $telegram->setUploadPath(__DIR__ . '/Upload');

    // Here you can set some command specific parameters
    // e.g. Google geocode/timezone api key for /date command
    //$telegram->setCommandConfig('date', ['google_api_key' => 'your_google_api_key_here']);
/*
	$telegram->setCommandConfig('cleanup', [
	      // Define which tables should be cleaned.
	      'tables_to_clean' => [
	          'message',
	          'edited_message',
	      ],
	      // Define how old cleaned entries should be.
	      'clean_older_than' => [
	          'message'        => '3 days',
	          'edited_message' => '3 days',
	      ]
		  ]
	  );

*/

    // Requests Limiter (tries to prevent reaching Telegram API limits)
    $telegram->enableLimiter();

    $telegram->handle();
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // log telegram errors
     echo $e->getMessage();
}
