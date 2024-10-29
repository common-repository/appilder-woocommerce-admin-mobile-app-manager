<?php
class Appilder_woocommerce_admin_push {
	static $gcm = 1;
	static $apns = 2;
	static $services ;
	static  $table,$history_table;
	static function init(){
		self::$services =array(
			self::$gcm => "GCM_Send"
		);
	}

	public function __construct(){
		$this->init();
		$this->gcm_auth_key = get_option('appilder_woo_admin_gcm_key');
	}

	public function sendPush($message,$title,$actionType,$actionParam){
		$suc=0;
		$fail=0;
		$data =array();
		foreach(self::$services as $service){
			$return = call_user_func(array($this,$service),$message,$title,$actionType,$actionParam);
			$suc += $return['response']['success'];
			$fail +=  $return['response']['fail'];
			$data[$service]= $return['data'];
		}
		$return = array("status"=>1,"success"=>$suc,"fail"=>$fail);
		return $return;
	}
	public function GCM_Send($message,$title,$actionType,$actionParam){
		$ids = $this->getIds();
		$ids = array_chunk($ids,999);
		$url = 'https://android.googleapis.com/gcm/send';
		$headers = array(
			'Authorization: key=' . $this->gcm_auth_key,
			'Content-Type: application/json');
		$fields = array(
			'registration_ids' => array(),
			'data' => array("message" => $message,"text" => $message,"title"=>$title,"content"=>$message,"actionType"=>$actionType,"actionParam"=>$actionParam,
			                "extra"=>array("actionType"=>$actionType,"actionParam"=>$actionParam)
			),
		);
		$suc=0;
		$fail=0;
		$answer = array();
		foreach($ids as $i=>$chunk) {
			$fields["registration_ids"] = $chunk;
			$result =$this->sendRequest($url, $fields, $headers);
			$answer[$i] = json_decode($result);
			if($answer[$i]) {
				$suc += $answer[$i]->{'success'};
				$fail += $answer[$i]->{'failure'};
			}
		}
		return array("data"=>$answer,"response"=>array("status"=>1,"success"=>$suc,"fail"=>$fail));
	}
	private function sendRequest($url,$fields,$headers){
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $fields ));
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	public static function getIDS(){
		$users = get_option('appilder_woo_admin_gcm_users',array());
		return array_keys($users);
	}
	public static function getCount(){
		$users = get_option('appilder_woo_admin_gcm_users',array());
		return count(array_keys($users));
	}
}
Appilder_woocommerce_admin_push::init();