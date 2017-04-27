<?php
date_default_timezone_set('Europe/Moscow');
define('CONTROLLERS',dirname(__FILE__) .'/engine/controllers/');
define('DB',dirname(__FILE__) .'/engine/database/');
define('SETTINGS',dirname(__FILE__) .'/engine/');
require_once DB.'rb.php';
require_once CONTROLLERS.'request.php';
require_once CONTROLLERS.'message.php';
require_once CONTROLLERS.'vkRequests.php';
require_once CONTROLLERS.'vk.php';
require_once CONTROLLERS.'user.php';
require_once CONTROLLERS.'config.php';
$config = new Config();
R::setup( 'mysql:host='.$config->db_host.';dbname='.$config->db_name.'', $config->db_user, $config->db_password );
define('BOT_TOKEN', $config->token);
define('API_URL', $config->api_url.BOT_TOKEN.'/');
define('WEBHOOK_URL', $config->webhook_url);

$request = new Request();

$content = file_get_contents("php://input");
$update = json_decode($content, true);
if (!$update) {
  // receive wrong update, must not happen
	exit;
}

if (isset($update["message"])) {
	//Log your JSON requests
	$myFile = "json.txt";
	file_put_contents($myFile,$content);

	$message = new Message($update["message"]);
	$request->getChatId($message->getChatId());
	$user = new User($update["message"]);
	$vk = new VKMessages($user->getVkId(),$user->getToken());


	$vkRequest = new vkRequests($user->getVkId(),$user->getToken());

	

	if(isset($update["message"]['reply_to_message']))
	{

		$answer = $vk->answerMessage($update["message"]);

		if(!$answer) return $request->sendMessage("Что-то пошло не так :(");

		$request->sendMessage("Отправлено");


	}else {

	switch($message->getMessage())
	{
		case '/start':
			if(!$message->getMessageParam(1)) return $request->sendMessage("Привет! Отправь свой ID в VK \n/start <vk_id>");
			$user->setVkId($message->getMessageParam(1));

			$request->sendMessage("Хорошо, теперь нужно указать ID твоего Standalone Application\n/app <app_id>");
			
			
		break;
		case '/app':
		if(!$message->getMessageParam(1)) return $request->sendMessage("Пожалуйста укажи ID приложения\n/app <app_id>");
			$user->setAppId($message->getMessageParam(1));

			$request->sendMessage("Хорошо, теперь нужно авторизоваться в приложении перейдя по ссылке: \nhttps://oauth.vk.com/authorize?client_id=".$user->getAppId()."&display=page&redirect_uri=https://oauth.vk.com/blank.html&scope=offline,messages&response_type=token&v=5.37 \nПосле чего в адресной строке в куче символов будет  access_token. Его нужно скопировать и указать таким образом: /token <access_token>");
		break;
		case '/token':
			if(!$message->getMessageParam(1)) return $request->sendMessage('Отправь свой access_token');
			$user->setToken($message->getMessageParam(1));
			$request->sendMessage("Хорошо. Теперь можно получать сообщения следующей командой: /get");

		break;
		case '/get':

			if(!$vk->returnMessages()) return $request->sendMessage('Новых сообщений нет.');
			$data = $vk->returnMessages();
			for($i=0;$i<count($data);$i++)
			{
				$msg = $request->sendMessage("".$data[$i]['title']."\n".$data[$i]['user_name']."\n".$data[$i]['text']."\n".date("d M Y H:i:s",$data[$i]['date'])."");
				$message->logMessage($msg,$data[$i]['added_message']);
			}

		break;


		default:$request->sendMessage('Again');
	}
}
}
?>
