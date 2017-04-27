<?php
class VKMessages extends vkRequests{
	private $messages = array();
	private $user_ids = array();
	private $users_names = array();

	private $create_messages = array();

	private $created_names = array();
	private $messages_to_send = array();


	public function answerMessage($data = array()){
		return $this->sendMessage($data);
	}

	public function getNames(){
		for($i=0;$i<count($this->messages);$i++){
			$this->user_ids[$i] = $this->messages[$i]['user_id'];
		}
		return $this->returnUsers($this->user_ids);
	}
	public function createNames($input = array()){
		for($i=0;$i<count($input);$i++)
		{
			$this->created_names[$i]['user_id'] = $input[$i]['id'];
			$this->created_names[$i]['user_name'] = "".$input[$i]['first_name']." ".$input[$i]['last_name']."";

		}
		return $this->created_names;
	}
	public function returnUsername($user_id){
		$key = array_search($user_id,array_column($this->users_names, 'user_id') );
		return $this->users_names[$key]['user_name'];
	}
	
	
	
	public function getAttachments($ats = array()){
		if(!isset($ats['attachments'])) return false;
		$atsX = ' ';
		for($i=0;$i<count($ats['attachments']);$i++)
		{
			if(isset($ats['attachments'][$i]['photo']))
			{
				$atsX .=  $ats['attachments'][$i]['photo']['photo_604']." ";
			}

			if(isset($ats['attachments'][$i]['sticker']))
			{
				$atsX .=  $ats['attachments'][$i]['sticker']['photo_128']." ";
			} 

			if(isset($ats['attachments'][$i]['doc']))
			{
				$atsX .=  $ats['attachments'][$i]['doc']['url']." ";
			}
			
		}
		return $atsX;
	}


	public function messagesArray($i){
			$this->create_messages[$i]['message_id'] = $this->messages[$i]['id'];
			$this->create_messages[$i]['title'] = $this->messages[$i]['title'];
			$this->create_messages[$i]['user_id'] = $this->messages[$i]['user_id'];
			$this->create_messages[$i]['user_name'] = $this->returnUsername($this->messages[$i]['user_id']);
			$this->create_messages[$i]['chat_id'] = $this->getChatId($this->messages[$i]);
			$this->create_messages[$i]['owner_id'] = $this->getOwner();
			$this->create_messages[$i]['text'] = $this->messages[$i]['body'].$this->getAttachments($this->messages[$i]);
			$this->create_messages[$i]['date'] = $this->messages[$i]['date'];
			$this->create_messages[$i]['exists'] = 0;
	}
	public function isInDB($i){
			$message = R::findOne('messages','message_id = ? AND date = ? AND vk_id = ?',[
				$this->create_messages[$i]['message_id'],
				$this->create_messages[$i]['date'],
				$this->create_messages[$i]['owner_id']]);

			if($message){}
			else{
				$mess = R::dispense('messages');
				$mess->vk_id = $this->create_messages[$i]['owner_id'];
				$mess->message_id = $this->create_messages[$i]['message_id'];
				$mess->date = $this->create_messages[$i]['date'];
				$mess->chat_id = $this->create_messages[$i]['chat_id'];
				$mess->user_id = $this->create_messages[$i]['user_id'];

				
				$id = R::store($mess);		
				$this->create_messages[$i]['added_message'] = $id;

				$this->messages_to_send[$i] = $this->create_messages[$i];

			}
			
			return $this->create_messages;
	}
	public function sendMessages(){
		return array_reverse($this->messages_to_send);
	}
	public function createMessage(){
		for($i=0;$i<count($this->messages);$i++)
		{
			$this->messagesArray($i);
			$this->isInDB($i);
	
			
		}
		
		return $this->create_messages;
		
	}
	public function returnMessages(){
		$this->messages = $this->returnMessagesR();
	
		$this->users_names = $this->createNames($this->getNames());
		$this->createMessage();
		return $this->sendMessages();
	}


}