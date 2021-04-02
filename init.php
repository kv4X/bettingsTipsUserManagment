<?php
if(!session_id()){
    session_start();
}

include_once 'includes/db.php';
ini_set("display_errors", true);
date_default_timezone_set("Europe/Sarajevo");

/* BAZA */
define("DB_SERVER", "116.203.222.120");
define("DB_USERNAME", "root");
define("DB_PASSWORD", "almir1233");
define("DB_DATABASE", "nostalgija");

$db = new Db(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);


/* CHECK LAST ACTIVITY */
function checkLastActivity(){
	// check if is there activity in last 2 hours
	global $db;
	if(isset($_SESSION['logged'])){
		if($_SESSION['logged'] == true){
			$email = unhashSession($_SESSION['key']);
			$db->where("email", $email);
			$user = $db->getOne("users");
			$activity = $db->rawQueryOne("SELECT * FROM `loginLogs` WHERE userId = ? AND date >= NOW()- INTERVAL 2 HOUR ORDER BY DATE DESC LIMIT 1", array($user['id']));
			if($activity){
				return true;
			}
			return false;
		}
		return false;
	}
	return false;
}

/* LOG ACTIVITY */
function addActivity($note){
	global $db;
	$ip = getInfoIP(getClientIP());
	//$ip = getInfoIP("77.77.216.97");
	if(isset($_SESSION['logged'])){
		if($_SESSION['logged'] == true){
			$email = unhashSession($_SESSION['key']);
			$db->where("email", $email);
			$user = $db->getOne("users");
			$data = Array(
				"userId" => $user['id'],
				"ip" => $ip['ip'],
				"geo" => $ip['city'].', '.$ip['country_code'],
				"userAgent" => $_SERVER['HTTP_USER_AGENT'],
				"os" => 'Unknown',
				"type" => 2,
				"note" => $note,
				"session_idWeb" => session_id()
			);
			$id = $db->insert('loginLogs', $data);
			return false;
		}
		return false;
	}
	return false;
}

/* GET USER NAME FROM ID */
function getUserById($id){
	global $db;
	$db->where("id", $id);
	$user = $db->getOne("users");
	return $user;
}

/* GET CAT NAME FROM ID */
function getCatById($id){
	global $db;
	$db->where("id", $id);
	$user = $db->getOne("categories");
	return $user;
}

/* OSTALE GLUPOSTI */
function hashSession($simple_string){	  
	$ciphering = "AES-128-CTR"; 
	$iv_length = openssl_cipher_iv_length($ciphering); 
	$options = 0; 
	  
	$encryption_iv = '1234567891011121'; 
	$encryption_key = "HakniMeAkoSmijes"; 
	 $encryption = openssl_encrypt($simple_string, $ciphering, 
				$encryption_key, $options, $encryption_iv); 
	  
	return $encryption;
}

function unhashSession($encryption){
	$ciphering = "AES-128-CTR"; 
	$decryption_iv = '1234567891011121'; 
	$decryption_key = "HakniMeAkoSmijes"; 
	$options = 0;
	$decryption=openssl_decrypt($encryption, $ciphering,  
			$decryption_key, $options, $decryption_iv); 
	  
	// Display the decrypted string 
	return $decryption;
}

function getClientIP() {
	if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
			  $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
			  $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
	}
	$client  = @$_SERVER['HTTP_CLIENT_IP'];
	$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
	$remote  = $_SERVER['REMOTE_ADDR'];

	if(filter_var($client, FILTER_VALIDATE_IP)) { $ip = $client; }
	elseif(filter_var($forward, FILTER_VALIDATE_IP)) { $ip = $forward; }
	else { $ip = $remote; }

	return $ip;
}

function getInfoIP($ip) {
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://freegeoip.app/json/".$ip,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array(
		"accept: application/json",
		"content-type: application/json"
	  ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
	  //echo "cURL Error #:" . $err;
	} else {
	  return json_decode($response, true);
	}
}