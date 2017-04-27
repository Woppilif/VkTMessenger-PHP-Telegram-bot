<?php 
define('VK_API','https://api.vk.com/method/');
class vkRequests{
	private $params = array();
	private $method = '';
	public function __construct($vk_id,$vk_token){
		$this->params['user_id'] = '';
		$this->params['access_token'] = '';
		$this->params['chat_id'] = '';
		$this->params['v'] = '5.37';


		$this->params['user_id'] = $vk_id;
		$this->params['access_token'] = $vk_token;
		$this->params['v'] = '5.37';
		
	}
	public function getOwner(){
		return $this->params['user_id'];
	}
	public function returnMessagesR(){
		return $this->getMessages()['response']['items'];
	}
	public function returnUsers($user_ids = array()){
		return $this->getUsers($user_ids)['response'];
	}

	public function getChatId($data){
		if( isset($data['chat_id']))  return $data['chat_id'];
		if(!isset($data['chat_id'])) return 0;
	}
	public function getUsers($user_ids = array()){
		$this->method = 'users.get';
//		unset($this->params['user_id']);
//		unset($this->params['v']);
		$this->params['user_ids'] = $user_ids;
		$this->params['name_case'] = 'Nom';
		$this->params['v'] = '5.63';
		return json_decode($this->executeRequest(),true);

	}

	public function getMessages(){
		$this->method = 'messages.get';
		$this->params['out'] = '0';
		$this->params['count'] = 10;
		return json_decode($this->executeRequest(),true);

	}

	public function sendMessage($message){
		$this->method = 'messages.send';
		$this->params['message'] = $message['text'];
		$vk_message = $this->selectDestination($message);
		if($vk_message['chat_id'])
		{
       		$this->params['chat_id'] = $vk_message['chat_id'];
       		unset($this->params['user_id']);
    	} 
    	else 
    	{
       		$this->params['user_id'] = $vk_message['user_id'];
    	}
		return json_decode($this->executeRequest(),true);
	}
	
	public function selectDestination($data){
		$reply_data = $data['reply_to_message'];

		$reply_message = R::findOne('telegram','message_id = ?',[$reply_data['message_id']]);
		if(!$reply_message) return false;
		$vk_message = R::findOne('messages','id = ?',[$reply_message['added_message']]);
		if(!$vk_message) return false;

		
    	return $vk_message;

	}

	private function executeRequest(){
		return file_get_contents(VK_API.$this->method, false, stream_context_create(array(
			'http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => http_build_query($this->params)
				)
			)));
	}
}