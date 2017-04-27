<?php
class Config {
	private $data = array();

	public function __construct() {
		if(is_readable(SETTINGS . 'config.php')) {
			require_once(SETTINGS . 'config.php');
			$this->data = array_merge($this->data, $config); //array_merge сливание массивов в один
			return true;
		}
		exit('Error:Couldnt load file!');
	}

	public function __set($key, $val){
		$this->data[$key] = $val;
	}

	public function __get($key){
		if(isset($this->data[$key])){
			return $this->data[$key];
		}
		return false;
	}
}
?>
