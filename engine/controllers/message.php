<?php
class Message
{
	private $chat_id;
	private $message_id;
	private $message;
	public function __construct($message)
	{
		if(isset($message))
		{
			$this->chat_id = $message['chat']['id'];
			$this->message_id = $message['message_id'];
			$this->message = $message['text'];


			
		}
		
	}
	public function logMessage($message,$data){
		$mess = R::dispense('telegram');
				
		$mess->chat_id = $message['chat']['id'];
		$mess->message_id =  $message['message_id'];

		$mess->added_message = $data;
	//	$mess->message = $message['text'];
		R::store($mess);
	}
	public function getChatId()
	{
		return $this->chat_id;
	}
	public function getMessageId()
	{
		return $this->message_id;
	}
	public function getMessage()
	{
		$command  = explode(" ", $this->message);
		return $command[0];
	}
	public function getWholeMessage()
	{
		$command  = explode(" ", $this->message);
		unset($command[0]);


		return implode($command);
	}
	public function getMessageParam($param)
	{
		$command  = explode(" ", $this->message);
		if(array_key_exists($param,$command)) return $command[$param];
		else return false;

	}
}