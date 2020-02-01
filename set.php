<?php
/**
 * README
 * This file is intended to set the webhook.
 * Uncommented parameters must be filled
 */

// Load composer
require_once __DIR__ . '/../vendor/autoload.php';

$bot = isset($_REQUEST['bot']) ? $_REQUEST['bot'] : 'Gardi_bot';

 switch ($bot) {
            case 'pizzero_bot':
					$bot_api_key  = '909881128:AAErjYS57qW-HI-3m2zsrt1_-fHdN6Ht_EQ';
					$bot_username = 'pizzero_bot';
					$hook_url     = 'https://redengo.com/bots/pizzero/hook.php?bot=pizzero_bot';			    
               break;
           case 'Gardi_bot':
   					$bot_api_key  = '817206183:AAH_CXTGEe8J_ucRYt5DxOlKLUODciPfY5o';
					$bot_username = 'Gardi_bot';
					$hook_url     = 'https://redengo.com/bots/bots6/imgbot/hook.php?bot=Gardi_bot';			    
					break;
			case 'fcstbot':
   					$bot_api_key  = '1026050025:AAFSFQlxIJjD5NPefxBFKlAuGFLTtJwHEmM';
					$bot_username = $bot;
					$hook_url     = 'https://redengo.com/bots/bots6/imgbot/hook.php?bot='.$bot;			    
					break;
		       
            case 'chepiBot':
					$bot_api_key  = '920563592:AAFINtvJPH6ovDVVY24Nfav73LXp_a0oBAE';
					$bot_username = 'chepiBot';
					$hook_url     = 'https://redengo.com/bots/pizzero/hook.php?bot=chepiBot';	
					break;
			default:
				$bot_api_key  = '909881128:AAErjYS57qW-HI-3m2zsrt1_-fHdN6Ht_EQ';
					$bot_username = 'pizzero_bot';
					$hook_url     = 'https://redengo.com/bots/pizzero/hook.php?bot=pizzero_bot';	
 }

					

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Set webhook
    $result = $telegram->setWebhook($hook_url);

    // To use a self-signed certificate, use this line instead
    //$result = $telegram->setWebhook($hook_url, ['certificate' => $certificate_path]);

    if ($result->isOk()) {
        echo $result->getDescription();
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    echo $e->getMessage();
}
