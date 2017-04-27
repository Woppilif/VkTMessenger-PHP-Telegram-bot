<?php
class Request
{
	private $chat_id;
	public function apiRequestWebhook($method, $parameters) {
		if (!is_string($method)) {
			error_log("Method name must be a string\n");
			return false;
		}
		if (!$parameters) {
			$parameters = array();
		} else if (!is_array($parameters)) {
			error_log("Parameters must be an array\n");
			return false;
		}
		$parameters["method"] = $method;
		header("Content-Type: application/json");
		echo json_encode($parameters);
		return true;
	}
	public function exec_curl_request($handle) {
		$response = curl_exec($handle);
		if ($response === false) {
			$errno = curl_errno($handle);
			$error = curl_error($handle);
			error_log("Curl returned error $errno: $error\n");
			curl_close($handle);
			return false;
		}
		$http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
		curl_close($handle);
		if ($http_code >= 500) {
    // do not wat to DDOS server if something goes wrong
			sleep(10);
			return false;
		} else if ($http_code != 200) {
			$response = json_decode($response, true);
			error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
			if ($http_code == 401) {
				throw new Exception('Invalid access token provided');
			}
			return false;
		} else {
			$response = json_decode($response, true);
			if (isset($response['description'])) {
				error_log("Request was successfull: {$response['description']}\n");
			}
			$response = $response['result'];
		}
		return $response;
	}
	public function apiRequest($method, $parameters) {
		if (!is_string($method)) {
			error_log("Method name must be a string\n");
			return false;
		}
		if (!$parameters) {
			$parameters = array();
		} else if (!is_array($parameters)) {
			error_log("Parameters must be an array\n");
			return false;
		}
		foreach ($parameters as $key => &$val) {
    // encoding to JSON array parameters, for example reply_markup
			if (!is_numeric($val) && !is_string($val)) {
				$val = json_encode($val);
			}
		}
		$url = API_URL.$method.'?'.http_build_query($parameters);
		$handle = curl_init($url);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($handle, CURLOPT_TIMEOUT, 60);
		return $this->exec_curl_request($handle);
	}
	public function apiRequestJson($method, $parameters) {
		if (!is_string($method)) {
			error_log("Method name must be a string\n");
			return false;
		}
		if (!$parameters) {
			$parameters = array();
		} else if (!is_array($parameters)) {
			error_log("Parameters must be an array\n");
			return false;
		}
		$parameters["method"] = $method;
		$handle = curl_init(API_URL);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($handle, CURLOPT_TIMEOUT, 60);
		curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
		curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		return $this->exec_curl_request($handle);
	}
	public function getChatId($chat_id)
	{
		$this->chat_id = $chat_id;
	}
	public function sendMessage($text)
	{
		$data = $this->apiRequest("sendMessage", array('chat_id' => $this->chat_id, "text" =>  $text));
		return $data;
	}

	public function sendMessageTo($text,$chat)
	{
		$this->apiRequest("sendMessage", array('chat_id' => $chat, "text" =>  $text));
	}
	public function sendMessageWK($text,$buttons = array())
	{
		$this->apiRequestJson("sendMessage", array('chat_id' => $this->chat_id, "text" => $text, 'reply_markup' => array(
        'keyboard' => $buttons,
        'one_time_keyboard' => true,
        'resize_keyboard' => false)));
	}

}
